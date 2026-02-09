# LMS Platform (Symfony + Vue 3)

Monorepo con dos proyectos separados: API en Symfony 7 y SPA en Vue 3 (DashUI Tailwind). Pensado para hosting del cliente y desarrollo local con Docker.

## Estructura
- `lms-api/`: API Symfony (JWT + RBAC + PostgreSQL + Redis + MinIO)
- `lms-web/`: SPA Vue 3 (DashUI + Tailwind + Pinia + Router)
- `devops/`: Docker Compose para DEV
- `docs/`: Diagramas, decisiones y OpenAPI
  - `docs/api.md`: Documentación detallada de endpoints (español)
  - `docs/openapi.yaml`: OpenAPI manual basado en el comportamiento actual

## Quickstart (DEV)
1. Copia `.env.example` a `.env` y ajusta si es necesario.
2. Levanta el stack:

```bash
cd devops
docker compose --env-file ../.env -f docker-compose.dev.yml up --build
```

Servicios locales:
- API: http://localhost:8000
- OpenAPI: http://localhost:8000/api/docs
- Web: http://localhost:5174 (configurable en .env)
- MailHog: http://localhost:8025
- MinIO Console: http://localhost:9001

Lee los README de `lms-api/` y `lms-web/` para pasos específicos.

## Worker (Messenger)
Para procesar colas async:

```bash
cd devops
docker compose -f docker-compose.dev.yml exec -T lms-api php bin/console messenger:consume async -vv
```

Retry/failed:
```bash
docker compose -f docker-compose.dev.yml exec -T lms-api php bin/console messenger:failed:show
docker compose -f docker-compose.dev.yml exec -T lms-api php bin/console messenger:failed:retry
```
Notas:
- En producción, correr el worker como servicio (systemd/supervisor/k8s).
- Ajustar `MESSENGER_TRANSPORT_DSN` y `MESSENGER_FAILURE_TRANSPORT_DSN` para ambiente prod.
- Monitorear `messenger:failed:show` y alertas si hay reintentos fallidos.
Ejemplo (systemd):
```ini
[Unit]
Description=LMS Messenger Worker
After=network.target

[Service]
User=www-data
WorkingDirectory=/var/www/lms-api
ExecStart=/usr/bin/php bin/console messenger:consume async -vv --time-limit=3600 --memory-limit=256M
Restart=always

[Install]
WantedBy=multi-user.target
```

## Observaciones de API (importante para pruebas)
- Las rutas NO llevan prefijo `/api` (ej: `/auth/login`, `/users`, `/structure/*`, `/virtual/*`, `/assessments/*`, `/files/*`, `/imports/*`, `/tracking/*`).
- Login devuelve `access_token` y `refresh_token` (snake_case).
- Los DTOs usan snake_case y nombres específicos (ejemplos):
  - Users: `first_name`, `last_name`, `role`.
  - Curso: `capacity`, `start_date`, `end_date`, `periodo_id`, `teacher_id`, `sede_jornada_id`, `carrera_id`, `asignatura_id`.
  - Actividad: `due_at` (datetime válido), `file_id`, `attachment_ids`, `youtube_url`.
  - Quiz: `start_at`, `end_at`, `time_limit_minutes`.
  - Tracking: `route`, `course_id`, `timestamp`.
  - Imports: `file_id` (después de `/files/complete`).
- Uploads a MinIO: el `presign` devuelve host `minio`; la subida debe hacerse dentro del contenedor (`docker compose exec -T ... curl`).
- Uploads desde browser: configurar `MINIO_PUBLIC_ENDPOINT` (ej: `http://localhost:9000`) para que el presign use host accesible.
- Tracking: considerar job diario (cron) para consolidación/retención si el volumen de accesos crece.
  - Command: `php bin/console tracking:cleanup --days=180`.

## Ultima sesion (2026-02-09)
- Swagger UI corregido (Twig/Asset) y accesible en `/api/docs`.
- Tracking: resumen admin/teacher con filtros (query `from`, `to`, `course_id`, `user_id`).
- Files: URL firmada GET vía `/files/{id}/download` + política MinIO privada.
- Admin: validaciones y helper texts en Assessments (quizzes/preguntas/intentos/respuestas).
- Frontend teacher: filtros, analiticas y acciones masivas en entregas.
- Admin: vista previa del logo con URL firmada cuando hay file_id disponible.
- Sidebar: logo usa URL firmada si hay file_id en institution.
- Frontend: dependencias actualizadas por `npm audit fix --force` (Vite ^7.3.1, plugin-vue ^6.0.4).
- Frontend: ESLint TS/Vue configurado y auto-fix aplicado.
- Frontend apuntando a API en `http://localhost:8000`.
- Smoke tests OK: auth/users/structure/virtual/actividades/tracking.
- Deprecaciones resueltas (Url requireTld y eraseCredentials).
- Docs: validaciones detalladas de Tracking y Files en docs/api.md.
- Docs: Auth/Users/Institution/Structure con validaciones en docs/api.md.
- Docs: OpenAPI YAML corregido y sincronizado con rutas de answers y sede-jornadas.
- Docs: ejemplos agregados para `/users/teachers`, `/users/admins`, PUT `/virtual/cursos/{id}` y PUT `/virtual/anuncios/{id}`.
- Docs: se estandarizaron respuestas JSON en seccion Structure (PUT/DELETE) de `docs/api.md`.
- Docs: OpenAPI `docs/openapi.yaml` con ejemplos completos en schemas principales.
- Docs: OpenAPI `InstitutionSettings` + validacion de `logo_url` (URL o key).
- Tracking: resumen admin/teacher incluye `by_route` (accesos por ruta).
- Files: endpoint `/files/{id}/stream` agregado.
- Tests: phpunit OK (10 tests). ESLint frontend OK (sin errores ni warnings).
- Fixtures: recargadas con `doctrine:fixtures:load --no-interaction`.
- Tests: phpunit OK tras recarga de fixtures.
- Auth: responses incluyen `is_active` en el usuario.
- Frontend: CRUDs admin de Structure/Virtual/Imports implementados.
- Frontend: CRUDs admin de Assessments (quizzes/preguntas/intentos/respuestas).
- Frontend: toasts globales agregados.
- Frontend: teacher dashboard + cursos/detalle con anuncios/actividades y quizzes.
- Frontend: student cursos + detalle con tracking de tiempo.
- Backend: GET de cursos/anuncios/actividades/quizzes habilitado para estudiantes.
- Frontend: teacher gestion de evaluaciones (quizzes + preguntas).
- Frontend: student flujo de quiz (intentos, respuestas, finalizacion, puntaje).
- Backend: GET de preguntas para estudiantes (sin `correct_option`).
- Backend: entregas de actividades (submissions) con calificacion y feedback.
  - Frontend: entregas con adjuntos (presign + complete) y descarga (student/teacher).
- Tracking: nuevos reportes por ruta/día (`/tracking/admin/routes`, `/tracking/teacher/routes`).
- Tracking: comando `tracking:cleanup` para retención diaria.
- OpenAPI: Auth/Users/Imports con schemas de request + 422 Validation.
- OpenAPI: Structure con schemas de request + 422 Validation.
- OpenAPI: Virtual con schemas de request + 422 Validation.
- OpenAPI: Assessments con schemas de request + 422 Validation.
- OpenAPI: Files + Tracking heartbeat con schemas request/response + 422 Validation.

## Pendientes (especificos)
- Backend/Messenger: correr worker `messenger:consume` en entorno/infra de producción (systemd/supervisor/k8s).
- Backend/Tests: unit/integration + fixtures adicionales.
- DevOps/Prod: dockerfiles prod, envs, migraciones, backups, despliegue en hosting del cliente.
- Docs: ADRs, diagrama general, flujo de archivos/MinIO y SMTP.

## Credenciales (DEV)
- admin@lms.local / Admin123!
- teacher@lms.local / Teacher123!
- student@lms.local / Student123!
## Tests
```bash
cd devops
docker compose --env-file ../.env -f docker-compose.dev.yml exec -T lms-api php vendor/bin/phpunit
```
