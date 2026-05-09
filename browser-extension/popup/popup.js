/**
 * Popup OCIANN Vault - Consume API con sesión web (credentials: 'include').
 * Por defecto usa el dominio de producción; la URL se puede cambiar en Opciones de desarrollador.
 * Soporta tema claro/oscuro (Bootstrap data-bs-theme).
 */

const DEFAULT_VAULT_URL = '__VAULT_BASE_URL__';
const PRODUCTION_VAULT_URL = 'https://vault.ocianncloud.com';
const THEME_KEY = 'theme';

function getInjectedDefaultUrl() {
  return (DEFAULT_VAULT_URL.startsWith('http') ? DEFAULT_VAULT_URL : null) || PRODUCTION_VAULT_URL;
}

async function getTheme() {
  const { [THEME_KEY]: theme } = await chrome.storage.local.get(THEME_KEY);
  return theme || 'system';
}

function setTheme(theme) {
  return chrome.storage.local.set({ [THEME_KEY]: theme });
}

function getResolvedTheme() {
  return document.documentElement.getAttribute('data-bs-theme') || 'light';
}

function applyTheme(theme) {
  const resolved = theme === 'system'
    ? (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
    : theme;
  document.documentElement.setAttribute('data-bs-theme', resolved);
  const icon = document.getElementById('themeIcon');
  if (icon) icon.textContent = resolved === 'dark' ? '☀' : '☽';
  document.querySelectorAll('.theme-option').forEach((el) => {
    el.classList.toggle('active', el.getAttribute('data-theme') === theme);
  });
}

async function initTheme() {
  const theme = await getTheme();
  applyTheme(theme);
  document.querySelectorAll('.theme-option').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const t = btn.getAttribute('data-theme');
      await setTheme(t);
      applyTheme(t);
    });
  });
  if (theme === 'system' && window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => applyTheme('system'));
  }
}

async function getDeveloperOptionsEnabled() {
  const { developerOptionsEnabled } = await chrome.storage.local.get('developerOptionsEnabled');
  return !!developerOptionsEnabled;
}

function setDeveloperOptionsEnabled(enabled) {
  return chrome.storage.local.set({ developerOptionsEnabled: enabled });
}

async function getVaultBaseUrl() {
  const devEnabled = await getDeveloperOptionsEnabled();
  if (!devEnabled) return getInjectedDefaultUrl();
  const { vaultBaseUrl } = await chrome.storage.local.get('vaultBaseUrl');
  return vaultBaseUrl || getInjectedDefaultUrl();
}

function setVaultBaseUrl(url) {
  return chrome.storage.local.set({ vaultBaseUrl: url });
}

async function apiGet(path, options = {}) {
  const base = await getVaultBaseUrl();
  const url = base.replace(/\/$/, '') + path;
  const res = await fetch(url, {
    method: 'GET',
    credentials: 'include',
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    ...options,
  });
  if (!res.ok) {
    if (res.status === 401 || res.status === 302) return null;
    throw new Error(`HTTP ${res.status}`);
  }
  return res.json();
}

function showPanel(id) {
  document.querySelectorAll('.panel').forEach((el) => el.classList.add('d-none'));
  const panel = document.getElementById(id);
  if (panel) panel.classList.remove('d-none');
}

function setStatus(text, isError = false) {
  const el = document.getElementById('status');
  if (!el) return;
  el.textContent = text;
  el.className = isError ? 'text-danger small mb-0' : 'text-muted small mb-0';
}

function showAlert(message) {
  const msgEl = document.getElementById('alertModalMessage');
  const modalEl = document.getElementById('alertModal');
  if (msgEl && modalEl) {
    msgEl.textContent = message;
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
  }
}

/**
 * Actualiza el badge del icono de la extensión (número de items o vacío si no hay sesión).
 * @param {number|null} total - Cantidad de items del vault, o null si no hay sesión.
 */
function updateBadge(total) {
  if (typeof chrome.action === 'undefined') return;
  if (total === null || total === undefined) {
    chrome.action.setBadgeText({ text: '' });
    chrome.action.setBadgeBackgroundColor({ color: '#6c757d' });
    return;
  }
  const text = total > 99 ? '99+' : String(total);
  chrome.action.setBadgeText({ text });
  chrome.action.setBadgeBackgroundColor({ color: '#0d6efd' });
}

async function loadItems() {
  const listEl = document.getElementById('itemsList');
  const emptyEl = document.getElementById('emptyState');
  const loadingEl = document.getElementById('loadingState');
  if (listEl) listEl.innerHTML = '';
  if (emptyEl) emptyEl.classList.add('d-none');
  if (loadingEl) {
    loadingEl.classList.remove('d-none');
    listEl?.classList.add('d-none');
  }

  const data = await apiGet('/api/extension/vault/items');

  if (loadingEl) {
    loadingEl.classList.add('d-none');
    listEl?.classList.remove('d-none');
  }
  if (!data) {
    updateBadge(null);
    showPanel('loginRequired');
    document.getElementById('openVault').href = await getVaultBaseUrl();
    setStatus('No hay sesión. Abre el vault e inicia sesión.');
    return;
  }

  updateBadge(data.total);
  showPanel('mainPanel');
  setStatus(`${data.total} item(s)`);
  renderItems(data.items || []);
}

function renderItems(items) {
  const list = document.getElementById('itemsList');
  const empty = document.getElementById('emptyState');
  list.innerHTML = '';

  if (items.length === 0) {
    empty.classList.remove('d-none');
    return;
  }
  empty.classList.add('d-none');

  const shareContextLabels = { shared_user: 'Compartido', shared_group: 'En grupo' };

  items.forEach((item) => {
    const li = document.createElement('li');
    li.dataset.id = item.id;
    li.className = 'card border-0 shadow-sm rounded-3 mb-3 item-card';
    const shareLabel = item.share_context && item.share_context !== 'own' ? shareContextLabels[item.share_context] || '' : '';
    li.innerHTML = `
      <div class="card-body p-3">
        <div class="d-flex flex-wrap align-items-center gap-2 small mb-2">
          <span class="item-title flex-grow-1 text-truncate text-body fw-medium" title="${escapeHtml(item.title)}">${escapeHtml(item.title)}</span>
          ${item.username ? `<span class="item-username visually-hidden">${escapeHtml(item.username)}</span>` : ''}
          <span class="badge rounded-pill bg-secondary text-uppercase small">${escapeHtml(item.type)}</span>
          ${shareLabel ? `<span class="badge rounded-pill bg-body-secondary text-body border border-secondary text-uppercase small">${escapeHtml(shareLabel)}</span>` : ''}
        </div>
        <div class="d-flex gap-2">
          ${item.type === 'auth' ? '<button type="button" class="btn btn-outline-secondary copy-username btn-sm btn-pill" title="Copiar usuario">Usuario</button><button type="button" class="btn btn-primary copy-password btn-sm btn-pill" title="Copiar contraseña">Contraseña</button>' : '<button type="button" class="btn btn-primary copy-password btn-sm btn-pill" title="Ver/copiar">Copiar</button>'}
        </div>
      </div>
    `;

    li.querySelector('.copy-username')?.addEventListener('click', (e) => {
      e.stopPropagation();
      copyItemField(item.id, 'username');
    });
    li.querySelector('.copy-password')?.addEventListener('click', (e) => {
      e.stopPropagation();
      copyItemField(item.id, 'password');
    });

    list.appendChild(li);
  });
}

function escapeHtml(s) {
  if (s == null) return '';
  const div = document.createElement('div');
  div.textContent = s;
  return div.innerHTML;
}

async function copyItemField(itemId, field) {
  try {
    const data = await apiGet(`/api/extension/vault/items/${itemId}`);
    if (!data || !data.secret) {
      showAlert('No se pudo obtener el item. ¿Sesión activa?');
      return;
    }
    const secret = data.secret;
    let text = '';
    if (field === 'username') {
      text = secret.username || secret.cardholder_name || '';
    } else if (field === 'password') {
      text = secret.env_content || secret.password || secret.api_key || secret.security_code || secret.passphrase || JSON.stringify(secret);
    }
    if (text && typeof text === 'string') {
      await navigator.clipboard.writeText(text);
      setStatus('Copiado al portapapeles', false);
      setTimeout(() => setStatus(''), 2000);
    }
  } catch (err) {
    setStatus('Error al copiar', true);
    console.error(err);
  }
}

// Búsqueda en la lista actual (filtrado en cliente para MVP)
function onSearchInput() {
  const q = document.getElementById('search').value.trim().toLowerCase();
  const items = Array.from(document.getElementById('itemsList').children);
  items.forEach((li) => {
    const title = (li.querySelector('.item-title')?.textContent || '').toLowerCase();
    const username = (li.querySelector('.item-username')?.textContent || '').toLowerCase();
    const show = !q || title.includes(q) || username.includes(q);
    li.style.display = show ? '' : 'none';
  });
  document.getElementById('emptyState').classList.toggle('d-none', items.some((li) => li.style.display !== 'none'));
}

function syncVaultLinks(baseUrl) {
  const path = baseUrl.replace(/\/$/, '') + '/vault';
  const linkMain = document.getElementById('linkIntegrations');
  const linkSettings = document.getElementById('linkIntegrationsSettings');
  if (linkMain) linkMain.href = path;
  if (linkSettings) linkSettings.href = path;
}

function goToSettings() {
  showPanel('settingsPanel');
  setStatus('');
}

async function goBackFromSettings() {
  showPanel('mainPanel');
  await loadItems();
}

async function init() {
  await initTheme();
  const baseUrl = await getVaultBaseUrl();
  syncVaultLinks(baseUrl);
  const openVaultEl = document.getElementById('openVault');
  if (openVaultEl) openVaultEl.href = baseUrl;

  const devToggle = document.getElementById('developerOptionsToggle');
  const devPanel = document.getElementById('developerOptionsPanel');
  const urlInput = document.getElementById('vaultBaseUrl');

  const devEnabled = await getDeveloperOptionsEnabled();
  if (devToggle) devToggle.checked = devEnabled;
  if (devPanel) devPanel.classList.toggle('d-none', !devEnabled);
  if (urlInput) urlInput.value = baseUrl;

  if (devToggle) {
    devToggle.addEventListener('change', async () => {
      const enabled = devToggle.checked;
      await setDeveloperOptionsEnabled(enabled);
      devPanel?.classList.toggle('d-none', !enabled);
      if (enabled && urlInput) urlInput.value = await getVaultBaseUrl();
      const url = await getVaultBaseUrl();
      syncVaultLinks(url);
      if (openVaultEl) openVaultEl.href = url;
      await loadItems();
    });
  }

  if (urlInput) {
    urlInput.addEventListener('change', async () => {
      const url = urlInput.value.trim().replace(/\/$/, '') || getInjectedDefaultUrl();
      await setVaultBaseUrl(url);
      urlInput.value = url;
      syncVaultLinks(url);
      if (openVaultEl) openVaultEl.href = url;
      await loadItems();
    });
  }

  document.getElementById('search').addEventListener('input', onSearchInput);

  document.getElementById('btnOpenSettings')?.addEventListener('click', goToSettings);
  document.getElementById('btnOpenSettingsFromLogin')?.addEventListener('click', goToSettings);
  document.getElementById('btnBackFromSettings')?.addEventListener('click', goBackFromSettings);

  const btnCheckUpdate = document.getElementById('btnCheckUpdate');
  if (btnCheckUpdate) btnCheckUpdate.addEventListener('click', checkForUpdate);

  await loadItems();
}

/**
 * Obtiene la versión actual de la extensión desde version.txt (timestamp) o manifest.
 */
function getExtensionVersion() {
  const url = chrome.runtime.getURL('version.txt');
  return fetch(url)
    .then((r) => (r.ok ? r.text() : Promise.reject()))
    .then((t) => t.trim() || Promise.reject())
    .catch(() => (chrome.runtime.getManifest()?.version || '0'));
}

/**
 * Compara dos versiones: timestamps (numéricos) o semver "1.0.0". Devuelve 1 si a > b, -1 si a < b, 0 si igual.
 */
function compareVersions(a, b) {
  const na = parseInt(a, 10);
  const nb = parseInt(b, 10);
  if (!Number.isNaN(na) && !Number.isNaN(nb)) {
    if (na > nb) return 1;
    if (na < nb) return -1;
    return 0;
  }
  const partsA = (a || '0').split('.').map((n) => parseInt(n, 10) || 0);
  const partsB = (b || '0').split('.').map((n) => parseInt(n, 10) || 0);
  const len = Math.max(partsA.length, partsB.length);
  for (let i = 0; i < len; i++) {
    const pa = partsA[i] || 0;
    const pb = partsB[i] || 0;
    if (pa > pb) return 1;
    if (pa < pb) return -1;
  }
  return 0;
}

/**
 * Comprueba actualización: primero contra tu servidor (vault), luego Chrome Web Store si aplica.
 * Si hay versión nueva en el servidor, ofrece "Descargar" (abre el ZIP desde tu página).
 */
function checkForUpdate() {
  const msgEl = document.getElementById('updateModalMessage');
  const actionBtn = document.getElementById('updateModalAction');
  const modalEl = document.getElementById('updateModal');
  if (!msgEl || !modalEl) return;

  actionBtn?.classList.add('d-none');
  msgEl.textContent = 'Comprobando…';

  const modal = new bootstrap.Modal(modalEl);
  modal.show();

  Promise.all([
    getExtensionVersion(),
    getVaultBaseUrl().then((baseUrl) => {
      const apiUrl = baseUrl.replace(/\/$/, '') + '/api/extension/version';
      return fetch(apiUrl, { method: 'GET', credentials: 'include', headers: { Accept: 'application/json' } })
        .then((res) => (res.ok ? res.json() : null))
        .catch(() => null);
    }),
  ]).then(([currentVersion, data]) => {
    const serverVersion = data?.version;
    if (serverVersion && compareVersions(serverVersion, currentVersion) > 0) {
      const ts = parseInt(serverVersion, 10);
      const dateStr = !Number.isNaN(ts) ? new Date(ts * 1000).toLocaleDateString('es') : serverVersion;
      msgEl.textContent = `Nueva versión disponible (${dateStr}). Descárgala desde tu vault e instálala (descomprime y recarga en chrome://extensions).`;
      if (actionBtn) {
        actionBtn.textContent = 'Descargar';
        actionBtn.classList.remove('d-none');
        const newBtn = actionBtn.cloneNode(true);
        actionBtn.replaceWith(newBtn);
        newBtn.addEventListener('click', () => {
          if (data.download_url) window.open(data.download_url, '_blank');
          bootstrap.Modal.getInstance(modalEl)?.hide();
        });
      }
      return;
    }
    if (serverVersion && compareVersions(serverVersion, currentVersion) === 0) {
      msgEl.textContent = 'Ya tienes la última versión.';
      return;
    }
    tryStoreUpdate(msgEl, actionBtn);
  }).catch(() => {
    tryStoreUpdate(msgEl, actionBtn);
  });
}


function tryStoreUpdate(msgEl, actionBtn) {
  if (typeof chrome.runtime.requestUpdateCheck !== 'function') {
    msgEl.textContent = 'No se pudo comprobar. Verifica la URL del vault o recarga en chrome://extensions.';
    return;
  }
  chrome.runtime.requestUpdateCheck((status) => {
    if (status === 'update_available') {
      msgEl.textContent = 'Hay una nueva versión en Chrome Web Store. Recarga la extensión para aplicarla.';
      if (actionBtn) {
        actionBtn.textContent = 'Recargar ahora';
        actionBtn.classList.remove('d-none');
        const newBtn = actionBtn.cloneNode(true);
        actionBtn.replaceWith(newBtn);
        newBtn.addEventListener('click', () => chrome.runtime.reload());
      }
    } else if (status === 'no_update') {
      msgEl.textContent = 'Ya tienes la última versión.';
    } else {
      msgEl.textContent = 'Comprobado. Vuelve a intentarlo más tarde si esperas una actualización.';
    }
  });
}

init().catch((err) => {
  setStatus('Error al cargar', true);
  console.error(err);
});
