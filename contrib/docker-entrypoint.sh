#!/bin/bash

set -e
/usr/sbin/apache2ctl -DFOREGROUND

composer install
composer dump-autoload --no-scripts -o

#php bin/console lexik:jwt:generate-keypair
#setfacl -R -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
#setfacl -dR -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
