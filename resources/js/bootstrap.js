import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Importar Bootstrap JS
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
