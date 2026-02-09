# Flujos Funcionales (Basado en "Flujo final.docx")

Este documento resume los flujos funcionales y tipos de actividades/evaluaciones descritos en `docs/Flujo final.docx`.

## 1. Autenticación
1.1 Login
Acciones y elementos clave:
- Ingreso con correo y contraseña.
- Botón `Ingresar`.
- Enlaces: recuperación de contraseña, solicitud de usuario, interés comercial.

1.2 Recuperación de contraseña
Acciones y elementos clave:
- Ingreso de correo.
- Envío de código OTP al correo registrado.

## 2. Dashboard (Administrativo)
Elementos clave:
- Barra superior: logo institucional, buscador de personas (nombre o número de cédula).
- Menú principal: Institucional, Académico, Perfil de usuario.
- Perfil: cargar logo, color de plataforma, cambiar contraseña, cerrar sesión.

## 3. Módulo Institucional
3.1 Estudiantes
Acciones y elementos clave:
- Registro de estudiante con datos personales y académicos.
- Asignación de cursos según programa.
- Acciones: cambiar contraseña, eliminar usuario.

3.2 Docentes
Acciones y elementos clave:
- Registro con datos personales.
- Asignación de roles (docente/administrador/estudiante).
- Acciones: cambiar contraseña, eliminar usuario.

3.3 Administrativos
Acciones y elementos clave:
- Registro similar a docente.

3.4 Importación de personas
Acciones y elementos clave:
- Visualización de la última importación.
- Archivos: datos correctos, erróneos, omitidos.
- Subida de archivos y descarga de plantillas.
- Historial de importaciones.

3.5 Estructuración (Institucional)
Acciones y elementos clave:
- Sede–Jornada: CRUD y modalidades (mañana, tarde, noche, completa, fin de semana).
- Niveles: código, nombre, orden, estado.
- Periodos: nombre, fechas, estado, opción de inactivar estudiantes.

## 4. Módulo Académico
4.1 Educación Virtual
Acciones y elementos clave:
- Listado de cursos virtuales.
- Búsqueda avanzada por periodo, programa, asignatura, estado.

4.2 Vista del curso
Acciones y elementos clave:
- Información general del curso.
- Lateral izquierdo: anuncios (CRUD) y contenido.
- Actividades: CRUD por tipo.

## 5. Tipos de actividades
Tipos y campos clave:
- Texto: título, contenido, adjunto, audio, método de finalización, restricción por fecha.
- Archivo: subida de archivo, método de finalización, restricción por fecha.
- Video: título, URL YouTube, subida de archivo, método de finalización, restricción por fecha.
- Tarea: título, contenido, archivo/audio, calificable, restricción por fecha.

## 6. Evaluaciones – Cuestionarios
Cabecera:
- Título, contenido, archivo/audio, calificable, restricción por fecha.

Tipos de preguntas:
- Selección única.
- Selección múltiple (una o varias correctas, puntaje parcial o total).
- Rellenar espacios.
- Asociación (relación texto/imagen).
- Descriptiva (respuesta abierta).

Configuración:
- Preguntas por página, mostrar puntaje, tiempo máximo, porcentaje de aprobación, intentos disponibles.

## 7. Usuarios del curso
Acciones y elementos clave:
- Listado de participantes.
- Búsqueda.
- Reportes: lista de usuarios, informe de progreso, detalle por estudiante (avance por actividades).
- Accesos y tiempo de conexión.
- Nota: el tiempo solo se contabiliza si la plataforma está en foco.

## 8. Plantillas
Acciones y elementos clave:
- Reutilización de cursos.
- Filtros por nombre, asignatura, estado.
- CRUD de asignaturas dentro de la plantilla.

## 9. Estructuración
Acciones y elementos clave:
- Asignaturas: código, nombre, abreviación, estado.
- Carreras: código, nombre, estado.
- Sede–Jornada: relación jornada–sede con programa.
- Cursos: cupos, fechas, periodo, docente, asignatura.

## 10. Vista del Estudiante
Acciones y elementos clave:
- Login igual al administrativo.
- Dashboard con logo institucional.
- Cursos virtuales: buscador, filtro (todos/completados/en progreso).
- Acceso a cursos y vista de contenido.
- Actividades y evaluaciones con restricciones por fecha.
- Extras: tiempo de conexión, notificaciones (nuevas evaluaciones, anuncios).
- Perfil: cambiar contraseña, cerrar sesión.
