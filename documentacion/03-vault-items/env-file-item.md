# Item tipo Archivo .env

Tipo de vault item para almacenar y visualizar archivos `.env`. Un item = un archivo completo; el propietario y quienes tengan acceso pueden ver el contenido (visualizador simple con botón Copiar).

**Estado:** Implementado como visualizador. No hay UI para editar visibilidad por línea.

---

## 1. Concepto actual

- **Un item = un archivo .env completo.** El contenido se crea/edita en create/edit y se almacena cifrado.
- **Visualización:** En la vista del item se muestra el contenido en la card "Detalles del Secreto" con botón "Copiar todo".
- **Owner** ve todo el contenido. Si en el secreto existen `visibility_rules`, los receptores ven solo las líneas que les correspondan; si no hay reglas, el filtrado no aplica (comportamiento existente del parser).

---

## 2. Modelo de datos

- **Tipo:** `VaultItem` con `type = 'env_file'`.
- **Secreto (`VaultItemSecret`):**
  - `raw`: texto original del .env.
  - `lines`: array de `{ line_number, content, is_section_header }` (parseado).
  - `visibility_rules`: opcional; si existe, se usa para filtrar qué líneas ve cada usuario/grupo (sin UI de edición).

---

## 3. Formato .env soportado

- Líneas `KEY=value`, comentarios `# ...`, líneas vacías.
- Parseo vía `App\Services\EnvFileParser`.

---

## 4. Referencias

- [README.md](README.md) — Tipos de vault items y arquitectura.
- [documentacion/04-comparticion/](../04-comparticion/) — Compartición con usuarios y grupos.

---

**Última actualización:** 2026-02-19 — Simplificado a solo visualizador; eliminada UI de visibilidad por línea.
