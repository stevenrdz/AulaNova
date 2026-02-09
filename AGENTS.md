# AGENTS.md - LMS Platform

## Status (2026-02-09)
- Swagger UI corregido (Twig/Asset) y accesible en `/api/docs`.
- Tracking: resumen admin/teacher con filtros (query `from`, `to`, `course_id`, `user_id`).
- Files: URL firmada GET vía `/files/{id}/download` + política MinIO privada.
- Messenger: `failed` transport + retries configurados.
- Frontend apuntando a API en `http://localhost:8000`.
- Smoke tests OK: auth/users/structure/virtual/actividades/tracking.
- Deprecaciones resueltas (Url requireTld, eraseCredentials).
- Docs: validaciones detalladas en Tracking/Files (docs/api.md).
- Docs: Auth/Users/Institution/Structure con validaciones (docs/api.md).
- Docs: OpenAPI YAML corregido (answers + sede-jornadas).
- Docs: ejemplos reales agregados en users/teachers/admins y updates de virtual (api.md).
- Docs: respuestas JSON estandarizadas para Structure (PUT/DELETE) en api.md.
- Docs: OpenAPI schemas con ejemplos completos (User/Curso/Virtual/Assessments/Imports).
- Tracking: resumen admin/teacher ahora incluye `by_route` (accesos por ruta).
- Files: endpoint de stream directo `/files/{id}/stream`.
- DB: migracion agregada para `time_tracking_route_daily`.
- Tests: phpunit OK luego de migracion en entorno test.
- Fixtures: recargadas (purge) en entorno dev.
- Frontend admin: CRUDs Structure (sede-jornada, niveles, periodos, asignaturas, carreras, cursos).
- Frontend admin: CRUDs Virtual (cursos virtuales) e Imports (batches + detalle).
- Frontend admin: Assessments (quizzes, preguntas, intentos, respuestas).
- UX: toasts globales para errores (frontend).
- Frontend teacher: dashboard con resumen tracking; cursos + detalle con anuncios/actividades y quizzes.
- Frontend student: cursos con tiempo de actividad y detalle (anuncios/actividades/quizzes).
- Backend: lectura de cursos/anuncios/actividades/quizzes permitida para estudiantes (GET).
- Seed: usuario `student@lms.local` creado para pruebas.
- Frontend teacher: gestión de evaluaciones (quizzes + preguntas) desde vista docente.
- Frontend student: flujo de quiz (iniciar, responder, finalizar, ver puntaje).
- Backend: lectura de preguntas para estudiantes (sin `correct_option`).
- Backend: entregas de actividades (submissions) con calificacion y feedback.
- Frontend: entregas basicas (student envia, teacher califica).
- Files: presign ahora usa `MINIO_PUBLIC_ENDPOINT` para uploads desde navegador.
- Frontend student: subida real de adjuntos en entregas (presign + PUT + complete).
- Frontend teacher/student: descarga de adjuntos en entregas.
- Frontend teacher: acciones masivas, filtros y analiticas en entregas.
- Admin: vista previa del logo con URL firmada cuando hay file_id disponible.
- Sidebar: logo usa URL firmada si hay file_id en institution.
- Frontend: Vite actualizado a ^7.3.1 y plugin-vue ^6.0.4 por audit fix.
- Frontend: ESLint TS/Vue configurado y auto-fix aplicado.

## Pending - Backend (especifico)
- Tracking: revisar retencion/limpieza de data diaria si aplica.
- Messenger: correr worker `messenger:consume` en entorno/infra de producción (systemd/supervisor/k8s).
- Docs: OpenAPI detallado, DTOs/validaciones/serializer groups completos.
- Tests: unit/integration + fixtures adicionales.
 
## Tests
- PHPUnit: OK (5 tests, 24 assertions).
- Frontend: ESLint OK (sin errores ni warnings).

## Pending - Frontend (especifico)
- Ninguno por ahora.

## Pending - DevOps/Docs
- Dockerfiles prod, variables finales, migraciones, backups.
- Documentar política MinIO, SMTP y pasos de despliegue.

## Notes
- `logo_url` guarda el `key` de MinIO; la descarga debe ser con URL firmada (`/files/{id}/download`).
- Autenticación: el login responde con `access_token` y `refresh_token` (snake_case).
- Las rutas NO llevan prefijo `/api` (ej: `/auth/login`, `/users`, `/structure/*`, `/virtual/*`, `/assessments/*`, `/files/*`, `/imports/*`, `/tracking/*`).
- DTOs usan snake_case y nombres específicos (ver README). En pruebas E2E fallaba por usar camelCase y campos antiguos.
- Uploads a MinIO: el `presign` devuelve host `minio`, por lo que la subida debe hacerse dentro del contenedor (`docker compose exec -T ... curl`).
- Entregas: requiere `MINIO_PUBLIC_ENDPOINT` para adjuntos desde browser.

## Ultima sesion (2026-02-09)
- Swagger UI disponible en `/api/docs`.
- Tracking admin/teacher summary agregado.
- Files: descarga con URL firmada.
- Messenger retries/failures configurados.
- Documentación actualizada en `docs/api.md`.
- OpenAPI YAML: rutas corregidas en `docs/openapi.yaml`.
- Institution: validacion de `logo_url` ampliada (URL o key) y esquema OpenAPI agregado.
- Fixtures: recargadas con `doctrine:fixtures:load --no-interaction`.
- Tests: phpunit OK (5 tests).
- API docs: ejemplos añadidos para users/teachers/admins y PUTs de virtual.
- API docs: estructura (Structure) reescrita y respuestas unificadas.
- OpenAPI: ejemplos añadidos en schemas principales.
- Tracking: agregado resumen por ruta (`by_route`) y tabla diaria dedicada.
- Files: endpoint `/files/{id}/stream` agregado.
- Tests: phpunit OK (4 tests).
- Fixtures: `doctrine:fixtures:load --no-interaction` ejecutado.
- Frontend: CRUDs admin de Structure/Virtual/Imports implementados.
- Frontend: CRUDs admin de Assessments (quizzes/preguntas/intentos/respuestas).
- Frontend: toasts globales agregados.
- Frontend: teacher dashboard + cursos/detalle con anuncios/actividades y quizzes.
- Frontend: student cursos + detalle con tracking de tiempo.

## Useful commands
- docker compose --env-file devops/.env -f devops/docker-compose.dev.yml up --build
- docker compose -f devops/docker-compose.dev.yml exec lms-api php bin/console doctrine:migrations:migrate
- docker compose -f devops/docker-compose.dev.yml exec lms-api php bin/console lexik:jwt:generate-keypair
- docker compose -f devops/docker-compose.dev.yml exec lms-api php bin/console doctrine:fixtures:load
- docker compose -f devops/docker-compose.dev.yml exec -T lms-api php bin/console messenger:consume async -vv
- docker compose -f devops/docker-compose.dev.yml exec -T lms-api php bin/console messenger:failed:show
- docker compose -f devops/docker-compose.dev.yml exec -T lms-api php bin/console messenger:failed:retry

## Default credentials
- admin@lms.local / Admin123!
- teacher@lms.local / Teacher123!
- student@lms.local / Student123!
