# Prueba Técnica sudespacho.net

La prueba técnica se desarrollado en una aplicación de Symfony 6.4 y PHP 8.2. Se ha dockerizado para una fácil instalación en un entorno local.
Se ha realizado una estructura en DDD con Arquitectura Hexagonal y CQRS. 

## Instalación y Configuración

- Clonar el repositorio: 
- Instalar Docker y Docker Compose.
- Copiar el fichero .env a .env.local
- Copiar el fichero .env.test a .env.test.local
- Desde la carpeta raíz en la línea de comandos (terminal), ejecutar: docker-compose up -d
- Una vez iniciados los contenedores (products_web y products_mysql), desde el terminal entrar en el contenedor 
'products_web' ejecutando: docker exec -it products_web bash
- Una vez dentro del contenedor web, instalar las dependencias, ejecutando: composer install
- Después generar las claves para la autenticación de usuarios con JWT. Ejecutar: php bin/console lexik:jwt:generate-keypair
- Limpiar la cache de Symfony ejecutando: php bin/console clear:cache
- Ejecutar las migraciones para la base de datos: php bin/console doctrine:migrations:migrate
- Instalar los assets ejecutando: php bin/console assets:install

## Endpoints API y API Platform

Una vez hecha la instalación y configuración, ya se pueden probar los endpoints de la API. 
Para poder probar los endpoints de la API facilmente desde un navegador, se ha instalado, configurado y utilizado la librería de 
'API Platform'.

Simplemente hay que abrir una nueva pestaña en el navegador e ir a la URL: http://localhost:8070/api

Aquí aparecerá la documentación de la API, donde también se pueden probar los diferentes endpoints.

### Endpoints:

A continuación se definen los endpoints por orden de ejecución:

- Users:
  - '/api/users/{tipoForm}' (POST): es un endpoint de la parte de usuarios donde se puede ejecutar el formulario para crear un usuario de la API
- Login:
  - '/api/login' (POST): una vez creado algún usuario, este endpoint permite crear un token JWT para la autenticación de un usuario en concreto.
- Productos:
  - '/api/form/{tipoForm}' (POST): en este endpoint se puede enviar un formulario para crear un producto nueva. Nota: hay que estar autenticado. Para autenticarse en API Platform, ejecutar el endpoint de /login y copiar el token generado en la respuesta. Después clicar en el botón de 'Authorize', pegar el token copiado y confirmar autenticación clicando el botón 'Authorize'.
  - '/api/productos/{pagina}/{resultadosPorPagina}': endpoint para listar los productos creados con páginación. Parámetros: en la URL se incluyen los parámetros de {pagina} y {resultadosPorPagina} para la paginación y son obligatorios. Hay 2 parámetros más opcionales, vía query, donde se puede especificar el 'orden' de los resultados y otro para filtar los productos por nombre (name).

## Tests

Para ejecutar los tests ir al contenedor web desde la terminal, ejecutando: docker exec -it products_web bash.

Una vez dentro, ejecutar las migraciones para el entorno y base de datos de test: php bin/console doctrine:migrations:migrate --env=test --no-interaction

Y finalmente, ejecutar el PHPUnit: ./vendor/bin/phpunit