## Starhaven Oculus
Oculus is Starhaven's account and control-plane API. It owns player registration and authentication. The authoritative Node.js game servers own gameplay simulation.

No Laravel starter kit is installed. The API uses Laravel Sanctum bearer tokens.

## Docker Container
docker run -d `
  --name starhaven-oculus `
  --env-file .env `
  -e PHP_OPCACHE_ENABLE=1 `
  -e PHP_OPCACHE_VALIDATE_TIMESTAMPS=1 `
  -e PHP_OPCACHE_REVALIDATE_FREQ=0 `
  -e PHP_MEMORY_LIMIT=512M `
  -p 8080:8080 `
  --mount "type=bind,source=$($PWD.Path),target=/var/www/html" `
  starhaven-oculus

## Install Composer
docker exec starhaven-oculus composer install