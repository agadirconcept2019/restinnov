import { initState, login, logout, currentUser, getState, setEffectiveRole, persist } from './state.js';
import { getRoute, go } from './router.js';
import { appShell, initUi, showToast } from './ui.js';
import { setLang } from './i18n.js';
import { menuByRole } from './permissions.js';
import {
  renderLogin, bindLogin, renderDashboard, renderPublicBooking, bindPublicBooking, renderProperties,
  renderReservations, renderCustomers, renderOperations, renderQuality, renderDocuments,
  renderNotifications, renderSettings, renderDemoGuide, bindGenericActions
} from './views/index.js';
import { roleLabel } from './helpers.js';

const app = document.getElementById('app');
initState();
initUi();

const routeAllowed = (role, route) => menuByRole(role).includes(route) || ['dashboard'].includes(route);

const renderRoute = () => {
  const route = getRoute();
  const user = currentUser();
  if (!user && !['login', 'public-booking'].includes(route)) return go('login');
  if (!user && route === 'login') {
    app.innerHTML = renderLogin();
    bindLogin({ onLogin: (e,p) => { if (!login(e,p)) return showToast('Invalid credentials', 'error'); go('dashboard'); renderRoute(); } });
    return;
  }
  if (!user && route === 'public-booking') {
    app.innerHTML = `<div class="main">${renderPublicBooking()}<div class="small" style="margin-top:.6rem"><a href="#/login">Staff Login</a></div></div>`;
    bindPublicBooking();
    return;
  }

  const role = getState().effectiveRole;
  let safeRoute = route;
  if (!routeAllowed(role, route)) {
    safeRoute = 'dashboard';
    go('dashboard');
    showToast('Access restricted for this role. Redirected to Dashboard.', 'error');
  }

  app.innerHTML = appShell({ role, route: safeRoute, userName: user.name });
  const view = document.getElementById('view');
  const map = {
    dashboard: renderDashboard, 'public-booking': renderPublicBooking, properties: renderProperties, reservations: renderReservations,
    customers: renderCustomers, operations: renderOperations, housekeeping: renderOperations, maintenance: renderOperations,
    quality: renderQuality, documents: renderDocuments, notifications: renderNotifications, settings: renderSettings, 'demo-guide': renderDemoGuide
  };
  view.innerHTML = (map[safeRoute] || renderDashboard)();
  bindPublicBooking();
  bindGenericActions(renderRoute);

  const roleSel = document.getElementById('quickRole');
  roleSel.innerHTML = Object.entries(roleLabel).map(([k,v])=>`<option value="${k}" ${role===k?'selected':''}>${v}</option>`).join('');
  roleSel.onchange = (e) => { setEffectiveRole(e.target.value); showToast(`Switched to ${roleLabel[e.target.value]}`, 'success'); renderRoute(); };
  document.getElementById('logoutBtn').onclick = () => { logout(); go('login'); renderRoute(); };
  document.getElementById('langSel').onchange = (e) => { setLang(e.target.value); renderRoute(); };
  persist();
};

window.addEventListener('hashchange', renderRoute);
if (!location.hash) go('login');
renderRoute();
