# Sistema de Gestión Judicial

**Proyecto N° 3 — Implementación de Sitios Web Dinámicos**
7° 6ta Programación — Prof. Munafo Guillermo

> Este documento consolida el relevamiento original (**Fase 1**) junto con la ampliación de alcance solicitada por nuevas normativas del *"Juez Supremo"* (**Fase 2**), integrando ambos en un único análisis de requisitos vigente.
>
> Cada requisito se identifica como:
> - 🟢 **ORIGINAL** — sin cambios respecto a la Fase 1
> - 🟡 **MODIFICADO** — alterado por la ampliación
> - 🔵 **NUEVO** — incorporado en la ampliación

---

## 📌 Tabla de Contenidos

- [1. Objetivo del Proyecto](#1-objetivo-del-proyecto)
- [2. Metodología de Trabajo (Git & GitHub)](#2-metodología-de-trabajo-git--github)
- [3. Actividades a Realizar](#3-actividades-a-realizar)
- [4. Requisitos Funcionales Unificados](#4-requisitos-funcionales-unificados)
- [5. Consideraciones de Diseño UML](#5-consideraciones-de-diseño-uml)
- [6. Mejoras Futuras](#6-mejoras-futuras)

---

## 1. Objetivo del Proyecto

El objetivo de esta actividad es modelar un sistema de información y registro para el ámbito judicial. Para ello se utiliza un diagrama de clases UML como base para el diseño e implementación de una página web dinámica que soporte dicho sistema.

La versión inicial (Fase 1) fue aprobada por el Ministerio de Justicia; este documento incorpora la ampliación inmediata de alcance exigida (Fase 2), por lo que el sistema debe **refactorizarse y extenderse** bajo un flujo de trabajo profesional.

---

## 2. Metodología de Trabajo (Git & GitHub)

Para el desarrollo de esta ampliación queda establecido un flujo de trabajo obligatorio con control de versiones. **Queda prohibido el intercambio de código por mail o carpetas compartidas.**

- 🚫 **Prohibido programar en `main`**: la rama `main` solo contendrá código estable y testeado que funcione al 100%.
- 🌿 **Ramas por integrante**: cada integrante crea una rama específica para su propio trabajo, de forma tal que se pueda reconocer qué es lo que hizo cada uno y en qué forma.
- ✅ **Pull Requests obligatorios**: ninguna funcionalidad se fusiona directamente a `main`; debe abrirse un PR en GitHub para que un compañero revise el código, valide que no rompa nada y autorice el impacto en la rama principal.

---

## 3. Actividades a Realizar

- [ ] **Identificación de clases**: reconocer las entidades principales del sistema (abogado, cliente, expediente, juzgado, audiencia, etc.) y definirlas como clases.
- [ ] **Definición de atributos**: para cada clase, determinar los datos que la describen (código, nombre, dirección, número de matrícula, número de expediente, carátula, juez a cargo, fuero, fecha de audiencia, etc.).
- [ ] **Establecimiento de relaciones**: especificar asociaciones, herencia, agregación o composición entre clases con notación UML correcta (incluyendo la nueva relación muchos a muchos entre Abogado y Cliente/Expediente).
- [ ] **Creación del diagrama de clases UML**: elaborar el diagrama completo (Lucidchart, draw.io, StarUML u otra herramienta) con clases, atributos, métodos y relaciones actualizadas.
- [ ] **Diseño e implementación de la página web**: construir el sitio dinámico que implemente las funcionalidades descriptas, siguiendo el flujo de trabajo Git/GitHub establecido en la sección 2.

---

## 4. Requisitos Funcionales Unificados

### 4.1 Gestión de Abogados y Clientes

| # | Requisito | Estado |
|---|---|---|
| RF1 | El sistema debe permitir el registro de abogados, almacenando obligatoriamente su código, nombre, dirección y número de matrícula. | 🟢 ORIGINAL |
| RF2 | El sistema debe permitir el registro de los clientes afectados al sistema judicial, almacenando su código, nombre y dirección. | 🟢 ORIGINAL |
| RF3 | El sistema debe permitir asociar y determinar de forma explícita con qué cliente/expediente está trabajando cada abogado en un momento dado. La relación Abogado–Cliente/Expediente pasa de ser 1 a N a ser de Muchos a Muchos, dado que ahora dos o más abogados (co-defensores) pueden trabajar en pareja sobre un mismo expediente. Se requiere una tabla intermedia (ej.: `Abogado_Expediente`) que registre cada intervención, de forma de no perder el historial de asignaciones a lo largo del tiempo. | 🟡 MODIFICADO |

### 4.2 Gestión de Expedientes y Casos

| # | Requisito | Estado |
|---|---|---|
| RF4 | El sistema debe permitir la creación y mantenimiento de expedientes por cada caso que atienda el juzgado. | 🟢 ORIGINAL |
| RF5 | Cada expediente registrado debe estar compuesto obligatoriamente por un número de expediente, una carátula, el cliente involucrado y el/los abogado(s) afectado(s) al mismo (uno o más, en función de la nueva relación de co-defensores). | 🟡 MODIFICADO |
| RF6 | El sistema debe garantizar que cada expediente pertenezca de forma estricta a un único juzgado. | 🟢 ORIGINAL |
| RF7 | El sistema debe almacenar de forma automática la fecha y hora de la última modificación o actualización realizada sobre cada expediente, sin intervención manual del usuario. | 🔵 NUEVO |

### 4.3 Gestión de Juzgados

| # | Requisito | Estado |
|---|---|---|
| RF8 | El sistema debe permitir la administración de los diferentes juzgados, registrando por cada uno su código de juzgado, nombre, dirección y el nombre del juez a cargo. | 🟢 ORIGINAL |
| RF9 | Cada juzgado debe tener asignado un atributo Fuero que determine su competencia específica (ej.: Civil y Comercial, Penal, Laboral, Familia). | 🔵 NUEVO |
| RF10 | El sistema debe implementar la funcionalidad **"Sortear Juzgado"**: al dar de alta un expediente, no se permitirá seleccionar el juzgado manualmente. El sistema deberá buscar de forma automatizada, dentro del fuero correspondiente, el juzgado con la menor cantidad de expedientes activos y asignárselo dinámicamente (balanceo de carga). | 🔵 NUEVO |

### 4.4 Gestión de Agenda y Audiencias

| # | Requisito | Estado |
|---|---|---|
| RF11 | El sistema debe permitir la gestión de una entidad Audiencia, con los atributos: código, fecha, hora, estado (Pendiente, Realizada, Suspendida) y tipo (Mediación, Testimonial, Sentencia). | 🔵 NUEVO |
| RF12 | Toda Audiencia debe pertenecer de forma estricta a un único Expediente y ejecutarse físicamente en un Juzgado específico. | 🔵 NUEVO |
| RF13 | El sistema debe validar, tanto en backend como en frontend, que no se superpongan audiencias: se bloqueará el registro si el juzgado ya tiene otra audiencia en ese rango horario, o si el abogado defensor ya está afectado a otra audiencia el mismo día y a la misma hora en un juzgado diferente. | 🔵 NUEVO |

### 4.5 Seguridad y Validación (Transversales)

| # | Requisito | Estado |
|---|---|---|
| RF14 | El sistema debe validar los datos ingresados en los formularios del sitio web (campos obligatorios como matrículas, códigos y formatos de expedientes). | 🟢 ORIGINAL |
| RF15 | Solo los usuarios autorizados (operadores del sistema / abogados) deben acceder a las funcionalidades de gestión según los permisos que tengan asignados. | 🟢 ORIGINAL |

### 4.6 Extras y Mejoras Posibles (Opcionales)

| # | Requisito | Estado |
|---|---|---|
| RF16 | Buscador avanzado de expedientes por carátula, abogado asignado o juzgado interviniente. | 🟢 ORIGINAL |
| RF17 | Panel visual para el abogado, con listado de expedientes y clientes bajo su tutela (contemplando co-defensores). | 🟢 ORIGINAL |
| RF18 | Reporte o historial de casos activos clasificados por cada juzgado y, opcionalmente, por fuero. | 🟢 ORIGINAL |

---

## 5. Consideraciones de Diseño UML

- **Abogado ↔ Cliente/Expediente**: relación de Muchos a Muchos (antes 1 a N), materializada mediante una clase asociativa/tabla intermedia que conserve el historial de intervenciones de cada co-defensor *(RF3, RF5)*.
- **Expediente → Juzgado**: relación de asociación fuerte, estricta y obligatoria *(RF6)*; cada expediente pertenece a un único juzgado, asignado automáticamente por el sorteo *(RF10)*.
- **Juzgado → Fuero**: atributo o, según nivel de normalización deseado, entidad propia si se requiere administrar fueros de forma independiente *(RF9)*.
- **Audiencia → Expediente**: asociación estricta 1 a N (un expediente puede tener varias audiencias, cada audiencia pertenece a un único expediente) *(RF12)*.
- **Audiencia → Juzgado**: asociación que indica dónde se ejecuta físicamente la audiencia, sujeta a validación de disponibilidad horaria *(RF12, RF13)*.

---

## 6. Mejoras Futuras

- 🔐 **Sistema de autenticación** mediante usuarios y contraseñas. Esto permitiría incorporar una tabla `Usuario` con distintos roles (**Administrador**, **Operador**, **Abogado**), aplicando permisos de acceso según el perfil de cada usuario y cumpliendo plenamente el requisito **RF15**.

---

## 📄 Licencia

Proyecto académico — 7° 6ta Programación.
