# Diccionario de Datos — Sistema de Gestión Judicial

**Proyecto N° 3 — 7° 6ta Programación**
**Prof. Munafo Guillermo**

Este documento describe la estructura de datos del Sistema de Gestión Judicial, en base al Análisis de Requerimientos Unificado (Fase 1 + Ampliación de Alcance). Incluye las entidades principales, sus atributos y la tabla intermedia que resuelve la relación muchos a muchos entre Abogado y Expediente.

## Convenciones

- **PK**: Clave primaria
- **FK**: Clave foránea
- Los campos en **negrita** son clave primaria y/o clave foránea.

---

## 1. Entidades

### 1.1 Abogado

Registro de los abogados del sistema (RF1).

| Nombre del Campo | Tipo    | Descripción                                  |
|-------------------|---------|-----------------------------------------------|
| **a_codigo**      | INT     | Clave primaria, AUTOINCREMENT, NOT NULL       |
| a_nombre          | VARCHAR | NOT NULL                                      |
| a_apellido        | VARCHAR | NOT NULL                                      |
| a_matricula       | INT     | NOT NULL, UNIQUE                              |
| a_calle           | VARCHAR | NOT NULL                                      |
| a_numero          | INT     | NOT NULL                                      |
| a_piso            | INT     | Opcional                                      |
| a_depto           | INT     | Opcional                                      |

### 1.2 Cliente

Registro de los clientes afectados al sistema judicial (RF2).

| Nombre del Campo | Tipo    | Descripción                              |
|-------------------|---------|--------------------------------------------|
| **c_codigo**      | INT     | Clave primaria, AUTOINCREMENT, NOT NULL   |
| c_nombre          | VARCHAR | NOT NULL                                  |
| c_apellido        | VARCHAR | NOT NULL                                  |
| c_calle           | VARCHAR | NOT NULL                                  |
| c_numero          | INT     | NOT NULL                                  |
| c_piso            | INT     | Opcional                                  |
| c_depto           | INT     | Opcional                                  |

### 1.3 Expediente

Cada expediente pertenece a un único cliente y a un único juzgado; los abogados intervinientes se resuelven mediante la tabla intermedia `Abogado_Expediente` (RF4, RF5, RF6, RF7).

| Nombre del Campo        | Tipo     | Descripción                                    |
|--------------------------|----------|--------------------------------------------------|
| **e_codigo**             | INT      | Clave primaria, AUTOINCREMENT, NOT NULL          |
| **c_codigo**             | INT      | Clave foránea (Cliente), NOT NULL                |
| **j_codigo**             | INT      | Clave foránea (Juzgado), NOT NULL                |
| e_caratula               | VARCHAR  | NOT NULL                                          |
| ultima_modificacion      | DATETIME | NOT NULL — se actualiza automáticamente (RF7)    |

### 1.4 Juzgado

Administración de juzgados, cada uno con su fuero de competencia (RF8, RF9).

| Nombre del Campo      | Tipo    | Descripción                                  |
|-------------------------|---------|-------------------------------------------------|
| **j_codigo**            | INT     | Clave primaria, AUTOINCREMENT, NOT NULL         |
| j_nombre                | VARCHAR | NOT NULL                                        |
| j_apellido_juez         | VARCHAR | NOT NULL                                        |
| j_nombre_juez           | VARCHAR | NOT NULL                                        |
| j_calle                 | VARCHAR | NOT NULL                                        |
| j_numero                | INT     | NOT NULL                                        |
| j_piso                  | INT     | Opcional                                        |
| j_depto                 | INT     | Opcional                                        |
| fuero                   | VARCHAR | NOT NULL — competencia del juzgado (ej.: Civil y Comercial, Penal, Laboral, Familia) |

### 1.5 Audiencia

Gestión de audiencias asociadas a un expediente (RF11, RF12, RF13).

| Nombre del Campo   | Tipo     | Descripción                                                     |
|----------------------|----------|--------------------------------------------------------------------|
| **au_codigo**        | INT      | Clave primaria, AUTOINCREMENT, NOT NULL                            |
| au_fecha_hora        | DATETIME | NOT NULL                                                            |
| au_estado            | VARCHAR  | NOT NULL — valores: Pendiente, Realizada, Suspendida               |
| au_tipo              | VARCHAR  | NOT NULL — valores: Mediación, Testimonial, Sentencia              |
| **e_codigo**         | INT      | Clave foránea (Expediente), NOT NULL                               |

> **Nota de diseño:** la Audiencia no incluye un campo `j_codigo` propio. El juzgado en el que se ejecuta la audiencia se obtiene de forma indirecta a través de `Audiencia → Expediente → Juzgado`. Esto es válido porque, según RF6, cada Expediente pertenece de forma estricta a un único Juzgado, por lo que la relación es siempre unívoca y no genera ambigüedad. La validación de superposición horaria del RF13 se resuelve con un JOIN entre `Audiencia`, `Expediente` y `Juzgado`, evitando así duplicar el dato del juzgado en dos tablas.

---

## 2. Tablas de Relaciones

### 2.1 Abogado_Expediente

Tabla intermedia que resuelve la relación muchos a muchos entre Abogado y Expediente, permitiendo registrar co-defensores y conservar el historial de asignaciones (RF3, RF5).

| Nombre del Campo   | Tipo     | Descripción                                                        |
|----------------------|----------|------------------------------------------------------------------------|
| **a_codigo**         | INT      | Clave foránea (Abogado), NOT NULL — 1ra componente de la clave primaria compuesta |
| **e_codigo**         | INT      | Clave foránea (Expediente), NOT NULL — 2da componente de la clave primaria compuesta |
| fecha_asignacion     | DATETIME | NOT NULL — fecha en que el abogado quedó afectado al expediente        |

---

## 3. Resumen de Relaciones entre Entidades

| Relación                          | Cardinalidad        | Referencia |
|------------------------------------|---------------------|------------|
| Abogado ↔ Expediente               | Muchos a Muchos (vía `Abogado_Expediente`) | RF3, RF5 |
| Expediente → Cliente                | N a 1               | RF2, RF5   |
| Expediente → Juzgado                | N a 1 (estricta)    | RF6, RF10  |
| Audiencia → Expediente              | N a 1 (estricta)    | RF12       |
| Audiencia → Juzgado (indirecta, vía Expediente) | N a 1 (unívoca por transitividad) | RF12, RF13 |
| Juzgado → Fuero                     | Atributo simple     | RF9        |
