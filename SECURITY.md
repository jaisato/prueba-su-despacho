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
