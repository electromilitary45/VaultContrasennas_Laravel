/**
 * Content script: detecta formularios de login y ofrece rellenar con OCIANN Vault.
 * Para rellenar necesita que el usuario haya iniciado sesión en una pestaña del vault
 * (la extensión usa la misma sesión vía cookies en fetch con credentials).
 */

const DEFAULT_VAULT_URL = 'http://localhost:8000';

async function getVaultBaseUrl() {
  return new Promise((resolve) => {
    chrome.storage.local.get('vaultBaseUrl', (r) => resolve(r.vaultBaseUrl || DEFAULT_VAULT_URL));
  });
}

async function apiGet(path) {
  const base = await getVaultBaseUrl();
  const url = base.replace(/\/$/, '') + path;
  const res = await fetch(url, {
    method: 'GET',
    credentials: 'include',
    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
  });
  if (!res.ok) return null;
  return res.json();
}

function findLoginFields() {
  const passwordFields = document.querySelectorAll('input[type="password"]');
  if (!passwordFields.length) return null;

  const form = passwordFields[0].closest('form');
  if (!form) return null;

  const passwordInput = passwordFields[0];
  let usernameInput = form.querySelector('input[type="email"]') ||
    form.querySelector('input[type="text"]') ||
    form.querySelector('input[name="username"]') ||
    form.querySelector('input[name="user"]') ||
    form.querySelector('input[autocomplete="username"]') ||
    form.querySelector('input[id*="user"], input[id*="login"], input[id*="email"]');

  if (!usernameInput && passwordInput.previousElementSibling?.tagName === 'INPUT') {
    usernameInput = passwordInput.previousElementSibling;
  }

  return { form, usernameInput, passwordInput };
}

function createFillButton(callback) {
  const btn = document.createElement('button');
  btn.type = 'button';
  btn.textContent = 'Rellenar con OCIANN Vault';
  btn.style.cssText = 'margin-top:8px;padding:6px 12px;font-size:13px;background:#0071e3;color:#fff;border:none;border-radius:6px;cursor:pointer;';
  btn.addEventListener('click', callback);
  return btn;
}

function injectFillUI() {
  const login = findLoginFields();
  if (!login) return;

  const { form, usernameInput, passwordInput } = login;
  if (document.querySelector('[data-ociann-vault-injected]')) return;

  const wrap = document.createElement('div');
  wrap.setAttribute('data-ociann-vault-injected', '1');
  wrap.style.marginTop = '8px';

  const btn = createFillButton(async () => {
    const data = await apiGet('/api/extension/vault/items');
    if (!data || !data.items || data.items.length === 0) {
      alert('Inicia sesión en el vault en otra pestaña o no tienes items de tipo "auth".');
      return;
    }
    const authItems = data.items.filter((i) => i.type === 'auth');
    if (authItems.length === 0) {
      alert('No hay items de tipo login en tu vault.');
      return;
    }
    if (authItems.length === 1) {
      await fillWithItem(authItems[0].id, usernameInput, passwordInput);
      return;
    }
    const choice = prompt(
      'Escribe el número del item (1-' + authItems.length + '):\n' +
      authItems.map((a, i) => `${i + 1}. ${a.title} (${a.username || '-'})`).join('\n')
    );
    const idx = parseInt(choice, 10);
    if (idx >= 1 && idx <= authItems.length) {
      await fillWithItem(authItems[idx - 1].id, usernameInput, passwordInput);
    }
  });

  wrap.appendChild(btn);
  passwordInput.closest('div')?.appendChild(wrap) || form.appendChild(wrap);
}

async function fillWithItem(itemId, usernameInput, passwordInput) {
  const data = await apiGet('/api/extension/vault/items/' + itemId);
  if (!data || !data.secret) return;
  const s = data.secret;
  if (usernameInput && (s.username != null)) {
    usernameInput.value = s.username;
    usernameInput.dispatchEvent(new Event('input', { bubbles: true }));
  }
  if (passwordInput && (s.password != null)) {
    passwordInput.value = s.password;
    passwordInput.dispatchEvent(new Event('input', { bubbles: true }));
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', injectFillUI);
} else {
  injectFillUI();
}
