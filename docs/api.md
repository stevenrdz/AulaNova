# API LMS (Symfony)

DocumentaciÃ³n en espaÃ±ol basada en el comportamiento actual de la API. Todas las rutas son sin prefijo `/api`.

**Base URL (DEV)**  
`http://localhost:8000`

**AutenticaciÃ³n**  
Se usa JWT con esquema Bearer. El login devuelve `access_token` y `refresh_token`.

**Errores comunes**
```json
{
  "message": "Validation failed",
  "errors": {
    "campo": ["mensaje de validacion"]
  }
}
```
```json
{ "message": "Registro no encontrado." }
```
```json
{ "message": "Unauthorized" }
```
## OpenAPI Schemas (resumen)

User
```json
{
  "id": 1,
  "email": "user@lms.local",
  "first_name": "User",
  "last_name": "One",
  "roles": ["ROLE_STUDENT"],
  "is_active": true
}
```

Curso
```json
{
  "id": 16,
  "name": "Curso 1",
  "capacity": 30,
  "start_date": "2026-02-01",
  "end_date": "2026-06-01",
  "periodo": { "id": 16, "name": "2026-1" },
  "teacher": { "id": 29, "email": "teacher@lms.local", "first_name": "Teacher", "last_name": "One" },
  "sede_jornada": { "id": 16, "name": "Sede A" },
  "carrera": { "id": 16, "name": "Ingenieria" },
  "asignatura": { "id": 16, "name": "Programacion I" }
}
```

CursoVirtual
```json
{
  "id": 15,
  "description": "Desc",
  "created_at": "2026-02-05 06:23:49",
  "curso": { "id": 16, "name": "Curso 1" }
}
```

Anuncio
```json
{
  "id": 13,
  "title": "Bienvenida",
  "content": "Hola",
  "created_at": "2026-02-05 06:22:07",
  "updated_at": null,
  "curso_virtual": { "id": 14, "curso_id": 15 },
  "created_by": { "id": 1, "email": "admin@lms.local", "first_name": "Admin", "last_name": "LMS" }
}
```

Actividad
```json
{
  "id": 3,
  "type": "FILE",
  "title": "Actividad 1",
  "content": "Sube",
  "youtube_url": null,
  "is_graded": false,
  "due_at": "2026-03-01 12:00:00",
  "created_at": "2026-02-05 06:22:10",
  "curso_virtual": { "id": 14, "curso_id": 15 },
  "file": { "id": 8, "key": "uploads/2026/02/5853c24080caea7cd50bab330fc202c1.txt", "bucket": "lms", "original_name": "test.txt", "mime_type": "text/plain", "size": 5 },
  "attachments": []
}
```

Quiz
```json
{
  "id": 3,
  "title": "Quiz 1",
  "description": null,
  "start_at": "2026-02-05 00:00:00",
  "end_at": "2026-02-10 23:59:59",
  "time_limit_minutes": 30,
  "curso_virtual": { "id": 15, "curso_id": 16 }
}
```

Question
```json
{
  "id": 5,
  "type": "SINGLE",
  "prompt": "2+2?",
  "options": ["3", "4"],
  "correct_option": "4",
  "quiz": { "id": 3, "curso_virtual_id": 15 }
}
```

Attempt
```json
{
  "id": 3,
  "started_at": "2026-02-05 06:23:53",
  "finished_at": "2026-02-05 06:23:54",
  "score": 100,
  "quiz": { "id": 3, "title": "Quiz 1" },
  "user": { "id": 30, "email": "student@lms.local", "first_name": "Student", "last_name": "One" }
}
```

Answer
```json
{
  "id": 5,
  "answer_text": "4",
  "is_correct": true,
  "attempt": { "id": 3, "quiz_id": 3, "user_id": 30 },
  "question": { "id": 5, "type": "SINGLE", "prompt": "2+2?" }
}
```

FileObject
```json
{
  "id": 9,
  "key": "uploads/2026/02/3561b28ff1f7844a3c2417bea4db73e7.csv",
  "bucket": "lms",
  "original_name": "users.csv",
  "mime_type": "text/csv",
  "size": 120
}
```

ImportBatch
```json
{
  "id": 1,
  "type": "users",
  "status": "pending",
  "total_rows": null,
  "success_count": null,
  "error_count": null,
  "created_at": "2026-02-05 06:24:19",
  "created_by": { "id": 1, "email": "admin@lms.local", "first_name": "Admin", "last_name": "LMS" },
  "result_file": null
}
```
```json
{
  "message": "Validation failed",
  "errors": {
    "campo": ["mensaje de validaciÃ³n"]
  }
}
```
```json
{
  "message": "Registro no encontrado."
}
```
```json
{
  "message": "Unauthorized"
}
```

## Auth

**POST `/auth/login`**
```json
{
  "email": "admin@lms.local",
  "password": "Admin123!"
}
```
Validaciones:
- `email` requerido y formato email
- `password` requerido
Respuestas
```json
{
  "access_token": "jwt",
  "refresh_token": "token",
  "user": {
    "id": 1,
    "email": "admin@lms.local",
    "first_name": "Admin",
    "last_name": "LMS",
    "roles": ["ROLE_ADMIN"]
  }
}
```
```json
{ "message": "Credenciales invalidas." }
```
```json
{ "message": "Demasiados intentos. Intenta mas tarde." }
```

**POST `/auth/refresh`**
```json
{ "refresh_token": "token" }
```
Validaciones:
- `refresh_token` requerido
Respuestas
```json
{
  "access_token": "jwt",
  "refresh_token": "token",
  "user": {
    "id": 1,
    "email": "admin@lms.local",
    "first_name": "Admin",
    "last_name": "LMS",
    "roles": ["ROLE_ADMIN"]
  }
}
```
```json
{ "message": "Refresh token invalido o expirado." }
```
```json
{ "message": "Refresh token reutilizado. Sesion invalidada." }
```

**POST `/auth/logout`**
```json
{ "refresh_token": "token" }
```
Validaciones:
- `refresh_token` requerido
Respuestas
```json
{ "message": "Sesion finalizada" }
```

**POST `/auth/change-password`**
```json
{
  "current_password": "Old123!",
  "new_password": "New123!"
}
```
Validaciones:
- `current_password` requerido
- `new_password` requerido, minimo 8
Respuestas
```json
{ "message": "Contrasena actualizada" }
```
```json
{ "message": "Unauthorized" }
```
```json
{ "message": "Contrasena actual invalida." }
```
```json
{ "message": "La nueva contrasena no puede ser igual a la actual." }
```

**POST `/auth/forgot-password`**
```json
{ "email": "user@lms.local" }
```
Validaciones:
- `email` requerido y formato email
Respuestas
```json
{ "message": "Si el correo existe, enviaremos un OTP." }
```
```json
{ "message": "Demasiados intentos. Intenta mas tarde." }
```

**POST `/auth/reset-password`**
```json
{
  "email": "user@lms.local",
  "otp": "123456",
  "new_password": "New123!"
}
```
Validaciones:
- `email` requerido y formato email
- `otp` requerido (6 digitos)
- `new_password` requerido, minimo 8
Respuestas
```json
{ "message": "Contrasena actualizada" }
```
```json
{ "message": "OTP invalido o expirado." }
```\r\n## Users (ROLE_ADMIN)

**GET `/users`**  
Query: `page`, `limit`, `q`, `is_active`, `role`
Notas:
- `is_active` acepta `true`/`false`
Respuestas
```json
{
  "data": [
    {
      "id": 1,
      "email": "admin@lms.local",
      "first_name": "Admin",
      "last_name": "LMS",
      "roles": ["ROLE_ADMIN"],
      "is_active": true
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```

**GET `/users/teachers`**  
Misma respuesta que `/users` (filtra por ROLE_TEACHER).

Respuestas
```json
{
  "data": [
    {
      "id": 2,
      "email": "teacher@lms.local",
      "first_name": "Teacher",
      "last_name": "One",
      "roles": ["ROLE_TEACHER"],
      "is_active": true
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```

**GET `/users/admins`**  
Misma respuesta que `/users` (filtra por ROLE_ADMIN).

Respuestas
```json
{
  "data": [
    {
      "id": 1,
      "email": "admin@lms.local",
      "first_name": "Admin",
      "last_name": "LMS",
      "roles": ["ROLE_ADMIN"],
      "is_active": true
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```

**POST `/users`**
```json
{
  "email": "teacher@lms.local",
  "password": "Teacher123!",
  "first_name": "Teacher",
  "last_name": "One",
  "role": "ROLE_TEACHER"
}
```
Validaciones:
- `email` requerido y formato email
- `password` requerido, minimo 8
- `first_name` requerido
- `last_name` requerido
- `role` opcional (`ROLE_ADMIN`, `ROLE_TEACHER`, `ROLE_STUDENT`)
Respuestas
```json
{
  "data": {
    "id": 2,
    "email": "teacher@lms.local",
    "first_name": "Teacher",
    "last_name": "One",
    "roles": ["ROLE_TEACHER"],
    "is_active": true
  }
}
```
```json
{ "message": "El email ya existe." }
```

**PUT `/users/{id}`**
```json
{
  "email": "teacher@lms.local",
  "password": "New123!",
  "first_name": "Teacher",
  "last_name": "Two",
  "role": "ROLE_TEACHER"
}
```
Validaciones:
- `email` si se envia, formato email
- `password` si se envia, minimo 8
- `role` si se envia (`ROLE_ADMIN`, `ROLE_TEACHER`, `ROLE_STUDENT`)
Respuestas
```json
{
  "data": {
    "id": 2,
    "email": "teacher@lms.local",
    "first_name": "Teacher",
    "last_name": "Two",
    "roles": ["ROLE_TEACHER"],
    "is_active": true
  }
}
```
```json
{ "message": "Usuario no encontrado." }
```

**DELETE `/users/{id}`**
Respuestas
```json
{ "message": "Usuario eliminado" }
```
```json
{ "message": "Usuario no encontrado." }
```\r\n

**GET `/users`**  
Query: `page`, `limit`, `q`, `is_active`, `role`
Respuestas
```json
{
  "data": [
    {
      "id": 1,
      "email": "admin@lms.local",
      "first_name": "Admin",
      "last_name": "LMS",
      "roles": ["ROLE_ADMIN"],
      "is_active": true
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```

**GET `/users/teachers`**  
Misma respuesta que `/users` (filtra por ROLE_TEACHER).

Respuestas
```json
{
  "data": [
    {
      "id": 2,
      "email": "teacher@lms.local",
      "first_name": "Teacher",
      "last_name": "One",
      "roles": ["ROLE_TEACHER"],
      "is_active": true
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
## Institution (ROLE_ADMIN)

**GET `/institution`**
```json
{
  "data": {
    "logo_url": "minio-key",
    "primary_color": "#0ea5e9"
  }
}
```

**PUT `/institution`**
```json
{
  "logo_url": "minio-key",
  "primary_color": "#0ea5e9"
}
```
Respuestas
```json
{
  "data": {
    "logo_url": "minio-key",
    "primary_color": "#0ea5e9"
  }
}
```

## Structure (ROLE_ADMIN)

Validaciones (DTOs):
- SedeJornada: name requerido (max 120), is_active opcional (bool)
- Nivel: name requerido (max 120), is_active opcional (bool)
- Periodo: name requerido (max 120), start_date/end_date formato YYYY-MM-DD
- Carrera: name requerido (max 120), is_active opcional (bool)
- Asignatura: name requerido (max 120), is_active opcional (bool)
- Curso: name requerido (max 150), capacity >= 0, fechas YYYY-MM-DD, IDs positivos (periodo_id, teacher_id, sede_jornada_id, carrera_id, asignatura_id)

**GET `/structure/sede-jornadas`**  
Query: `page`, `limit`, `q`, `is_active`
```json
{
  "data": [
    { "id": 1, "name": "Sede A", "is_active": true }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```
**POST `/structure/sede-jornadas`**
```json
{ "name": "Sede A", "is_active": true }
```
**PUT `/structure/sede-jornadas/{id}`**  
Respuestas de creacion/actualizacion/lectura
```json
{ "data": { "id": 1, "name": "Sede A", "is_active": true } }
```
**DELETE `/structure/sede-jornadas/{id}`**  
Respuestas de error
```json
{ "message": "Registro no encontrado." }
```
```json
{ "message": "No se puede eliminar el registro." }
**POST `/virtual/actividades/{id}/submissions`** (student)
```json
{
  "content": "Mi respuesta",
  "file_id": 10
}
```
Validaciones:
- `content` o `file_id` requerido (al menos uno)
- `file_id` si viene debe existir
- respeta `due_at` (si la actividad ya vencio, devuelve 400)

**GET `/virtual/actividades/{id}/submissions`** (teacher/admin)  
Query: `page`, `limit`, `status`, `user_id`
```json
{
  "data": [
    {
      "id": 1,
      "status": "SUBMITTED",
      "grade": null,
      "feedback": null,
      "content": "Mi respuesta",
      "submitted_at": "2026-02-09 12:00:00",
      "graded_at": null,
      "actividad": { "id": 1, "curso_virtual_id": 1 },
      "user": { "id": 5, "email": "student@lms.local", "first_name": "Student", "last_name": "LMS" },
      "file": { "id": 10, "key": "uploads/...", "bucket": "lms", "original_name": "tarea.pdf", "mime_type": "application/pdf", "size": 12000 }
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```
**GET `/virtual/actividades/{id}/submissions/me`** (student)
```json
{
  "data": [
    {
      "id": 1,
      "status": "GRADED",
      "grade": 95,
      "feedback": "Buen trabajo",
      "content": "Mi respuesta",
      "submitted_at": "2026-02-09 12:00:00",
      "graded_at": "2026-02-10 09:00:00",
      "actividad": { "id": 1, "curso_virtual_id": 1 },
      "user": { "id": 5, "email": "student@lms.local", "first_name": "Student", "last_name": "LMS" },
      "file": null
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```
**PUT `/virtual/actividades/submissions/{id}`** (teacher/admin)
```json
{
  "grade": 90,
  "feedback": "Buen trabajo",
  "status": "GRADED"
}
```
```

**GET `/structure/niveles`**  
Query: `page`, `limit`, `q`, `is_active`
```json
{
  "data": [
    { "id": 1, "name": "Pregrado", "is_active": true }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```
**POST `/structure/niveles`**
```json
{ "name": "Pregrado", "is_active": true }
```
**PUT `/structure/niveles/{id}`**  
Respuestas de creacion/actualizacion/lectura
```json
{ "data": { "id": 1, "name": "Pregrado", "is_active": true } }
```
**DELETE `/structure/niveles/{id}`**  
Respuestas de error
```json
{ "message": "Registro no encontrado." }
```
```json
{ "message": "No se puede eliminar el registro." }
```

**GET `/structure/periodos`**  
Query: `page`, `limit`, `q`
```json
{
  "data": [
    { "id": 1, "name": "2026-1", "start_date": "2026-01-15", "end_date": "2026-06-30" }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```
**POST `/structure/periodos`**
```json
{ "name": "2026-1", "start_date": "2026-01-15", "end_date": "2026-06-30" }
```
**PUT `/structure/periodos/{id}`**  
Respuestas de creacion/actualizacion/lectura
```json
{ "data": { "id": 1, "name": "2026-1", "start_date": "2026-02-01", "end_date": "2026-06-01" } }
```
Respuestas de error de formato de fecha
```json
{
  "message": "Validation failed",
  "errors": { "start_date": ["Formato invalido. Usa YYYY-MM-DD."] }
}
```
**DELETE `/structure/periodos/{id}`**  
Respuestas de error
```json
{ "message": "Registro no encontrado." }
```
```json
{ "message": "No se puede eliminar el registro." }
```

**GET `/structure/carreras`**  
Query: `page`, `limit`, `q`, `is_active`
```json
{
  "data": [
    { "id": 1, "name": "Ingenieria", "is_active": true }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```
**POST `/structure/carreras`**
```json
{ "name": "Ingenieria", "is_active": true }
```
**PUT `/structure/carreras/{id}`**  
Respuestas de creacion/actualizacion/lectura
```json
{ "data": { "id": 1, "name": "Ingenieria", "is_active": true } }
```
**DELETE `/structure/carreras/{id}`**  
Respuestas de error
```json
{ "message": "Registro no encontrado." }
```
```json
{ "message": "No se puede eliminar el registro." }
```

**GET `/structure/asignaturas`**  
Query: `page`, `limit`, `q`, `is_active`
```json
{
  "data": [
    { "id": 1, "name": "Programacion I", "is_active": true }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```
**POST `/structure/asignaturas`**
```json
{ "name": "Programacion I", "is_active": true }
```
**PUT `/structure/asignaturas/{id}`**  
Respuestas de creacion/actualizacion/lectura
```json
{ "data": { "id": 1, "name": "Programacion I", "is_active": true } }
```
**DELETE `/structure/asignaturas/{id}`**  
Respuestas de error
```json
{ "message": "Registro no encontrado." }
```
```json
{ "message": "No se puede eliminar el registro." }
```

**GET `/structure/cursos`**  
Query: `page`, `limit`, `q`, `periodo_id`, `teacher_id`, `sede_jornada_id`, `carrera_id`, `asignatura_id`
```json
{
  "data": [
    {
      "id": 1,
      "name": "Curso 1",
      "capacity": 30,
      "start_date": "2026-02-01",
      "end_date": "2026-06-01",
      "periodo": { "id": 1, "name": "2026-1" },
      "teacher": { "id": 2, "email": "teacher@lms.local", "first_name": "Teacher", "last_name": "One" },
      "sede_jornada": { "id": 1, "name": "Sede A" },
      "carrera": { "id": 1, "name": "Ingenieria" },
      "asignatura": { "id": 1, "name": "Programacion I" }
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```
**POST `/structure/cursos`**
```json
{
  "name": "Curso 1",
  "capacity": 30,
  "start_date": "2026-02-01",
  "end_date": "2026-06-01",
  "periodo_id": 1,
  "teacher_id": 2,
  "sede_jornada_id": 1,
  "carrera_id": 1,
  "asignatura_id": 1
}
```
**PUT `/structure/cursos/{id}`**  
Respuestas de creacion/actualizacion/lectura
```json
{
  "data": {
    "id": 1,
    "name": "Curso 1",
    "capacity": 30,
    "start_date": "2026-02-01",
    "end_date": "2026-06-01",
    "periodo": { "id": 1, "name": "2026-1" },
    "teacher": { "id": 2, "email": "teacher@lms.local", "first_name": "Teacher", "last_name": "One" },
    "sede_jornada": { "id": 1, "name": "Sede A" },
    "carrera": { "id": 1, "name": "Ingenieria" },
    "asignatura": { "id": 1, "name": "Programacion I" }
  }
}
```
Respuestas de error especificas
```json
{ "message": "Periodo no encontrado." }
```
```json
{ "message": "Docente no encontrado." }
```
```json
{ "message": "El usuario no es docente." }
```
```json
{ "message": "Sede jornada no encontrada." }
```
```json
{ "message": "Carrera no encontrada." }
```
```json
{ "message": "Asignatura no encontrada." }
```
**DELETE `/structure/cursos/{id}`**  
Respuestas de error
```json
{ "message": "Registro no encontrado." }
```
```json
{ "message": "No se puede eliminar el registro." }
```## Virtual (ROLE_TEACHER)

**GET `/virtual/cursos`**  
Query: `page`, `limit`, `q`, `curso_id`
Notas:
- `q` busca en nombre del curso y descripcion
```json
{
  "data": [
    {
      "id": 1,
      "description": "Desc",
      "created_at": "2026-02-05 12:00:00",
      "curso": { "id": 1, "name": "Curso 1" }
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```
**POST `/virtual/cursos`**
```json
{ "curso_id": 1, "description": "Desc" }
```
Validaciones:
- `curso_id` requerido y positivo
- `description` opcional (max 2000)
Respuestas
```json
{
  "data": {
    "id": 1,
    "description": "Desc",
    "created_at": "2026-02-05 12:00:00",
    "curso": { "id": 1, "name": "Curso 1" }
  }
}
```
```json
{ "message": "Curso no encontrado." }
```
**PUT `/virtual/cursos/{id}`**
Validaciones:
- `curso_id` si se envia, positivo
- `description` opcional (max 2000)
Respuestas de creacion/actualizacion/lectura
```json
{
  "data": {
    "id": 1,
    "description": "Desc",
    "created_at": "2026-02-05 12:00:00",
    "curso": { "id": 1, "name": "Curso 1" }
  }
}
```

**DELETE `/virtual/cursos/{id}`**
Respuestas de error
```json
{ "message": "Registro no encontrado." }
```
```json
{ "message": "No se puede eliminar el registro." }
```

**GET `/virtual/anuncios`**  
Query: `page`, `limit`, `q`, `curso_virtual_id`
```json
{
  "data": [
    {
      "id": 1,
      "title": "Bienvenida",
      "content": "Hola",
      "created_at": "2026-02-05 12:00:00",
      "updated_at": null,
      "curso_virtual": { "id": 1, "curso_id": 1 },
      "created_by": { "id": 2, "email": "teacher@lms.local", "first_name": "Teacher", "last_name": "One" }
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```
**POST `/virtual/anuncios`**
```json
{ "curso_virtual_id": 1, "title": "Bienvenida", "content": "Hola" }
```
Validaciones:
- `curso_virtual_id` requerido y positivo
- `title` requerido (max 200)
- `content` requerido
Respuestas de creacion/actualizacion/lectura
```json
{
  "data": {
    "id": 1,
    "title": "Bienvenida",
    "content": "Hola",
    "created_at": "2026-02-05 12:00:00",
    "updated_at": null,
    "curso_virtual": { "id": 1, "curso_id": 1 },
    "created_by": { "id": 2, "email": "teacher@lms.local", "first_name": "Teacher", "last_name": "One" }
  }
}
```
```json
{ "message": "Curso virtual no encontrado." }
```
**PUT `/virtual/anuncios/{id}`**
Validaciones:
- `curso_virtual_id` si se envia, positivo
- `title` si se envia, max 200
Respuestas de creacion/actualizacion/lectura
```json
{
  "data": {
    "id": 1,
    "title": "Bienvenida",
    "content": "Hola",
    "created_at": "2026-02-05 12:00:00",
    "updated_at": null,
    "curso_virtual": { "id": 1, "curso_id": 1 },
    "created_by": { "id": 2, "email": "teacher@lms.local", "first_name": "Teacher", "last_name": "One" }
  }
}
```

**DELETE `/virtual/anuncios/{id}`**
Respuestas de error
```json
{ "message": "Registro no encontrado." }
```
```json
{ "message": "No se puede eliminar el registro." }
```

**GET `/virtual/actividades`**  
Query: `page`, `limit`, `q`, `curso_virtual_id`, `type`
```json
{
  "data": [
    {
      "id": 1,
      "type": "FILE",
      "title": "Actividad 1",
      "content": "Sube",
      "youtube_url": null,
      "is_graded": false,
      "due_at": "2026-03-01 12:00:00",
      "created_at": "2026-02-05 12:00:00",
      "curso_virtual": { "id": 1, "curso_id": 1 },
      "file": { "id": 10, "key": "uploads/...", "bucket": "lms", "original_name": "test.txt", "mime_type": "text/plain", "size": 5 },
      "attachments": [
        { "id": 10, "key": "uploads/...", "bucket": "lms", "original_name": "test.txt", "mime_type": "text/plain", "size": 5 }
      ]
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```
**POST `/virtual/actividades`**
```json
{
  "curso_virtual_id": 1,
  "type": "FILE",
  "title": "Actividad 1",
  "content": "Sube",
  "youtube_url": null,
  "file_id": 10,
  "is_graded": false,
  "due_at": "2026-03-01 12:00:00",
  "attachment_ids": [10]
}
```
Validaciones:
- `curso_virtual_id` requerido y positivo
- `type` requerido (`TEXT`, `FILE`, `VIDEO`, `TASK`)
- `title` requerido (max 200)
- `youtube_url` opcional, formato URL
- `file_id` opcional, positivo
- `is_graded` opcional (bool)
- `due_at` opcional (datetime)
- `attachment_ids` opcional (array de ids positivos)
**PUT `/virtual/actividades/{id}`**
```json
{
  "title": "Actividad 1 (editada)",
  "content": "Nuevo contenido",
  "type": "TEXT",
  "is_graded": true,
  "due_at": "2026-03-05 12:00:00",
  "attachment_ids": [10]
}
```
**DELETE `/virtual/actividades/{id}`**
```json
{ "message": "Registro eliminado" }
```
Respuestas de creacion/actualizacion/lectura
```json
{
  "data": {
    "id": 1,
    "type": "FILE",
    "title": "Actividad 1",
    "content": "Sube",
    "youtube_url": null,
    "is_graded": false,
    "due_at": "2026-03-01 12:00:00",
    "created_at": "2026-02-05 12:00:00",
    "curso_virtual": { "id": 1, "curso_id": 1 },
    "file": { "id": 10, "key": "uploads/...", "bucket": "lms", "original_name": "test.txt", "mime_type": "text/plain", "size": 5 },
    "attachments": [
      { "id": 10, "key": "uploads/...", "bucket": "lms", "original_name": "test.txt", "mime_type": "text/plain", "size": 5 }
    ]
  }
}
```
Respuestas de error especificas
```json
{ "message": "Curso virtual no encontrado." }
```
```json
{ "message": "Archivo no encontrado." }
```
```json
{ "message": "Archivo adjunto no encontrado." }
```
```json
{ "message": "Registro no encontrado." }
```
```json
{
  "message": "Validation failed",
  "errors": { "due_at": ["Formato invalido. Usa una fecha/hora valida."] }
}
```
```json
{ "message": "No se puede eliminar el registro." }
```\r\n## Assessments

**GET `/assessments/quizzes`**  
Query: `page`, `limit`, `q`, `curso_virtual_id`
```json
{
  "data": [
    {
      "id": 1,
      "title": "Quiz 1",
      "description": null,
      "start_at": "2026-02-05 00:00:00",
      "end_at": "2026-02-10 23:59:59",
      "time_limit_minutes": 30,
      "curso_virtual": { "id": 1, "curso_id": 1 }
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```
**POST `/assessments/quizzes`** (ROLE_TEACHER)
```json
{
  "curso_virtual_id": 1,
  "title": "Quiz 1",
  "description": null,
  "start_at": "2026-02-05 00:00:00",
  "end_at": "2026-02-10 23:59:59",
  "time_limit_minutes": 30
}
```
Validaciones:
- `curso_virtual_id` requerido y positivo
- `title` requerido (max 200)
- `start_at`/`end_at` opcional (datetime)
- `time_limit_minutes` opcional, positivo
Notas:
- `end_at` no puede ser menor a `start_at`
Respuestas de error
```json
{ "message": "Curso virtual no encontrado." }
```
```json
{ "message": "La fecha fin debe ser posterior a inicio." }
```
```json
{
  "message": "Validation failed",
  "errors": { "start_at": ["Formato invalido. Usa una fecha/hora valida."] }
}
```

**GET `/assessments/questions`**  
Query: `page`, `limit`, `quiz_id`, `type`
```json
{
  "data": [
    {
      "id": 1,
      "type": "SINGLE",
      "prompt": "2+2?",
      "options": ["2", "4"],
      "correct_option": "4",
      "quiz": { "id": 1, "curso_virtual_id": 1 }
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```
**POST `/assessments/questions`** (ROLE_TEACHER)
```json
{
  "quiz_id": 1,
  "type": "SINGLE",
  "prompt": "2+2?",
  "options": ["2", "4"],
  "correct_option": "4"
}
```
Validaciones:
- `quiz_id` requerido y positivo
- `type` requerido (`SINGLE`, `TEXT`)
- `prompt` requerido
- Si `type` es `SINGLE`: `options` array (min 2) y `correct_option` requerido
Respuestas de error
```json
{ "message": "Quiz no encontrado." }
```
```json
{ "message": "Opciones invalidas." }
```
```json
{ "message": "correct_option es requerido." }
```

**GET `/assessments/attempts`** (ROLE_STUDENT)
Query: `page`, `limit`, `quiz_id`, `user_id`
Notas:
- Si es estudiante, solo ve sus propios intentos
- Docente/admin puede filtrar por `user_id`
```json
{
  "data": [
    {
      "id": 1,
      "started_at": "2026-02-05 12:00:00",
      "finished_at": null,
      "score": null,
      "quiz": { "id": 1, "title": "Quiz 1" },
      "user": { "id": 3, "email": "student@lms.local", "first_name": "Student", "last_name": "One" }
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```
**POST `/assessments/attempts`** (ROLE_STUDENT)
```json
{ "quiz_id": 1 }
```
Validaciones:
- `quiz_id` requerido y positivo
Respuestas de error
```json
{ "message": "Quiz no encontrado." }
```
```json
{ "message": "El quiz aun no esta disponible." }
```
```json
{ "message": "El quiz ya no esta disponible." }
```

**POST `/assessments/attempts/{id}/finish`**
Notas:
- Calcula `score` usando solo preguntas `SINGLE`
```json
{
  "data": {
    "id": 1,
    "started_at": "2026-02-05 12:00:00",
    "finished_at": "2026-02-05 12:05:00",
    "score": 80,
    "quiz": { "id": 1, "title": "Quiz 1" },
    "user": { "id": 3, "email": "student@lms.local", "first_name": "Student", "last_name": "One" }
  }
}
```
Respuestas de error
```json
{ "message": "Registro no encontrado." }
```
```json
{ "message": "El intento ya fue finalizado." }
```
```json
{ "message": "Unauthorized" }
```

**GET `/assessments/answers`** (ROLE_STUDENT)
Query: `page`, `limit`, `attempt_id`
Notas:
- Si es estudiante, solo ve sus respuestas
```json
{
  "data": [
    {
      "id": 1,
      "answer_text": "4",
      "is_correct": true,
      "attempt": { "id": 1, "quiz_id": 1, "user_id": 3 },
      "question": { "id": 1, "type": "SINGLE", "prompt": "2+2?" }
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```
**POST `/assessments/answers`** (ROLE_STUDENT)
```json
{ "attempt_id": 1, "question_id": 1, "answer_text": "4" }
```
Validaciones:
- `attempt_id` requerido y positivo
- `question_id` requerido y positivo
- `is_correct` solo docente/admin
Respuestas de error
```json
{ "message": "Intento no encontrado." }
```
```json
{ "message": "Pregunta no encontrada." }
```
```json
{ "message": "La pregunta no pertenece al quiz." }
```
```json
{ "message": "El intento ya fue finalizado." }
```
```json
{ "message": "Unauthorized" }
```\r\n

**GET `/assessments/quizzes`**  
Query: `page`, `limit`, `q`, `curso_virtual_id`
```json
{
  "data": [
    {
      "id": 1,
      "title": "Quiz 1",
      "description": null,
      "start_at": "2026-02-05 00:00:00",
      "end_at": "2026-02-10 23:59:59",
      "time_limit_minutes": 30,
      "curso_virtual": { "id": 1, "curso_id": 1 }
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```
**POST `/assessments/quizzes`** (ROLE_TEACHER)
```json
{
  "curso_virtual_id": 1,
  "title": "Quiz 1",
  "description": null,
  "start_at": "2026-02-05 00:00:00",
  "end_at": "2026-02-10 23:59:59",
  "time_limit_minutes": 30
}
```
Respuestas de creaciÃ³n/actualizaciÃ³n/lectura
```json
{
  "data": {
    "id": 1,
    "title": "Quiz 1",
    "description": null,
    "start_at": "2026-02-05 00:00:00",
    "end_at": "2026-02-10 23:59:59",
    "time_limit_minutes": 30,
    "curso_virtual": { "id": 1, "curso_id": 1 }
  }
}
```
Respuestas de error especÃ­ficas
```json
{ "message": "Curso virtual no encontrado." }
```
```json
{ "message": "La fecha fin debe ser posterior a inicio." }
```

**GET `/assessments/questions`**  
Query: `page`, `limit`, `quiz_id`, `type`
```json
{
  "data": [
    {
      "id": 1,
      "type": "SINGLE",
      "prompt": "2+2?",
      "options": ["3", "4"],
      "correct_option": "4",
      "quiz": { "id": 1, "curso_virtual_id": 1 }
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```
**POST `/assessments/questions`** (ROLE_TEACHER)
```json
{
  "quiz_id": 1,
  "type": "SINGLE",
  "prompt": "2+2?",
  "options": ["3", "4"],
  "correct_option": "4"
}
```
Respuestas de creaciÃ³n/actualizaciÃ³n/lectura
```json
{
  "data": {
    "id": 1,
    "type": "SINGLE",
    "prompt": "2+2?",
    "options": ["3", "4"],
    "correct_option": "4",
    "quiz": { "id": 1, "curso_virtual_id": 1 }
  }
}
```
Respuestas de error especÃ­ficas
```json
{ "message": "Quiz no encontrado." }
```
```json
{ "message": "Opciones invÃ¡lidas." }
```
```json
{ "message": "correct_option es requerido." }
```

**GET `/assessments/attempts`** (ROLE_STUDENT)  
Query: `page`, `limit`, `quiz_id`, `user_id`
```json
{
  "data": [
    {
      "id": 1,
      "started_at": "2026-02-05 12:00:00",
      "finished_at": null,
      "score": null,
      "quiz": { "id": 1, "title": "Quiz 1" },
      "user": { "id": 3, "email": "student@lms.local", "first_name": "Student", "last_name": "One" }
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```
**POST `/assessments/attempts`** (ROLE_STUDENT)
```json
{ "quiz_id": 1 }
```
Respuestas
```json
{
  "data": {
    "id": 1,
    "started_at": "2026-02-05 12:00:00",
    "finished_at": null,
    "score": null,
    "quiz": { "id": 1, "title": "Quiz 1" },
    "user": { "id": 3, "email": "student@lms.local", "first_name": "Student", "last_name": "One" }
  }
}
```
Respuestas de error especÃ­ficas
```json
{ "message": "Quiz no encontrado." }
```
```json
{ "message": "El quiz aÃºn no estÃ¡ disponible." }
```
```json
{ "message": "El quiz ya no estÃ¡ disponible." }
```

**POST `/assessments/attempts/{id}/finish`** (ROLE_STUDENT)
Respuestas
```json
{
  "data": {
    "id": 1,
    "started_at": "2026-02-05 12:00:00",
    "finished_at": "2026-02-05 12:20:00",
    "score": 100,
    "quiz": { "id": 1, "title": "Quiz 1" },
    "user": { "id": 3, "email": "student@lms.local", "first_name": "Student", "last_name": "One" }
  }
}
```
```json
{ "message": "El intento ya fue finalizado." }
```

**GET `/assessments/answers`** (ROLE_STUDENT)  
Query: `page`, `limit`, `attempt_id`
```json
{
  "data": [
    {
      "id": 1,
      "answer_text": "4",
      "is_correct": true,
      "attempt": { "id": 1, "quiz_id": 1, "user_id": 3 },
      "question": { "id": 1, "type": "SINGLE", "prompt": "2+2?" }
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```
**POST `/assessments/answers`** (ROLE_STUDENT)
```json
{ "attempt_id": 1, "question_id": 1, "answer_text": "4" }
```
Respuestas
```json
{
  "data": {
    "id": 1,
    "answer_text": "4",
    "is_correct": true,
    "attempt": { "id": 1, "quiz_id": 1, "user_id": 3 },
    "question": { "id": 1, "type": "SINGLE", "prompt": "2+2?" }
  }
}
```
Respuestas de error especÃ­ficas
```json
{ "message": "Intento no encontrado." }
```
```json
{ "message": "Pregunta no encontrada." }
```
```json
{ "message": "La pregunta no pertenece al quiz." }
```
```json
{ "message": "El intento ya fue finalizado." }
```

## Files (ROLE_ADMIN)

**POST `/files/presign`**
```json
{ "filename": "test.txt", "mime_type": "text/plain", "size": 5 }
```
Respuestas
```json
{
  "data": {
    "url": "http://minio:9000/...",
    "key": "uploads/2026/02/..txt",
    "bucket": "lms",
    "expires_in": 900
  }
}
```

Flujo de upload (browser):
- Llamar `POST /files/presign` y usar la `url` para hacer PUT directo al bucket.
- Luego llamar `POST /files/complete` para registrar el archivo en BD.
- Para que funcione desde navegador, configurar `MINIO_PUBLIC_ENDPOINT` (ej: `http://localhost:9000`).

**POST `/files/complete`**
```json
{
  "key": "uploads/2026/02/..txt",
  "bucket": "lms",
  "original_name": "test.txt",
  "mime_type": "text/plain",
  "size": 5
}
```
Respuestas
```json
{
  "data": {
    "id": 10,
    "key": "uploads/2026/02/..txt",
    "bucket": "lms",
    "original_name": "test.txt",
    "mime_type": "text/plain",
    "size": 5
  }
}
```

**GET `/files/{id}`**
Respuestas
```json
{
  "data": {
    "id": 10,
    "key": "uploads/2026/02/..txt",
    "bucket": "lms",
    "original_name": "test.txt",
    "mime_type": "text/plain",
    "size": 5
  }
}
```
```json
{ "message": "Archivo no encontrado." }
```

## Imports (ROLE_ADMIN)

**POST `/imports/users`**
```json
{ "file_id": 10 }
```
Validaciones:
- `file_id` requerido y positivo
Notas:
- `file_id` debe venir de `/files/complete`
Respuestas
```json
{
  "data": {
    "id": 1,
    "type": "users",
    "status": "pending",
    "total_rows": 0,
    "success_count": 0,
    "error_count": 0,
    "created_at": "2026-02-05 12:00:00",
    "created_by": { "id": 1, "email": "admin@lms.local", "first_name": "Admin", "last_name": "LMS" },
    "result_file": null
  }
}
```
```json
{ "message": "Archivo no encontrado." }
```

**GET `/imports/batches`**
Query: `page`, `limit`, `type`, `status`
```json
{
  "data": [
    {
      "id": 1,
      "type": "users",
      "status": "completed",
      "total_rows": 10,
      "success_count": 9,
      "error_count": 1,
      "created_at": "2026-02-05 12:00:00",
      "created_by": { "id": 1, "email": "admin@lms.local", "first_name": "Admin", "last_name": "LMS" },
      "result_file": { "id": 11, "key": "imports/..csv", "bucket": "lms", "original_name": "result.csv", "mime_type": "text/csv", "size": 1234 }
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "total_pages": 1 }
}
```

**GET `/imports/batches/{id}`**
```json
{
  "data": {
    "id": 1,
    "type": "users",
    "status": "completed",
    "total_rows": 10,
    "success_count": 9,
    "error_count": 1,
    "created_at": "2026-02-05 12:00:00",
    "created_by": { "id": 1, "email": "admin@lms.local", "first_name": "Admin", "last_name": "LMS" },
    "result_file": { "id": 11, "key": "imports/..csv", "bucket": "lms", "original_name": "result.csv", "mime_type": "text/csv", "size": 1234 }
  },
  "errors": [
    { "id": 1, "row_number": 3, "message": "Email invalido", "raw_data": "..." }
  ]
}
```
```json
{ "message": "Batch no encontrado." }
```\r\n## Tracking (ROLE_STUDENT)

**POST `/tracking/heartbeat`**
```json
{ "route": "/virtual/cursos", "course_id": 1, "timestamp": 1738720000 }
```
Validaciones:
- `route` requerido
- `timestamp` requerido (epoch en ms o s)
- `course_id` opcional
Respuestas
```json
{ "message": "ok", "seconds": 15 }
```
```json
{ "message": "Unauthorized" }
```

**GET `/tracking/summary`**
Notas:
- Resumen del usuario autenticado
```json
{
  "data": {
    "total_seconds": 300,
    "by_course": [
      { "curso_id": 1, "curso_name": "Curso 1", "seconds": 300 }
    ]
  }
}
```
```json
{ "message": "Unauthorized" }
```

**GET `/tracking/admin/summary`** (ROLE_ADMIN)  
Query: `from`, `to`, `course_id`, `user_id`
Notas:
- `from` y `to` en formato `YYYY-MM-DD`
```json
{
  "data": {
    "total_seconds": 900,
    "by_course": [
      { "curso_id": 1, "curso_name": "Curso 1", "seconds": 600 },
      { "curso_id": 2, "curso_name": "Curso 2", "seconds": 300 }
    ],
    "by_user": [
      { "user_id": 3, "email": "student@lms.local", "first_name": "Student", "last_name": "One", "seconds": 600 }
    ],
    "by_day": [
      { "day": "2026-02-05", "seconds": 300 },
      { "day": "2026-02-06", "seconds": 600 }
    ],
    "by_route": [
      { "route": "/virtual/cursos", "seconds": 600 }
    ]
  }
}
```

**GET `/tracking/teacher/summary`** (ROLE_TEACHER)  
Query: `from`, `to`, `course_id`, `user_id`
Notas:
- Solo incluye cursos donde el docente autenticado es el profesor del curso
```json
{
  "data": {
    "total_seconds": 600,
    "by_course": [
      { "curso_id": 1, "curso_name": "Curso 1", "seconds": 600 }
    ],
    "by_user": [
      { "user_id": 3, "email": "student@lms.local", "first_name": "Student", "last_name": "One", "seconds": 600 }
    ],
    "by_day": [
      { "day": "2026-02-06", "seconds": 600 }
    ],
    "by_route": [
      { "route": "/virtual/cursos", "seconds": 600 }
    ]
  }
}
```
Respuestas de error
```json
{ "message": "Unauthorized" }
```
```json
{
  "message": "Validation failed",
  "errors": { "from": ["Formato invÃ¡lido. Usa una fecha vÃ¡lida."] }
}
```

## Health

**GET `/health`**
```json
{ "status": "ok" }
```

## Files

**PolÃ­tica MinIO (DEV)**  
Los objetos se mantienen privados; la descarga debe hacerse con URL firmada vÃ­a `/files/{id}/download`.

**POST `/files/presign`**
```json
{ "filename": "test.txt", "mime_type": "text/plain", "size": 5 }
```
Validaciones:
- `filename` requerido
- `mime_type` requerido
- `size` requerido (> 0)
Respuesta
```json
{
  "data": {
    "url": "https://...",
    "key": "uploads/2026/02/....txt",
    "bucket": "lms",
    "expires_in": 900
  }
}
```

**POST `/files/complete`**
```json
{
  "key": "uploads/2026/02/....txt",
  "bucket": "lms",
  "original_name": "test.txt",
  "mime_type": "text/plain",
  "size": 5
}
```
Validaciones:
- `key` requerido
- `bucket` requerido
- `original_name` requerido
- `mime_type` requerido
- `size` requerido (> 0)
Respuesta
```json
{
  "data": {
    "id": 10,
    "key": "uploads/2026/02/....txt",
    "bucket": "lms",
    "original_name": "test.txt",
    "mime_type": "text/plain",
    "size": 5
  }
}
```

**GET `/files/{id}`**
```json
{
  "data": {
    "id": 10,
    "key": "uploads/2026/02/....txt",
    "bucket": "lms",
    "original_name": "test.txt",
    "mime_type": "text/plain",
    "size": 5
  }
}
```
```json
{ "message": "Archivo no encontrado." }
```

**GET `/files/{id}/download`**  
Query: `disposition` (`attachment`|`inline`), `filename`
Notas:
- La URL firmada expira en ~15 min
```json
{
  "data": {
    "url": "https://...",
    "key": "uploads/2026/02/....txt",
    "bucket": "lms",
    "expires_in": 900
  }
}
```
```json
{ "message": "Archivo no encontrado." }
```

**GET `/files/{id}/stream`**
Query: `disposition` (`attachment`|`inline`), `filename`
Notas:
- Devuelve el archivo en binario (stream).
