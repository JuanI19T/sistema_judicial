# 🌿 Guía de Trabajo con Git & GitHub
### Sistema de Gestión Judicial — Isabella Carrete & Juan Torres

Esta guía explica paso a paso cómo trabajar con el flujo de ramas y Pull Requests definido para el proyecto.

---

## 📋 Estructura de Ramas

| Rama | Uso | ¿Quién programa acá? |
|---|---|---|
| `main` | Rama estable. Solo código testeado y funcionando al 100%. | ❌ Nadie programa directo acá |
| `Juan-Torres` | Rama de trabajo de Juan | ✅ Juan Torres |
| `Isabella-Carrete` | Rama de trabajo de Isabella | ✅ Isabella Carrete |

> 🚫 **Regla de oro**: nunca se hacen commits directo en `main`. Todo cambio pasa primero por la rama personal y después por un Pull Request.

---

## 🔧 Configuración Inicial (una sola vez)

### 1. Clonar el repositorio

```bash
git clone https://github.com/JuanI19T/sistema_judicial.git
cd sistema_judicial
```

### 2. Crear tu rama personal (si todavía no existe)

**Juan:**
```bash
git checkout -b Juan-Torres
git push -u origin Juan-Torres
```

**Isabella:**
```bash
git checkout -b Isabella-Carrete
git push -u origin Isabella-Carrete
```

Si la rama ya existe en GitHub, simplemente:
```bash
git checkout Juan-Torres        
# o Isabella-Carrete
```

---

## 🔁 Flujo de Trabajo Diario

### Paso 1 — Antes de empezar a programar, actualizate

Siempre, **antes de tocar código**, traé los últimos cambios de `main` a tu rama para evitar conflictos grandes más adelante:

```bash
git checkout main
git pull origin main

git checkout Juan-Torres         
# o Isabella-Carrete

git merge main
```

### Paso 2 — Programá tranquilo en tu rama

Trabajá normalmente: creá archivos, editá código, etc. Andá haciendo commits chicos y frecuentes (no un solo commit gigante al final):

```bash
git add .
git commit -m "Agrego formulario de alta de expediente"
```

💡 **Tip de mensajes de commit**: sean descriptivos. En vez de `"cambios"` o `"fix"`, escribí qué hiciste: `"Valido que el número de matrícula sea numérico"`.

### Paso 3 — Subí tu rama a GitHub

```bash
git push origin Juan-Torres     
# o Isabella-Carrete
```

### Paso 4 — Abrí el Pull Request (PR)

1. Entrá a GitHub → pestaña **Pull Requests** → **New Pull Request**.
2. Base: `main` ← Compare: `Juan-Torres` (o `Isabella-Carrete`).
3. Escribí un título claro y una breve descripción de **qué** hiciste y **por qué**.
4. Asigná como revisor a tu compañero/a (Juan revisa a Isabella, e Isabella revisa a Juan).

### Paso 5 — El otro integrante revisa

La otra persona entra al PR y:
- Lee los cambios (pestaña **Files changed**).
- Prueba que funcione si es posible.
- Deja comentarios si algo no está claro o hay un error.
- Si está todo bien → **Approve** y luego **Merge**.
- Si hay que corregir algo → comenta, el autor corrige en su rama y hace push de nuevo (el PR se actualiza solo).

### Paso 6 — Después de mergear

Una vez que el PR se fusionó a `main`, ambos deben actualizar sus ramas locales:

```bash
git checkout main
git pull origin main

git checkout Juan-Torres         
# o Isabella-Carrete
git merge main
```

---

## ⚠️ Cómo evitar conflictos grandes

- Actualizá tu rama con `main` **frecuentemente** (no dejes pasar una semana).
- Si van a tocar el mismo archivo (ej: el diagrama de clases o un mismo módulo), avísense antes por chat para no pisarse el trabajo.
- Commits chicos y frecuentes > un commit enorme al final del proyecto.

---

## 🆘 Resolución de Conflictos (si aparecen)

Si al hacer `git merge main` aparece un conflicto:

```bash
# Git te va a marcar los archivos en conflicto
git status

# Abrí el archivo marcado, vas a ver algo así:
<<<<<<< HEAD
tu código
=======
código de main
>>>>>>> main

# Editá manualmente y dejá la versión correcta (borrando las marcas <<<, ===, >>>)
git add archivo-corregido.php
git commit -m "Resuelvo conflicto entre mi rama y main"
```

Si tienen dudas al resolver un conflicto, es mejor consultarse entre ustedes antes de hacer commit, para no perder trabajo del otro por error.

---

## ✅ Checklist rápido antes de abrir un PR

- [ ] Actualicé mi rama con los últimos cambios de `main`
- [ ] Probé que mi código funciona sin errores
- [ ] Los commits tienen mensajes claros
- [ ] La descripción del PR explica qué hice