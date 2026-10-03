# Estado de seguridad de las dependencias

`composer audit --locked` sobre `composer.lock` **no informa de ningún advisory**
(revisado el 2026-10-03). Los cuatro que había estaban todos en
`api-platform/core` 2.7.18, la última versión de una rama sin soporte, y se han
cerrado migrando a **4.3.21** (`^4.1` en `composer.json`):

| CVE | Severidad | Corregido en | Estado |
|-----|-----------|--------------|--------|
| [CVE-2025-31481](https://github.com/advisories/GHSA-cg3c-245w-728m) — se puede saltar la seguridad de las operaciones de consulta de GraphQL | alta | 3.4.17 / 4.0.22 / 4.1.5 | Cerrado |
| [CVE-2025-31485](https://github.com/api-platform/core/security/advisories/GHSA-428q-q3vv-3fq3) — un `grant` de GraphQL sobre una propiedad puede cachearse con otros objetos | alta | 3.4.17 / 4.0.22 / 4.1.5 | Cerrado |
| [CVE-2026-49858](https://github.com/advisories/GHSA-pjhx-3c3w-9v23) — fuga de atributos entre usuarios en los normalizadores de JSON:API y HAL | media | 4.1.29 / 4.2.25 / 4.3.8 | Cerrado |
| [CVE-2026-54164](https://github.com/advisories/GHSA-9rjg-x2p2-h68h) — los IRI de relación no se comprueban por tipo | media | 4.1.30 / 4.2.26 / 4.3.12 | Cerrado |

Ninguno era explotable con la configuración que tenía el proyecto (sin GraphQL,
sin JSON:API ni HAL, sin relaciones por IRI en los DTO), pero esas mitigaciones
eran propiedades de la configuración, no de la librería, y el siguiente aviso
contra la rama 2.x tampoco habría tenido parche.

## La migración a API Platform 4

De 2.7 a 4.3 hay dos versiones mayores: la 3.0 reescribió los metadatos
(`ApiPlatform\Core\Annotation\ApiResource` con `collectionOperations` /
`itemOperations` pasó a `ApiPlatform\Metadata\ApiResource` con `operations`), la
4.0 retiró `openapi_context` en favor de objetos `ApiPlatform\OpenApi\Model\*`
y dejó desactivados por defecto los listeners del kernel. Lo que se ha decidido,
y por qué, está comentado en `config/packages/api_platform.yaml` y en los dos
DTO de `src/Api/Domain/Dto/Common/`; en resumen:

- **Solo `json` como formato de los recursos.** `html` queda únicamente como
  formato de la documentación (`docs_formats`). No se ha habilitado JSON-LD,
  JSON:API ni HAL: cada formato hipermedia es superficie nueva, y dos de los
  cuatro avisos de arriba vivían precisamente en esos normalizadores.
- **Errores solo en `application/problem+json`**, como en la 2.7.
- **Sin GraphQL**: `webonyx/graphql-php` sigue sin estar instalado.
- **`use_symfony_listeners: true`**, porque las peticiones las atienden
  controladores propios marcados con `_api_resource_class` y
  `_api_operation_name`.
- **La integración OpenAPI de LexikJWT está desactivada** de forma explícita:
  API Platform la activa por su cuenta desde la 3.2 y duplicaba el login que ya
  documenta `JwtDecorator`.

API Platform 4 registra además rutas propias que antes no existían
(`/api/errors/{status}`, `/api/validation_errors/{id}`). Solo describen
errores; `access_control` no las menciona, así que son públicas como el resto de
la documentación.

Antes de añadir un formato hipermedia, GraphQL o una relación por IRI a un DTO
de entrada conviene volver a pasar `composer audit` y revisar los avisos de la
rama instalada: la superficie que hoy no existe es la que cubrían esos CVE.

## Paquetes abandonados

`composer audit` señala tres:

- `composer/package-versions-deprecated` — sin reemplazo. Lo exige el propio
  `composer.json` (fijado a `1.11.99.2`); ningún otro paquete lo necesita.
- `doctrine/annotations` — sustituible por atributos nativos de PHP 8. Los
  recursos de api-platform ya usan atributos y ningún paquete lo exige: sólo
  lo pide el propio `composer.json` (`^1.13`). Retirarlo es un cambio aparte,
  que pasa por comprobar que no queda ninguna anotación en docblocks.
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

## Fugas de información en las respuestas de error

Los dos controladores de formulario (`SignUpController` y
`CreateProductController`) envolvían la llamada al bus en
`catch (\Throwable $e)` y devolvían `$e->getMessage()` dentro del cuerpo del
400, para **cualquier** excepción.

Eso está bien para las excepciones de dominio, cuyo mensaje está escrito para
quien rellena el formulario ("La contraseña y su verificacion no coinciden").
No lo está para el resto: una `DriverException` de Doctrine lleva dentro la
consulta y la cadena de conexión, un `TypeError` lleva la ruta del fichero y la
firma del método, y un timeout lleva el host y el puerto de la base de datos.
`/users/signup-user` es público y sin autenticar, así que ese texto lo podía
leer cualquiera capaz de provocar el fallo —y provocarlo es tan barato como
enviar un campo con un tipo inesperado.

Ahora cada controlador tiene una lista explícita de las excepciones cuyo mensaje
sale al cliente (`ValueObjectException` como base de las validaciones de value
objects, más las de dominio que ese formulario puede lanzar). Todo lo demás se
registra con `logger->error()` y su traza completa, y el cliente recibe un
mensaje genérico. El detalle sigue estando —donde lo ve quien opera el
servicio, no quien lo ataca.
