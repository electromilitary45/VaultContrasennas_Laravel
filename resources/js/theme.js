/**
 * Sistema de gestión de temas
 * Soporta: light, dark, auto (detecta preferencia del sistema)
 */

// Inicializar tema al cargar
document.addEventListener('DOMContentLoaded', function() {
    const themeManager = new ThemeManager();
    themeManager.init();
});

class ThemeManager {
    constructor() {
        this.storageKey = 'vault-theme';
        this.themes = ['light', 'dark', 'auto'];
    }

    /**
     * Obtener tema guardado o preferencia del sistema
     */
    getStoredTheme() {
        return localStorage.getItem(this.storageKey) || 'auto';
    }

    /**
     * Guardar tema seleccionado
     */
    setStoredTheme(theme) {
        localStorage.setItem(this.storageKey, theme);
    }

    /**
     * Obtener tema efectivo (resuelve 'auto' a 'light' o 'dark')
     */
    getEffectiveTheme() {
        const stored = this.getStoredTheme();
        
        if (stored === 'auto') {
            // Detectar preferencia del sistema
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        
        return stored;
    }

    /**
     * Aplicar tema al documento
     */
    applyTheme(theme = null) {
        const effectiveTheme = theme ? (theme === 'auto' ? this.getEffectiveTheme() : theme) : this.getEffectiveTheme();
        
        // Aplicar data-bs-theme para Bootstrap
        document.documentElement.setAttribute('data-bs-theme', effectiveTheme);
        
        // También agregar clase para CSS personalizado si es necesario
        document.documentElement.classList.remove('theme-light', 'theme-dark');
        document.documentElement.classList.add(`theme-${effectiveTheme}`);
    }

    /**
     * Inicializar tema
     */
    init() {
        const storedTheme = this.getStoredTheme();
        this.applyTheme(storedTheme);

        // Escuchar cambios en la preferencia del sistema (solo si está en modo auto)
        if (storedTheme === 'auto') {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (this.getStoredTheme() === 'auto') {
                    this.applyTheme('auto');
                }
            });
        }
    }

    /**
     * Cambiar tema
     */
    setTheme(theme) {
        if (!this.themes.includes(theme)) {
            console.error(`Tema inválido: ${theme}`);
            return;
        }

        this.setStoredTheme(theme);
        this.applyTheme(theme);

        // Re-escuchar cambios del sistema si cambió a auto
        if (theme === 'auto') {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (this.getStoredTheme() === 'auto') {
                    this.applyTheme('auto');
                }
            });
        }
    }

    /**
     * Obtener tema actual (puede ser 'auto')
     */
    getCurrentTheme() {
        return this.getStoredTheme();
    }
}

// Exportar para uso global
window.ThemeManager = ThemeManager;

// Crear instancia global
window.themeManager = new ThemeManager();
