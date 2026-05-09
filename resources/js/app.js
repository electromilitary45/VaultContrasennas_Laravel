import './bootstrap';
import './theme';
import './modals';
import './notifications';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Configurar Laravel Echo para WebSockets (Reverb)
if (window.userId && window.reverbAppKey) {
    window.Pusher = Pusher;
    
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: window.reverbAppKey,
        wsHost: window.reverbHost,
        wsPort: window.reverbPort,
        wssPort: window.reverbPort,
        forceTLS: window.reverbScheme === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            },
        },
    });

    // Escuchar canal privado del usuario para notificaciones
    window.Echo.private(`App.Models.User.${window.userId}`)
        .listen('.notification.created', (data) => {
            // Disparar evento personalizado para que otros módulos puedan escucharlo
            window.dispatchEvent(new CustomEvent('notification-received', { detail: data }));
        });
}

import Alpine from 'alpinejs';

// Plugin de Alpine para gestión de temas
Alpine.data('theme', () => ({
    currentTheme: 'auto',
    
    init() {
        // Obtener tema guardado
        this.currentTheme = window.themeManager.getStoredTheme();
        
        // Aplicar tema inicial
        window.themeManager.applyTheme(this.currentTheme);
    },
    
    setTheme(theme) {
        this.currentTheme = theme;
        window.themeManager.setTheme(theme);
    },
    
    getThemeIcon() {
        switch(this.currentTheme) {
            case 'light':
                return 'bi-sun-fill';
            case 'dark':
                return 'bi-moon-fill';
            case 'auto':
            default:
                return 'bi-circle-half';
        }
    },
    
    getThemeLabel() {
        switch(this.currentTheme) {
            case 'light':
                return 'Claro';
            case 'dark':
                return 'Oscuro';
            case 'auto':
            default:
                return 'Automático';
        }
    }
}));

window.Alpine = Alpine;

Alpine.start();
