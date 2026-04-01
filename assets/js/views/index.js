import { getState, persist, resetDemo } from '../state.js';
import { byId, fmtDate, uid, todayPlus } from '../helpers.js';
import { statusBadge, openConfirmModal, openDetailModal, openFormModal, showToast } from '../ui.js';
import { handlePublicBooking, createQualityAnomaly, updateTaskStatus } from '../workflow-engine.js';
import { can } from '../permissions.js';
import { t } from '../i18n.js';

const q = (s) => document.querySelector(s);
const getCtx = () => { const st = getState(); const role = st.effectiveRole; const user = st.data.users.find((u) => u.id === st.currentUserId); return { st, data: st.data, role, user }; };
const visibleProps = (ctx) => ctx.role === 'owner' ? ctx.data.properties.filter((p) => p.ownerId === ctx.user.id) : ctx.data.properties;
const ownerPropIds = (ctx) => visibleProps(ctx).map((p) => p.id);
const visibleReservations = (ctx) => ctx.role === 'owner' ? ctx.data.reservations.filter((r) => ownerPropIds(ctx).includes(r.propertyId)) : ctx.data.reservations;
const visibleOperations = (ctx) => {
  if (ctx.role === 'owner') return ctx.data.operations.filter((o) => ownerPropIds(ctx).includes(o.propertyId));
  if (ctx.role === 'housekeeping') return ctx.data.operations.filter((o) => o.assignedRole === 'housekeeping');
  if (ctx.role === 'maintenance') return ctx.data.operations.filter((o) => o.assignedRole === 'maintenance');
  if (ctx.role === 'inspector') return ctx.data.operations.filter((o) => o.category === 'Quality' || o.assignedRole === 'inspector');
  return ctx.data.operations;
};
const visibleNotifications = (ctx) => {
  if (ctx.role === 'manager') return ctx.data.notifications;
  if (ctx.role === 'owner') return ctx.data.notifications.filter((n) => n.userRole === 'owner' && ownerPropIds(ctx).includes(n.propertyId));
  return ctx.data.notifications.filter((n) => n.userRole === ctx.role);
};
const ensure = (ctx, action, module) => can(ctx.role, action, module);

export const renderLogin = () => `<div class="auth-wrap"><form id="loginForm" class="auth-card"><h2>RestInnov Demo Login</h2><p class="small">Use seeded credentials.</p><div><label>Email</label><input name="email" required></div><div><label>Password</label><input name="password" type="password" required></div><div class="row" style="margin-top:.6rem"><button class="btn-primary">Login</button><a href="#/public-booking" class="btn">Go to Public Booking</a></div><p class="small">manager@restinnov.demo / Demo123!</p></form></div>`;
export const bindLogin = ({ onLogin }) => q('#loginForm')?.addEventListener('submit', (e) => { e.preventDefault(); const fd = new FormData(e.target); onLogin(fd.get('email'), fd.get('password')); });

export const renderDashboard = () => {
  const ctx = getCtx();
  const reservations = visibleReservations(ctx);
  const operations = visibleOperations(ctx);
  const notifications = visibleNotifications(ctx);
  const roleBlock = {
    manager: `<div class="two-col"><section class="panel"><h3>Recent Reservations</h3><table class="table"><tbody>${reservations.slice(0,5).map((r)=>`<tr><td>${r.id}</td><td>${byId(ctx.data.properties,r.propertyId)?.name||''}</td><td>${r.checkIn}</td><td>${statusBadge(r.status)}</td></tr>`).join('')}</tbody></table></section><section class="panel"><h3>Open Tasks by Category</h3>${['Arrival Prep','Departure Turnover','Quality','Maintenance','General'].map((c)=>`<div>${c}: ${operations.filter((o)=>o.category===c && o.status!=='Done').length}</div>`).join('')}</section></div>`,
    owner: `<div class="two-col"><section class="panel"><h3>Owned Properties</h3>${visibleProps(ctx).map((p)=>`<div>${p.name} ${statusBadge(p.status)}</div>`).join('')}</section><section class="panel"><h3>Recent Documents</h3>${ctx.data.documents.filter((d)=>ownerPropIds(ctx).includes(d.propertyId)).slice(0,5).map((d)=>`<div>${d.title} <span class="small">${d.uploadedAt}</span></div>`).join('')}</section></div>`,
    housekeeping: `<section class="panel"><h3>Assigned Tasks Summary</h3><div class="row"><div class="badge">Overdue: ${operations.filter((o)=>o.dueDate<todayPlus(0) && o.status!=='Done').length}</div><div class="badge">Today: ${operations.filter((o)=>o.dueDate===todayPlus(0)).length}</div><div class="badge">Upcoming: ${operations.filter((o)=>o.dueDate>todayPlus(0)).length}</div></div></section>`,
    maintenance: `<section class="panel"><h3>Maintenance by Priority</h3>${['Urgent','High','Medium','Low'].map((p)=>`<div>${p}: ${operations.filter((o)=>o.priority===p).length}</div>`).join('')}</section>`,
    inspector: `<section class="panel"><h3>Quality Checks Overview</h3><div>Upcoming checks: ${ctx.data.qualityChecks.filter((q)=>['Scheduled','In progress'].includes(q.status)).length}</div><div>Anomalies needing action: ${ctx.data.qualityChecks.reduce((a,x)=>a+x.anomalies.length,0)}</div></section>`
  };

  return `<div class="grid"><div class="grid kpis">${[['Reservations', reservations.length], ['Open Tasks', operations.filter((o)=>o.status!=='Done').length], ['Properties', visibleProps(ctx).length], ['Unread Notifications', notifications.filter((n)=>!n.read).length]].map(([k,v])=>`<div class="panel"><div class="small">${k}</div><div class="kpi-value">${v}</div></div>`).join('')}</div>${roleBlock[ctx.role]}<div class="two-col"><section class="panel"><h3>Latest Notifications</h3>${notifications.slice(0,5).map((n)=>`<div>${n.type} · ${n.text}</div>`).join('') || '<p class="small">No notifications.</p>'}</section><section class="panel"><h3>Timeline</h3><ul class="timeline">${ctx.data.activityTimeline.slice(0,6).map((t)=>`<li>${t.text}<div class="small">${fmtDate(t.createdAt)}</div></li>`).join('')}</ul></section></div></div>`;
};

export const renderPublicBooking = () => {
  const props = getState().data.properties.filter((p)=>p.status==='Active');
  return `<div class="panel"><h2>${t('public-booking')}</h2><form id="bookingForm" class="grid"><div class="filters"><div><label>Guest Name</label><input name="guestName" required></div><div><label>Guest Email</label><input name="guestEmail" type="email" required></div><div><label>Phone</label><input name="phone" required></div><div><label>Property</label><select name="propertyId" required>${props.map((p)=>`<option value="${p.id}">${p.name}</option>`)}</select></div><div><label>Check-in</label><input name="checkIn" type="date" required></div><div><label>Check-out</label><input name="checkOut" type="date" required></div><div><label>Guests</label><input name="guestsCount" type="number" min="1" max="12" value="2" required></div></div><div><label>Notes</label><input name="notes"></div><div class="row"><button class="btn-primary">Submit Booking</button></div></form><div id="bookingResult"></div></div>`;
};
export const bindPublicBooking = () => q('#bookingForm')?.addEventListener('submit', (e)=>{
  e.preventDefault(); const fd = Object.fromEntries(new FormData(e.target).entries());
  if(fd.checkOut<=fd.checkIn) return showToast('Check-out must be after check-in.', 'error');
  const res = handlePublicBooking(fd); const prop = byId(getState().data.properties, res.reservation.propertyId);
  q('#bookingResult').innerHTML = `<div class="panel" style="margin-top:.6rem"><h3>Workflow Engine Result</h3><div class="grid"><div><strong>Reservation created:</strong> ${res.reservation.id} · ${prop?.name} · ${res.reservation.checkIn} → ${res.reservation.checkOut}</div><div><strong>Customer:</strong> ${res.customerCreated ? 'New customer profile created' : 'Existing customer linked'}</div><div><strong>Operations generated:</strong> ${res.tasks.length} tasks</div><div><strong>Quality:</strong> check ${res.qc.id} scheduled</div><div><strong>Notifications:</strong> manager, owner, housekeeping, inspector notified</div><div class="badge info">Next step: switch to Manager dashboard</div></div></div>`;
  e.target.reset();
});

const crudHeader = (title, canCreate, key) => `<div class="row" style="justify-content:space-between"><h2>${title}</h2>${canCreate?`<button data-create="${key}">Create</button>`:''}</div>`;

export const renderProperties = () => {
  const ctx = getCtx(); const props = visibleProps(ctx); const canManage = ensure(ctx,'create','properties');
  return `<div class="panel">${crudHeader('Properties', canManage, 'property')}<div class="row"><button data-view="cards">Cards</button><button data-view="table">Table</button></div><div id="propView" data-mode="cards" class="cards">${props.map((p)=>`<article class="card"><h3>${p.name}</h3><div class="small">${p.city} · ${p.type}</div><p>${p.shortDescription}</p>${statusBadge(p.status)}<div class="row"><button data-detail="property:${p.id}">Detail</button>${canManage?`<button data-edit="property:${p.id}">Edit</button><button class="btn-danger" data-del="property:${p.id}">Delete</button>`:''}</div></article>`).join('')}</div></div>`;
};

export const renderReservations = () => {
  const ctx = getCtx(); const list = visibleReservations(ctx); const canManage = ensure(ctx,'create','reservations');
  return `<div class="panel">${crudHeader('Reservations', canManage, 'reservation')}<table class="table"><thead><tr><th>ID</th><th>Customer</th><th>Property</th><th>Dates</th><th>Status</th><th>Actions</th></tr></thead><tbody>${list.map((r)=>`<tr><td>${r.id}</td><td>${byId(ctx.data.customers,r.customerId)?.name||'-'}</td><td>${byId(ctx.data.properties,r.propertyId)?.name||'-'}</td><td>${r.checkIn} → ${r.checkOut}</td><td>${statusBadge(r.status)}</td><td><button data-detail="reservation:${r.id}">Detail</button>${canManage?`<button data-edit="reservation:${r.id}">Edit</button><button class="btn-danger" data-del="reservation:${r.id}">Delete</button>`:''}</td></tr>`).join('')}</tbody></table></div>`;
};

export const renderCustomers = () => {
  const ctx = getCtx(); const canManage = ensure(ctx,'create','customers');
  if (!canManage) return `<div class="panel"><p class="small">Customers module is manager-only.</p></div>`;
  return `<div class="panel">${crudHeader('Customers', true, 'customer')}<table class="table"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Reservations</th><th>Actions</th></tr></thead><tbody>${ctx.data.customers.map((c)=>`<tr><td>${c.name}</td><td>${c.email}</td><td>${c.phone}</td><td>${ctx.data.reservations.filter((r)=>r.customerId===c.id).length}</td><td><button data-edit="customer:${c.id}">Edit</button><button class="btn-danger" data-del="customer:${c.id}">Delete</button></td></tr>`).join('')}</tbody></table></div>`;
};

export const renderOperations = () => {
  const ctx = getCtx(); const list = visibleOperations(ctx);
  return `<div class="panel">${crudHeader(ctx.role==='housekeeping'?'Housekeeping':ctx.role==='maintenance'?'Maintenance':'Operations', ensure(ctx,'create','operations'), 'operation')}<div class="filters"><input id="taskSearch" placeholder="Search task"><select id="taskStatus"><option value="">All status</option><option>To do</option><option>In progress</option><option>Done</option><option>Blocked</option></select></div><table class="table"><thead><tr><th>Title</th><th>Property</th><th>Due</th><th>Status</th><th>Priority</th><th>Actions</th></tr></thead><tbody>${list.map((o)=>`<tr><td>${o.title}<div class="small">${o.category}</div></td><td>${byId(ctx.data.properties,o.propertyId)?.name||''}</td><td>${o.dueDate}</td><td>${statusBadge(o.status)}</td><td>${statusBadge(o.priority||'')}</td><td>${ensure(ctx,'update',ctx.role==='housekeeping'?'housekeeping':ctx.role==='maintenance'?'maintenance':'operations')?`<button data-status="${o.id}:In progress">Start</button><button data-status="${o.id}:Done">Done</button><button data-note="${o.id}">Notes</button>`:''}${ensure(ctx,'update','operations')?`<button data-edit="operation:${o.id}">Edit</button>`:''}${ensure(ctx,'delete','operations')?`<button class="btn-danger" data-del="operation:${o.id}">Delete</button>`:''}</td></tr>`).join('')}</tbody></table>${list.length===0?'<p class="small">No tasks match current scope.</p>':''}</div>`;
};

export const renderQuality = () => {
  const ctx=getCtx();
  const list = ctx.role==='inspector' ? ctx.data.qualityChecks.filter((x)=>x.inspectorId==='u_inspector') : ctx.data.qualityChecks;
  return `<div class="panel">${crudHeader('Quality Checks', ensure(ctx,'create','quality'), 'quality')}<table class="table"><thead><tr><th>ID</th><th>Property</th><th>Status</th><th>Anomalies</th><th>Actions</th></tr></thead><tbody>${list.map((x)=>`<tr><td>${x.id}</td><td>${byId(ctx.data.properties,x.propertyId)?.name||''}</td><td>${statusBadge(x.status)}</td><td>${x.anomalies.length}</td><td><button data-detail="quality:${x.id}">Detail</button>${ensure(ctx,'update','quality')?`<button data-anomaly="${x.id}">Add anomaly</button><button data-edit="quality:${x.id}">Edit</button>`:''}${ensure(ctx,'delete','quality')?`<button class="btn-danger" data-del="quality:${x.id}">Delete</button>`:''}</td></tr>`).join('')}</tbody></table></div>`;
};

export const renderDocuments = () => {
  const ctx=getCtx(); const canManage = ensure(ctx,'create','documents');
  let docs=ctx.data.documents; if(ctx.role==='owner') docs=docs.filter((d)=>ownerPropIds(ctx).includes(d.propertyId));
  return `<div class="panel">${crudHeader('Documents', canManage, 'document')}<table class="table"><thead><tr><th>Title</th><th>Type</th><th>Property</th><th>Uploaded</th><th>Actions</th></tr></thead><tbody>${docs.map((d)=>`<tr><td>${d.title}</td><td>${d.type}</td><td>${byId(ctx.data.properties,d.propertyId)?.name||''}</td><td>${d.uploadedAt}</td><td><button data-detail="document:${d.id}">Detail</button>${canManage?`<button data-edit="document:${d.id}">Edit</button><button class="btn-danger" data-del="document:${d.id}">Delete</button>`:''}</td></tr>`).join('')}</tbody></table></div>`;
};

export const renderNotifications = () => {
  const ctx=getCtx(); const list=visibleNotifications(ctx);
  return `<div class="panel"><h2>${t('notifications')}</h2>${list.map((n)=>`<div class="card"><div class="row" style="justify-content:space-between"><strong>${n.type}</strong>${statusBadge(n.read?'Read':'Unread')}</div><div>${n.text}</div><div class="small">${fmtDate(n.createdAt)} ${ensure(ctx,'update','notifications')?`<button data-notif="${n.id}">Toggle read</button>`:''}</div></div>`).join('')}</div>`;
};

export const renderSettings = () => `<div class="panel"><h2>${t('settings')}</h2><div class="card"><h3>Demo Controls</h3><p>Use topbar quick role switch to present role impacts instantly.</p><button id="resetDemo" class="btn-danger">Reset Demo Data</button></div><div class="card"><h3>Recommended Scenario</h3><ol><li>Create booking on Public Booking.</li><li>Switch Manager -> check chain.</li><li>Switch Owner/Housekeeping/Inspector.</li><li>Add anomaly, switch Maintenance.</li></ol></div></div>`;
export const renderDemoGuide = () => `<div class="panel"><h2>${t('demo-guide')}</h2><ol><li>Open Public Booking and create reservation.</li><li>Switch to Manager and review generated artifacts.</li><li>Switch to Owner and verify scoped visibility.</li><li>Switch to Housekeeping and start assigned tasks.</li><li>Switch to Inspector and add anomaly.</li><li>Switch to Maintenance and close follow-up task.</li></ol><button id="resetDemo" class="btn-danger">Reset Demo Data</button></div>`;

const reservationDetailHtml = (ctx, r) => {
  const customer = byId(ctx.data.customers, r.customerId); const property = byId(ctx.data.properties, r.propertyId);
  const tasks = ctx.data.operations.filter((o)=>o.reservationId===r.id); const notifs = ctx.data.notifications.filter((n)=>n.text.includes(r.id));
  const timeline = ctx.data.activityTimeline.filter((e)=>e.text.includes(r.id)).slice(0,5);
  return `<div><p><strong>${r.id}</strong> ${statusBadge(r.status)}</p><p>Customer: ${customer?.name} (${customer?.email})</p><p>Property: ${property?.name} (${property?.city})</p><p>Dates: ${r.checkIn} → ${r.checkOut}</p><h4>Generated Tasks</h4>${tasks.map((o)=>`<div>${o.title} ${statusBadge(o.status)}</div>`).join('') || '<p class="small">None</p>'}<h4>Related Notifications</h4>${notifs.map((n)=>`<div>${n.text}</div>`).join('') || '<p class="small">None</p>'}<h4>Timeline</h4>${timeline.map((e)=>`<div>${e.text}</div>`).join('') || '<p class="small">No direct timeline entries.</p>'}</div>`;
};

export const bindGenericActions = (rerender) => {
  document.body.onclick = (e) => {
    const t = e.target; const ctx = getCtx(); const state = ctx.data;
    const deny = () => showToast('Action not allowed for your role.', 'error');

    if (t.dataset.view) {
      const box = q('#propView'); if (!box) return;
      if (t.dataset.view === 'table') box.innerHTML = `<table class="table"><thead><tr><th>Name</th><th>City</th><th>Status</th></tr></thead><tbody>${visibleProps(ctx).map((p)=>`<tr><td>${p.name}</td><td>${p.city}</td><td>${statusBadge(p.status)}</td></tr>`).join('')}</tbody></table>`;
      else box.innerHTML = visibleProps(ctx).map((p)=>`<article class="card"><h3>${p.name}</h3><div class="small">${p.city} · ${p.type}</div></article>`).join('');
    }

    if (t.dataset.notif) { if (!ensure(ctx,'update','notifications')) return deny(); const n=state.notifications.find((x)=>x.id===t.dataset.notif); n.read=!n.read; persist(); return rerender(); }
    if (t.id==='resetDemo') return openConfirmModal({ title:'Reset Demo', message:'Restore seeded exhibition dataset?', onConfirm:()=>{ resetDemo(); location.hash='/login'; location.reload(); } });
    if (t.dataset.status) {
      const module = ctx.role==='housekeeping'?'housekeeping':ctx.role==='maintenance'?'maintenance':'operations';
      if (!ensure(ctx,'update',module)) return deny();
      const [id,status]=t.dataset.status.split(':'); updateTaskStatus(id,status,''); persist(); showToast(`Task moved to ${status}`, 'success'); return rerender();
    }
    if (t.dataset.note) {
      const module = ctx.role==='housekeeping'?'housekeeping':ctx.role==='maintenance'?'maintenance':'operations';
      if (!ensure(ctx,'update',module)) return deny();
      const rec = state.operations.find((o)=>o.id===t.dataset.note); if (!rec) return;
      return openFormModal({ title:'Edit Task Notes', fields:[{name:'notes',label:'Notes',required:false}], values:{notes:rec.notes||''}, onSubmit:(p)=>{ rec.notes=p.notes; persist(); showToast('Notes updated','success'); rerender(); } });
    }
    if (t.dataset.detail) {
      const [kind,id] = t.dataset.detail.split(':');
      if (kind==='reservation') { const r = state.reservations.find((x)=>x.id===id); if (r) openDetailModal({title:`Reservation ${id}`, content:reservationDetailHtml(ctx,r)}); }
      if (kind==='quality') { const qItem = state.qualityChecks.find((x)=>x.id===id); if (!qItem) return; openDetailModal({title:`Quality Check ${id}`, content:`<p>${statusBadge(qItem.status)}</p><p>Property: ${byId(state.properties,qItem.propertyId)?.name||''}</p><h4>Checklist</h4>${qItem.checklist.map((x)=>`<div>• ${x}</div>`).join('')}<h4>Notes</h4><p>${qItem.notes||'-'}</p><h4>Anomalies</h4>${qItem.anomalies.map((a)=>`<div>${a.description}</div>`).join('')||'<p class="small">No anomalies.</p>'}`}); }
      if (kind==='property') { const p = state.properties.find((x)=>x.id===id); if (p) openDetailModal({title:p.name, content:`<p>${p.city} · ${p.type}</p><p>Capacity ${p.capacity}, Nightly ${p.nightlyRate}</p><p>${p.shortDescription}</p>`}); }
      if (kind==='document') { const d=state.documents.find((x)=>x.id===id); if (d) openDetailModal({title:d.title, content:`<p>Type: ${d.type}</p><p>Property: ${byId(state.properties,d.propertyId)?.name||''}</p><p>Uploaded: ${d.uploadedAt}</p>`}); }
    }

    if (t.dataset.anomaly) {
      if (!ensure(ctx,'update','quality')) return deny();
      return openFormModal({ title:'Add Quality Anomaly', fields:[{name:'description',label:'Description',required:true}], onSubmit:(p)=>{ const res=createQualityAnomaly(t.dataset.anomaly,p.description); if(res) showToast(`Maintenance task ${res.mTask.id} created`, 'success'); rerender(); } });
    }

    const map = { property:['properties','properties'], reservation:['reservations','reservations'], customer:['customers','customers'], operation:['operations','operations'], quality:['qualityChecks','quality'], document:['documents','documents'] };
    if (t.dataset.del) {
      const [kind,id]=t.dataset.del.split(':'); const [arrKey,mod]=map[kind]||[];
      if (!ensure(ctx,'delete',mod)) return deny();
      return openConfirmModal({ title:'Delete Record', message:'This action cannot be undone in current demo state.', onConfirm:()=>{ const arr=state[arrKey]; const ix=arr.findIndex((x)=>x.id===id); if(ix>-1) arr.splice(ix,1); persist(); showToast('Record deleted','success'); rerender(); } });
    }

    const commonPropertyOptions = state.properties.map((p)=>({ value:p.id, label:p.name }));
    if (t.dataset.create) {
      const kind = t.dataset.create;
      if (!ensure(ctx,'create',kind==='quality'?'quality':kind==='document'?'documents':`${kind}s`)) return deny();
      if (kind==='property') return openFormModal({ title:'Create Property', fields:[{name:'name',label:'Name',required:true},{name:'city',label:'City',required:true},{name:'type',label:'Type',required:true},{name:'capacity',label:'Capacity',type:'number',required:true},{name:'nightlyRate',label:'Nightly rate',type:'number',required:true},{name:'status',label:'Status',required:true},{name:'shortDescription',label:'Short description',required:true}], onSubmit:(p)=>{ state.properties.push({ id:uid('p'), ownerId:'u_owner', ...p, capacity:Number(p.capacity), nightlyRate:Number(p.nightlyRate)}); persist(); showToast('Property created','success'); rerender(); } });
      if (kind==='reservation') return openFormModal({ title:'Create Reservation', fields:[{name:'customerId',label:'Customer',type:'select',options:state.customers.map((c)=>({value:c.id,label:c.name})),required:true},{name:'propertyId',label:'Property',type:'select',options:commonPropertyOptions,required:true},{name:'checkIn',label:'Check-in',type:'date',required:true},{name:'checkOut',label:'Check-out',type:'date',required:true},{name:'guestsCount',label:'Guests',type:'number',required:true},{name:'status',label:'Status',required:true}], values:{status:'Pending'}, onSubmit:(p)=>{ state.reservations.push({ id:uid('r'), ...p, guestsCount:Number(p.guestsCount), notes:'' }); persist(); showToast('Reservation created','success'); rerender(); } });
      if (kind==='customer') return openFormModal({ title:'Create Customer', fields:[{name:'name',label:'Name',required:true},{name:'email',label:'Email',required:true},{name:'phone',label:'Phone',required:true}], onSubmit:(p)=>{ state.customers.push({ id:uid('c'), ...p }); persist(); showToast('Customer created','success'); rerender(); } });
      if (kind==='operation') return openFormModal({ title:'Create Operation Task', fields:[{name:'title',label:'Title',required:true},{name:'category',label:'Category',required:true},{name:'propertyId',label:'Property',type:'select',options:commonPropertyOptions,required:true},{name:'assignedRole',label:'Assigned role',type:'select',options:[{value:'housekeeping',label:'Housekeeping'},{value:'maintenance',label:'Maintenance'},{value:'inspector',label:'Inspector'}],required:true},{name:'dueDate',label:'Due date',type:'date',required:true},{name:'priority',label:'Priority',required:true}], values:{status:'To do',priority:'Medium'}, onSubmit:(p)=>{ state.operations.push({ id:uid('o'), reservationId:'', status:'To do', notes:'', ...p }); persist(); showToast('Operation created','success'); rerender(); } });
      if (kind==='quality') return openFormModal({ title:'Create Quality Check', fields:[{name:'propertyId',label:'Property',type:'select',options:commonPropertyOptions,required:true},{name:'status',label:'Status',required:true},{name:'notes',label:'Notes',required:false}], values:{status:'Draft'}, onSubmit:(p)=>{ state.qualityChecks.push({ id:uid('q'), propertyId:p.propertyId, reservationId:'', inspectorId:'u_inspector', status:p.status, checklist:['Entry quality','Bathroom quality','Safety devices'], notes:p.notes||'', anomalies:[] }); persist(); showToast('Quality check created','success'); rerender(); } });
      if (kind==='document') return openFormModal({ title:'Create Document', fields:[{name:'title',label:'Title',required:true},{name:'type',label:'Type',required:true},{name:'propertyId',label:'Property',type:'select',options:commonPropertyOptions,required:true}], onSubmit:(p)=>{ state.documents.push({ id:uid('d'), reservationId:'', visibilityByRole:['manager','owner'], uploadedAt:todayPlus(0), ...p }); persist(); showToast('Document metadata created','success'); rerender(); } });
    }

    if (t.dataset.edit) {
      const [kind,id]=t.dataset.edit.split(':'); const [arrKey,mod]=map[kind]||[];
      if (!ensure(ctx,'update',mod)) return deny();
      const rec = state[arrKey]?.find((x)=>x.id===id); if (!rec) return;
      const editFields = {
        property:[{name:'name',label:'Name',required:true},{name:'city',label:'City',required:true},{name:'status',label:'Status',required:true},{name:'shortDescription',label:'Short description',required:true}],
        reservation:[{name:'status',label:'Status',required:true},{name:'checkIn',label:'Check-in',type:'date',required:true},{name:'checkOut',label:'Check-out',type:'date',required:true}],
        customer:[{name:'name',label:'Name',required:true},{name:'email',label:'Email',required:true},{name:'phone',label:'Phone',required:true}],
        operation:[{name:'status',label:'Status',required:true},{name:'priority',label:'Priority',required:true},{name:'dueDate',label:'Due date',type:'date',required:true}],
        quality:[{name:'status',label:'Status',required:true},{name:'notes',label:'Notes',required:false}],
        document:[{name:'title',label:'Title',required:true},{name:'type',label:'Type',required:true}]
      };
      return openFormModal({ title:`Edit ${kind}`, fields:editFields[kind], values:rec, onSubmit:(p)=>{ Object.assign(rec,p); persist(); showToast('Record updated','success'); rerender(); } });
    }
  };
};
