import { menuByRole } from './permissions.js';
import { t, getLang } from './i18n.js';

let modalRoot;
let toastRoot;

export const initUi = () => {
  if (!document.getElementById('modalRoot')) {
    modalRoot = document.createElement('div');
    modalRoot.id = 'modalRoot';
    document.body.appendChild(modalRoot);
  } else modalRoot = document.getElementById('modalRoot');
  if (!document.getElementById('toastRoot')) {
    toastRoot = document.createElement('div');
    toastRoot.id = 'toastRoot';
    document.body.appendChild(toastRoot);
  } else toastRoot = document.getElementById('toastRoot');
};

export const showToast = (message, type = 'info') => {
  initUi();
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.textContent = message;
  toastRoot.appendChild(el);
  setTimeout(() => el.remove(), 3000);
};

export const closeModal = () => { if (modalRoot) modalRoot.innerHTML = ''; };

const modalFrame = (title, body, footer='') => `
<div class="modal-backdrop" role="dialog" aria-modal="true" aria-label="${title}">
  <div class="modal">
    <div class="modal-head"><h3>${title}</h3><button id="modalCloseBtn">×</button></div>
    <div class="modal-body">${body}</div>
    <div class="modal-foot">${footer}</div>
  </div>
</div>`;

export const openDetailModal = ({ title, content }) => {
  initUi();
  modalRoot.innerHTML = modalFrame(title, content, '<button id="modalDoneBtn">Close</button>');
  modalRoot.querySelector('#modalCloseBtn').onclick = closeModal;
  modalRoot.querySelector('#modalDoneBtn').onclick = closeModal;
};

export const openConfirmModal = ({ title='Confirm', message, onConfirm }) => {
  initUi();
  modalRoot.innerHTML = modalFrame(title, `<p>${message}</p>`, '<button id="modalCancelBtn">Cancel</button><button id="modalConfirmBtn" class="btn-danger">Confirm</button>');
  modalRoot.querySelector('#modalCloseBtn').onclick = closeModal;
  modalRoot.querySelector('#modalCancelBtn').onclick = closeModal;
  modalRoot.querySelector('#modalConfirmBtn').onclick = () => { onConfirm?.(); closeModal(); };
};

export const openFormModal = ({ title, fields, values = {}, submitLabel = 'Save', onSubmit }) => {
  initUi();
  const body = `<form id="modalForm" class="grid">${fields.map((f) => `<div><label>${f.label}</label>${f.type==='select' ? `<select name="${f.name}" ${f.required?'required':''}>${f.options.map((o)=>`<option value="${o.value}" ${String(values[f.name] ?? f.value ?? '')===String(o.value)?'selected':''}>${o.label}</option>`).join('')}</select>` : `<input name="${f.name}" type="${f.type||'text'}" value="${values[f.name] ?? f.value ?? ''}" ${f.required?'required':''} />`}</div>`).join('')}</form>`;
  modalRoot.innerHTML = modalFrame(title, body, `<button id="modalCancelBtn">Cancel</button><button id="modalSubmitBtn" class="btn-primary">${submitLabel}</button>`);
  modalRoot.querySelector('#modalCloseBtn').onclick = closeModal;
  modalRoot.querySelector('#modalCancelBtn').onclick = closeModal;
  modalRoot.querySelector('#modalSubmitBtn').onclick = () => {
    const form = modalRoot.querySelector('#modalForm');
    if (!form.reportValidity()) return;
    const payload = Object.fromEntries(new FormData(form).entries());
    onSubmit?.(payload);
    closeModal();
  };
};

export const appShell = ({ role, route, userName }) => `
<div class="layout">
  <aside class="sidebar">
    <div class="brand">RestInnov</div>
    <nav class="menu">${menuByRole(role).map((m) => `<a href="#/${m}" class="${route===m?'active':''}">${t(m)}</a>`).join('')}</nav>
  </aside>
  <main class="main">
    <div class="topbar panel">
      <div><strong>${userName}</strong> <span class="small">(${role})</span></div>
      <div class="controls">
        <label class="small" for="langSel">Lang</label>
        <select id="langSel"><option value="en" ${getLang()==='en'?'selected':''}>EN</option><option value="fr" ${getLang()==='fr'?'selected':''}>FR</option><option value="es" ${getLang()==='es'?'selected':''}>ES</option></select>
        <select id="quickRole"></select>
        <button id="logoutBtn">${t('logout')}</button>
      </div>
    </div>
    <div id="view"></div>
  </main>
</div>`;

export const statusBadge = (s='') => {
  const cl = /done|confirmed|completed|checked-in|active/i.test(s) ? 'success' : /blocked|cancelled|urgent/i.test(s) ? 'danger' : /progress|pending|scheduled/i.test(s) ? 'warning' : 'info';
  return `<span class="badge ${cl}">${s}</span>`;
};
