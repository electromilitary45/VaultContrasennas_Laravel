# Reglas de Diseño - OCIANN Vault

## Estilo Visual: Minimalista tipo Apple

### Principios Fundamentales

1. **NO usar CSS/SCSS personalizado**
   - Solo usar clases de Bootstrap 5
   - No crear archivos CSS/SCSS adicionales
   - No usar estilos inline (excepto casos muy específicos justificados)
   - Si necesitas algo que Bootstrap no ofrece, usar utilidades de Bootstrap o reconsiderar el diseño

2. **Estética Minimalista tipo Apple**
   - Espacios amplios y respirables
   - Tipografía limpia y legible
   - Colores neutros y sutiles
   - Bordes mínimos o sin bordes
   - Sombras sutiles y elegantes
   - Transiciones suaves

3. **Paleta de Colores (Bootstrap)**
   - Fondo principal: `bg-white` o `bg-light`
   - Texto principal: `text-dark` o `text-body`
   - Acentos: `text-primary`, `text-secondary`
   - Bordes: `border-light` o `border-secondary` con opacidad baja
   - Evitar colores muy saturados

4. **Espaciado**
   - Usar clases de spacing de Bootstrap: `p-*`, `m-*`, `gap-*`
   - Preferir espaciado generoso: `p-4`, `p-5`, `mb-4`, `mb-5`
   - Evitar elementos muy juntos

5. **Tipografía**
   - Usar clases de Bootstrap: `display-*`, `h1`-`h6`, `lead`, `text-*`
   - Preferir tamaños legibles
   - Usar `fw-light` o `fw-normal` para títulos grandes
   - Evitar `fw-bold` excesivo

6. **Componentes**
   - Botones: `btn btn-outline-primary` o `btn btn-link` para estilo minimalista
   - Cards: `card` con `border-0` y `shadow-sm` o sin sombra
   - Formularios: `form-control` con `border-light` o bordes sutiles
   - Navbar: `navbar` con `bg-white` o `bg-light`, sin sombras fuertes

7. **Bordes y Sombras**
   - Bordes: `border-0` o `border border-light`
   - Sombras: `shadow-sm` o `shadow-none`
   - Evitar `shadow-lg` o `shadow-xl` excepto en casos especiales

8. **Transiciones**
   - Usar clases de Bootstrap para hover: `transition`, `text-decoration-none`
   - Efectos sutiles en interacciones

### Ejemplos de Clases Bootstrap Recomendadas

**Contenedores:**
- `container` o `container-fluid`
- `p-4`, `p-5` para padding generoso
- `mb-4`, `mb-5` para márgenes verticales

**Tipografía:**
- `display-1`, `display-2` para títulos grandes
- `h1`, `h2` con `fw-light` o `fw-normal`
- `lead` para texto destacado
- `text-muted` para texto secundario

**Botones:**
- `btn btn-outline-primary` - estilo minimalista
- `btn btn-link` - estilo texto
- `btn btn-sm` - tamaño pequeño

**Cards:**
- `card border-0 shadow-sm` - minimalista
- `card-body p-4` o `p-5` - padding generoso

**Formularios:**
- `form-control border-light` - bordes sutiles
- `form-label` con espaciado adecuado

### Modales en lugar de alertas JS

**Usar siempre modales (Bootstrap) en lugar de `alert()` o `confirm()` nativos.**

- **Confirmaciones** (acciones destructivas o críticas): usar el componente `<x-confirm-modal>` junto con un formulario, o `showConfirm({ title, message, onConfirm })` desde JS cuando la acción sea vía fetch.
- **Avisos** (errores, información): usar `showAlert('Título', 'Mensaje')` desde JS. Los modales globales se incluyen en los layouts app y admin.

❌ NO usar `alert()`, `confirm()`, `window.alert`, `window.confirm` ni `onsubmit="return confirm(...)"` / `onclick="return confirm(...)"`.

### Prohibiciones

❌ NO crear archivos CSS/SCSS personalizados
❌ NO usar estilos inline extensos
❌ NO usar colores muy saturados
❌ NO usar sombras fuertes (`shadow-lg`, `shadow-xl`)
❌ NO usar bordes gruesos
❌ NO usar espaciado muy compacto
❌ NO usar tipografía muy pesada (`fw-bold` en exceso)

### Cuando Consultar Este Archivo

- Antes de crear cualquier componente visual
- Cuando necesites decidir qué clases de Bootstrap usar
- Si sientes la tentación de crear CSS personalizado
- Al revisar diseños existentes

### Mejores Prácticas UI/UX

1. **Accesibilidad:**
   - Usar etiquetas semánticas de HTML
   - Agregar `aria-label` cuando sea necesario
   - Asegurar contraste adecuado de colores
   - Navegación por teclado funcional

2. **Feedback al usuario:**
   - Mostrar mensajes de éxito/error claros
   - Indicadores de carga para operaciones asíncronas
   - Confirmaciones para acciones destructivas
   - Validación en tiempo real cuando sea posible

3. **Consistencia:**
   - Mantener el mismo estilo en toda la aplicación
   - Usar componentes Bootstrap de forma consistente
   - Seguir el diseño minimalista tipo Apple

4. **Rendimiento:**
   - Lazy loading de imágenes
   - Paginación para listas largas
   - Optimizar consultas N+1
   - Usar caché cuando sea apropiado

5. **Responsive:**
   - Diseño mobile-first
   - Probar en diferentes tamaños de pantalla
   - Usar breakpoints de Bootstrap correctamente

6. **Seguridad en UI:**
   - No mostrar información sensible en URLs
   - Ocultar contraseñas por defecto
   - Protección CSRF en todos los formularios
   - Validación tanto en frontend como backend

### Documentación Relacionada

- Ver `.cursor/rules/documentation-rules.md` para reglas de documentación
- Ver `.cursor/rules/code-standards.md` para estándares de código, clean code, MVC y comentarios
- La documentación del proyecto se organiza en `documentacion/` por módulos

---

**Última actualización:** Modales en lugar de alert/confirm; regla añadida.
