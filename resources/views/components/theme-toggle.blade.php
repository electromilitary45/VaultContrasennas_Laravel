<!-- Toggle de tema - Versión única recomendada -->
<div x-data="theme()" x-init="init()" class="d-flex align-items-center">
    <div class="dropdown">
        <button 
            type="button" 
            class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center gap-1" 
            id="themeToggleDropdown" 
            data-bs-toggle="dropdown" 
            aria-expanded="false"
            title="Cambiar tema"
        >
            <i :class="getThemeIcon()"></i>
            <span class="d-none d-sm-inline">Tema</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm" aria-labelledby="themeToggleDropdown">
            <li>
                <h6 class="dropdown-header d-flex align-items-center gap-2 small">
                    <i class="bi bi-palette"></i>
                    <span>Modo de visualización</span>
                </h6>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <button 
                    type="button" 
                    class="dropdown-item d-flex align-items-center gap-2"
                    :class="{ 'active': currentTheme === 'light' }"
                    @click="setTheme('light')"
                >
                    <i class="bi bi-sun-fill"></i>
                    <span>Claro</span>
                    <i class="bi bi-check ms-auto" x-show="currentTheme === 'light'"></i>
                </button>
            </li>
            <li>
                <button 
                    type="button" 
                    class="dropdown-item d-flex align-items-center gap-2"
                    :class="{ 'active': currentTheme === 'dark' }"
                    @click="setTheme('dark')"
                >
                    <i class="bi bi-moon-fill"></i>
                    <span>Oscuro</span>
                    <i class="bi bi-check ms-auto" x-show="currentTheme === 'dark'"></i>
                </button>
            </li>
            <li>
                <button 
                    type="button" 
                    class="dropdown-item d-flex align-items-center gap-2"
                    :class="{ 'active': currentTheme === 'auto' }"
                    @click="setTheme('auto')"
                >
                    <i class="bi bi-circle-half"></i>
                    <span>Automático</span>
                    <i class="bi bi-check ms-auto" x-show="currentTheme === 'auto'"></i>
                </button>
            </li>
        </ul>
    </div>
</div>
