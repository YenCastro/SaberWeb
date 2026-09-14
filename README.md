# 🧠 SaberWeb

**SaberWeb** es una aplicación web para la realización de **simulacros tipo ICFES y UNAL**, desarrollada como proyecto académico para el **SENA**.

La plataforma busca ofrecer una experiencia similar a un examen real, permitiendo a los estudiantes realizar simulacros completos de manera continua, responder preguntas, consultar sus resultados e historial y reportar errores o sugerencias.

---

## 🚀 Características

### 👨‍🎓 Estudiantes

* Registro e inicio de sesión.
* Consulta y edición del perfil.
* Visualización de simulacros disponibles.
* Realización de simulacros con límite de tiempo.
* Examen realizado de manera continua.
* Visualización del área correspondiente a cada pregunta.
* Registro de respuestas.
* Consulta de resultados.
* Historial de simulacros realizados.
* Notificaciones.
* Reporte de errores en preguntas.
* Envío de sugerencias.

### 👨‍💼 Administrador

* Gestión de simulacros.
* Creación y edición de simulacros.
* Creación y edición de preguntas.
* Gestión de opciones de respuesta.
* Definición de respuestas correctas.
* Publicación de simulacros.
* Gestión de reportes y sugerencias de estudiantes.

---

## 📝 Simulacros

Los simulacros se realizan **de forma continua**, sin separar el examen en diferentes secciones por área, solo se separan por sesiones en caso del ICFES y todo seguido para el tipo UNAL.

Cada pregunta contiene información sobre el área a la que pertenece, permitiendo mostrarla durante el examen.

Por ejemplo:

```text
Pregunta 37 de 180
Área: Matemáticas

¿Cuál de las siguientes opciones...?

A. ...
B. ...
C. ...
D. ...
```

De esta manera, el estudiante mantiene la experiencia de un examen completo mientras puede identificar el área de cada pregunta.

---

## 🛠️ Tecnologías

| Tecnología | Uso                                        |
| ---------- | ------------------------------------------ |
| HTML5      | Estructura de la aplicación                |
| CSS3       | Diseño y estilos                           |
| JavaScript | Interactividad y funcionalidades dinámicas |
| PHP        | Lógica del servidor                        |
| MySQL      | Base de datos                              |
| phpMyAdmin | Administración de la base de datos         |

---

## 🗄️ Base de datos

El proyecto utiliza una base de datos MySQL llamada:

```text
saberweb
```

Principales tablas:

```text
cuentas
simulacros
preguntas
opciones_rta
resultados
respuestas_estudiante
notificaciones
reportes
pruebas
```

### Relaciones principales

```text
cuentas
   │
   ├── resultados
   │      └── respuestas_estudiante
   │
   ├── notificaciones
   │
   └── reportes
          │
          └── preguntas
                 │
                 └── opciones
                 
simulacros
    │
    └── preguntas
```

---

## 📂 Estructura del proyecto

```text
SaberWeb/
│
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
│
├── config/
│   └── conexion.php
│
├── controllers/
│
├── models/
│
├── views/
│   ├── inicio/
│   ├── login/
│   ├── registro/
│   ├── estudiante/
│   └── administrador/
│
├── index.php
└── README.md
```

---

## ⚙️ Instalación

### 1. Clonar el repositorio

```bash
git clone URL_DEL_REPOSITORIO
```

### 2. Ubicar el proyecto

Si utilizas **XAMPP**, coloca el proyecto dentro de:

```text
C:\xampp\htdocs\
```

### 3. Crear la base de datos

Abre **phpMyAdmin** y crea la base de datos:

```text
saberweb
```

Después ejecuta el script SQL incluido en el proyecto.

### 4. Configurar la conexión

Edita:

```text
config/conexion.php
```

y establece los datos correspondientes a tu servidor MySQL.

### 5. Iniciar XAMPP

Activa:

* Apache
* MySQL

### 6. Abrir el proyecto

En el navegador:

```text
http://localhost/SaberWeb/
```

---

## 🔐 Roles

El sistema cuenta con dos roles:

* **Estudiante:** puede realizar y consultar simulacros.
* **Administrador:** gestiona simulacros, preguntas, respuestas y reportes.

Los usuarios que visitan la plataforma sin registrarse **no constituyen un rol de la base de datos**.

---

## 📌 Estado del proyecto

🚧 **En desarrollo**

Actualmente se encuentra en proceso de construcción e implementación de sus diferentes funcionalidades.

### Próximas funcionalidades

* [ ] Gestión de sesiones y roles.
* [ ] Perfil del estudiante.
* [ ] Gestión de simulacros.
* [ ] Realización del examen.
* [ ] Temporizador.
* [ ] Registro de respuestas.
* [ ] Cálculo de resultados.
* [ ] Historial.
* [ ] Notificaciones.
* [ ] Sistema de reportes.
* [ ] Panel de administración.
* [ ] Diseño responsive.
* [ ] Pruebas y optimización.

---

## 👨‍💻 Autora

**Alexandra Castro**

Proyecto académico desarrollado para el **SENA**.

---

> 📚 **SaberWeb** — Practica, prepárate y mejora tus resultados.
