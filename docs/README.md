# Docs

Este directorio contiene documentacion y notas de implementacion.

## Archivos
- `api.md`: Endpoints y ejemplos (espanol).
- `flows.md`: Flujos funcionales (basado en `Flujo final.docx`).
- `openapi.yaml`: OpenAPI manual basado en el comportamiento actual.

## Pendiente
- ADRs y diagramas de arquitectura.
- Flujo de archivos/MinIO y SMTP.
- Validar cobertura total de rutas en OpenAPI vs `/api/docs.json`.
- Mantener ejemplos reales sincronizados con datos de BD en `api.md`.
- Verificar consistencia de respuestas/paginacion en nuevas rutas.
- Mantener ejemplos en `docs/openapi.yaml` alineados con `docs/api.md`.
- Documentar flujos UI teacher/student si se requieren en docs funcionales.
- Validar el flujo de upload en UI (presign + PUT + complete) en entorno real.
