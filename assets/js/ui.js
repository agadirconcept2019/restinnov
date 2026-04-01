import { menuByRole } from './permissions.js';
import { t, getLang } from './i18n.js';

export const appShell = ({ role, route, userName }) => `
<div class="layout">
  <aside class="sidebar">
    <div class="brand">RestInnov</div>
    <nav class="menu">
      ${menuByRole(role).map((m) => `<a href="#/${m}" class="${route===m?'active':''}">${t(m)}</a>`).join('')}
    </nav>
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
  const cl = /done|confirmed|completed|checked-in/i.test(s) ? 'success' : /blocked|cancelled|urgent/i.test(s) ? 'danger' : /progress|pending|scheduled/i.test(s) ? 'warning' : 'info';
  return `<span class="badge ${cl}">${s}</span>`;
};
