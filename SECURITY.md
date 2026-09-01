# Estado de seguridad de las dependencias

`composer audit` sobre `composer.lock` informa de **4 advisories**, todos ellos
en `api-platform/core` (instalado: `v2.7.18`). Ninguno tiene corrección dentro
de la rama 2.x, así que conviene dejar por escrito cuáles afectan realmente a
esta aplicación y cuáles no.

| CVE | Severidad | ¿Afecta a esta app? | Motivo |
|-----|-----------|---------------------|--------|
| [CVE-2025-31481](https://github.com/advisories/GHSA-cg3c-245w-728m) | alta | **No** | El fallo está en las operaciones de consulta de GraphQL. Esta API no expone GraphQL: `config/packages/api_platform.yaml` no lo habilita y no hay ningún resolver ni tipo declarado en `src/`. |
| [CVE-2025-31485](https://github.com/api-platform/core/security/advisories/GHSA-428q-q3vv-3fq3) | alta | **No** | Igual que el anterior: cacheo de permisos por propiedad en GraphQL. |
| [CVE-2026-49858](https://github.com/advisories/GHSA-pjhx-3c3w-9v23) | media | **No** | Fuga de atributos entre usuarios en los normalizadores de **JSON:API** y **HAL**. La configuración sólo declara `json` (`application/json`) y `html`; ninguno de esos dos formatos está activado. |
| [CVE-2026-54164](https://github.com/advisories/GHSA-9rjg-x2p2-h68h) | media | **Sí, potencialmente** | Los IRI de relación no se comprueban por tipo, de modo que un recurso relacionado puede desnormalizarse como un tipo distinto del esperado. Aplica al camino REST, que es el que usa esta API. |

## Sobre CVE-2026-54164

Es el único que puede afectar. El arreglo está en `api-platform/core >= 4.1.30`,
es decir, **dos versiones mayores por delante** de la instalada. Actualizar de
2.7 a 4.x no es un cambio de dependencia: la 3.0 reescribió el sistema de
metadatos (`ApiResource` pasó de anotaciones/`@ApiResource` a atributos con otra
forma), y la 4.0 volvió a mover la configuración de operaciones. Es una
migración con su propio trabajo de verificación y no se ha hecho aquí, porque
hacerla a ciegas y sin poder ejecutar la suite es peor que no hacerla.

Mitigación en el estado actual: el impacto depende de aceptar IRI de relación en
los cuerpos de entrada. Los DTO de entrada de esta API (`src/Api/Ui/Request/`)
son escalares -no hay ninguna propiedad que reciba un IRI-, lo que reduce mucho
la superficie. Antes de añadir una relación por IRI a un DTO de entrada,
**hay que abordar la migración a 4.1.30+**.

## Paquetes abandonados

`composer audit` señala tres:

- `composer/package-versions-deprecated` — sin reemplazo; sólo lo arrastra la
  cadena de dependencias.
- `doctrine/annotations` — sustituible por atributos nativos de PHP 8; requiere
  recorrer las entidades y va ligado a la migración de api-platform.
- `doctrine/cache` — sin reemplazo directo; lo arrastra `doctrine/orm ^2.9`.

## Restricciones de versión

Cinco dependencias declaraban `"*"` como restricción: `brick/money`,
`ircmaxell/random-lib`, `lexik/jwt-authentication-bundle`,
`paragonie/constant_time_encoding` y `webmozart/assert`. Con `"*"` cualquier
`composer update` puede traer una versión mayor -incluida una publicada después
de la última revisión del código- sin que nada avise. Dos de ellas están además
en el camino de la autenticación. Ahora están acotadas a la mayor instalada.

`ircmaxell/random-lib` se ha eliminado: era la única razón por la que estaba, y
`ClearTextPassword::generate()` ahora usa el CSPRNG del propio PHP.

## Autenticación de la API (corregido)

Hasta este cambio, la configuración de seguridad no protegía ningún endpoint:

- El firewall con JWT tenía `pattern: ^/area-usuario/`, y ninguna ruta cuelga de
  ahí. Las rutas se montan bajo `%api_route_prefix%` (`/api`), así que el
  autenticador JWT no llegaba a ejecutarse nunca.
- `access_control` apuntaba a `^/api/area-usuario/`, que tampoco casa con nada,
  de modo que `ROLE_WEB` no se exigía en ningún sitio -incluido
  `/api/form/{tipoForm}`, que el README documenta como autenticado-.
- En consecuencia toda petición caía en el firewall de login, que no tenía
  `pattern` propio ni `stateless`, y quedaba autenticada por **cookie de sesión**
  en lugar de por el JWT que el endpoint `/api/login` emite.

Ahora hay dos firewalls (`^%api_route_prefix%/login$` para emitir el token y
`^%api_route_prefix%` con `jwt` para el resto, ambos `stateless`) y las reglas de
`access_control` se escriben sobre las rutas reales: público para docs, login,
alta de usuario y listado de productos; `ROLE_WEB` para la creación de productos.
`CreateProductController` además llama a `denyAccessUnlessGranted('ROLE_WEB')`,
para que un patrón que deje de casar vuelva a fallar cerrado (403) en vez de
llegar con `null` a `UserWebTransformer` y salir como un 500.

El test `CreateProductControllerTest` enviaba la cabecera como `Authorization` en
el array `server`, donde BrowserKit espera `HTTP_AUTHORIZATION`: el token nunca
viajaba y el test pasaba gracias a la sesión. Corregido, y añadido un caso que
comprueba que sin token la respuesta es 401/403.

## Secretos en el repositorio

`.env` incluye valores reales de `APP_SECRET`, `JWT_PASSPHRASE` y las
credenciales de MySQL. Son los valores de desarrollo del docker-compose y no
se han tocado para no romper el entorno local, pero **no deben reutilizarse en
ningún despliegue**: ahí van por `.env.local` o por variables de entorno.

## Avisos abiertos en `api-platform/core` 2.7.18 (revisado 2026-08-30)

`composer audit --locked` señala cuatro avisos, los cuatro sobre
`api-platform/core`. La versión fijada es **2.7.18**, la última de la rama 2.x,
que está fuera de soporte: **ninguno tiene parche dentro de 2.x**. El umbral de
corrección **no es el mismo para los cuatro** -va en la tabla, aviso por aviso-,
así que un único número resume mal: sobre la rama 4.1 hace falta llegar a
**4.1.30** para cerrarlos todos, porque 4.1.29 deja CVE-2026-54164 abierto.

Contrastados uno a uno contra la configuración de este proyecto, **ninguno es
explotable tal y como está montado hoy**:

| Aviso | Corregido en | Por qué no aplica aquí |
|-------|--------------|------------------------|
| CVE-2025-31481 — se puede saltar la seguridad de las operaciones GraphQL | 3.4.17 / 4.0.22 / 4.1.5 | No hay GraphQL: `composer.lock` no incluye `webonyx/graphql-php` ni el subpaquete de GraphQL, así que la funcionalidad no está instalada. |
| CVE-2025-31485 — un `grant` de GraphQL sobre una propiedad puede cachearse con otros objetos | 3.4.17 / 4.0.22 / 4.1.5 | Igual que el anterior. |
| CVE-2026-49858 — fuga de atributos entre usuarios en los normalizadores de ítem de JSON:API y HAL | 4.1.29 / 4.2.25 / 4.3.8 | `config/packages/api_platform.yaml` declara únicamente `json` y `html`; los normalizadores de JSON:API y HAL no intervienen en ninguna respuesta. |
| CVE-2026-54164 — los IRI de relación no se comprueban por tipo (confusión de tipos al desnormalizar) | **4.1.30** / 4.2.26 / 4.3.12 | Los recursos expuestos (`ProductsPaginatedDto`, `FormResponseDto`) son DTO de salida sin propiedades de relación, de modo que no hay IRI de relación que desnormalizar. |

Esto **no** es motivo para dejarlo así indefinidamente. Depender de una rama sin
soporte significa que el próximo aviso tampoco tendrá parche, y las cuatro
mitigaciones de arriba son propiedades de la configuración actual: basta con
habilitar GraphQL, añadir `jsonapi` o `hal` a `formats`, o exponer un recurso con
relaciones, para que una de ellas deje de sostenerse sin que nada avise.

La corrección de verdad es subir a una rama con soporte, que es una migración de
mayor (anotaciones a atributos, metadatos de recurso, cambio de espacio de
nombres `ApiPlatform\Core\*`) y no un `composer update`: hay que hacerla y
probarla con la base de datos delante, no a ciegas.

También quedan tres paquetes abandonados —`composer/package-versions-deprecated`,
`doctrine/annotations` y `doctrine/cache`—, que la misma migración se lleva por
delante.
