# LMS API (Symfony 7)

API segura para LMS con JWT, RBAC, PostgreSQL, Redis y MinIO.

## Requisitos (DEV)
- Docker + Docker Compose

## Instalacion rapida
1. Copia `.env.example` a `.env` y ajusta variables.
2. Desde la raiz del repo:

```bash
cd devops
docker compose -f docker-compose.dev.yml up --build
```

## Comandos utiles
- Instalar dependencias:

```bash
composer install
```

- Migraciones:

```bash
php bin/console doctrine:migrations:migrate
```

- Seed (admin inicial):

```bash
php bin/console doctrine:fixtures:load
```

- Credenciales por defecto:
  - Email: `admin@lms.local`
  - Password: `Admin123!`

- Generar claves JWT:

```bash
php bin/console lexik:jwt:generate-keypair
```

## OpenAPI
Disponible en `http://localhost:8000/api/docs`.

## Configuracion
Ver `.env.example`.
