# Game Library

Aplicación SPA para administrar una biblioteca de videojuegos.

El proyecto utiliza:

* Laravel 13
* Laravel Sail
* React
* MongoDB
* Redis
* ChromaDB
* RustFS
* Docker

## Requisitos

* Docker Desktop
* Docker Compose
* WSL2, en Windows
* Git

No es necesario instalar PHP, Composer, MongoDB, Redis, ChromaDB o RustFS directamente en el equipo.

## Estructura del proyecto

```text
game-library/
├── backend/
├── frontend/
├── docs/
├── README.md
├── CHANGELOG.md
└── .gitignore
```

## Levantar el backend

Desde la carpeta `backend`:

```bash
cd backend
./vendor/bin/sail up -d
```

## Detener el backend

```bash
./vendor/bin/sail down
```

## Ver contenedores

```bash
./vendor/bin/sail ps
```

## Ver logs

```bash
./vendor/bin/sail logs -f
```

## Ejecutar comandos de Laravel

```bash
./vendor/bin/sail artisan <comando>
```

Ejemplo:

```bash
./vendor/bin/sail artisan route:list
```

## Ejecutar Composer

```bash
./vendor/bin/sail composer <comando>
```

Ejemplo:

```bash
./vendor/bin/sail composer install
```

## Acceder al contenedor de Laravel

```bash
./vendor/bin/sail shell
```

## Servicios

| Servicio       | Dirección             |
| -------------- | --------------------- |
| Laravel API    | http://localhost:8000 |
| MongoDB        | localhost:27017       |
| Redis          | localhost:6379        |
| ChromaDB       | http://localhost:8001 |
| RustFS API S3  | http://localhost:9000 |
| RustFS Console | http://localhost:9001 |

## Comprobar el estado de la API

```bash
curl http://localhost:8000/api/health
```

La respuesta debe mostrar el estado de MongoDB, Redis, ChromaDB y RustFS.

Ejemplo:

```json
{
    "status": "ok",
    "services": {
        "mongodb": {
            "healthy": true
        },
        "redis": {
            "healthy": true
        },
        "chromadb": {
            "healthy": true
        },
        "rustfs": {
            "healthy": true
        }
    }
}
```

## MongoDB

Abrir la consola de MongoDB:

```bash
./vendor/bin/sail exec mongodb mongosh
```

## Redis

Comprobar Redis:

```bash
./vendor/bin/sail redis ping
```

Resultado esperado:

```text
PONG
```

## Variables de entorno

Copia el archivo de ejemplo:

```bash
cp .env.example .env
```

Después genera la clave de Laravel:

```bash
./vendor/bin/sail artisan key:generate
```

El archivo `.env` no debe incluirse en Git.

## Frontend

El frontend React se alojará en la carpeta:

```text
frontend/
```

Cuando esté configurado podrá iniciarse en:

```text
http://localhost:5173
```

## Estado actual

* [x] Laravel Sail
* [x] MongoDB
* [x] Redis
* [x] ChromaDB
* [x] RustFS
* [x] Endpoint de salud
* [ ] CRUD de juegos
* [ ] React SPA
* [ ] Autenticación
* [ ] Favoritos
* [ ] Búsqueda semántica
* [ ] Administración de trailers
