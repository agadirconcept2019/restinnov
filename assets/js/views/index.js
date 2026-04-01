import { getState, persist, resetDemo } from '../state.js';
import { byId, fmtDate, uid } from '../helpers.js';
import { statusBadge } from '../ui.js';
import { handlePublicBooking, createQualityAnomaly, updateTaskStatus } from '../workflow-engine.js';

const q = (s) => document.querySelector(s);
const getCtx = () => {
  const st = getState();
  const role = st.effectiveRole;
  const user = st.data.users.find((u) => u.id === st.currentUserId);
  return { st, data: st.data, role, user };
};
const visibleProps = (ctx) => ctx.role === 'owner' ? ctx.data.properties.filter((p) => p.ownerId === ctx.user.id) : ctx.data.properties;
const visibleReservations = (ctx) => ctx.role === 'owner' ? ctx.data.reservations.filter((r) => visibleProps(ctx).some((p) => p.id === r.propertyId)) : ctx.data.reservations;

export const renderLogin = () => `<div class="auth-wrap"><form id="loginForm" class="auth-card"><h2>RestInnov Demo Login</h2><p class="small">Use seeded credentials.</p><div><label>Email</label><input name="email" required></div><div><label>Password</label><input name="password" type="password" required></div><div class="row" style="margin-top:.6rem"><button class="btn-primary">Login</button><a href="#/public-booking" class="btn">Go to Public Booking</a></div><p class="small">manager@restinnov.demo / Demo123!</p></form></div>`;
export const bindLogin = ({ onLogin }) => q('#loginForm')?.addEventListener('submit', (e) => { e.preventDefault(); const fd = new FormData(e.target); onLogin(fd.get('email'), fd.get('password')); });

export const renderDashboard = () => {
  const ctx = getCtx();
  const reservations = visibleReservations(ctx);
  const operations = ctx.role==='housekeeping' ? ctx.data.operations.filter((o)=>o.assignedRole==='housekeeping') : ctx.role==='maintenance' ? ctx.data.operations.filter((o)=>o.assignedRole==='maintenance') : ctx.data.operations;
  const notifications = ctx.data.notifications.filter((n)=>n.userRole===ctx.role || ctx.role==='manager').slice(0,6);
  return `<div class="grid"><div class="grid kpis">${[['Reservations', reservations.length], ['Open Tasks', operations.filter((o)=>!['Done'].includes(o.status)).length], ['Properties', visibleProps(ctx).length], ['Unread Notifications', notifications.filter((n)=>!n.read).length]].map(([k,v])=>`<div class="panel"><div class="small">${k}</div><div class="kpi-value">${v}</div></div>`).join('')}</div><div class="two-col"><section class="panel"><h3>Recent Activity</h3><ul class="timeline">${ctx.data.activityTimeline.slice(0,8).map((t)=>`<li><div>${t.text}</div><div class="small">${fmtDate(t.createdAt)}</div></li>`).join('')}</ul></section><section class="panel"><h3>Urgent Tasks</h3>${operations.filter((o)=>o.priority==='Urgent' || o.status==='Blocked').slice(0,8).map((o)=>`<div>${o.title} ${statusBadge(o.status)}</div>`).join('') || '<p class="small">No urgent tasks.</p>'}</section></div></div>`;
};

export const renderPublicBooking = () => {
  const props = getState().data.properties.filter((p)=>p.status==='Active');
  return `<div class="panel"><h2>Public Booking</h2><form id="bookingForm" class="grid"><div class="filters"><div><label>Guest Name</label><input name="guestName" required></div><div><label>Guest Email</label><input name="guestEmail" type="email" required></div><div><label>Phone</label><input name="phone" required></div><div><label>Property</label><select name="propertyId" required>${props.map((p)=>`<option value="${p.id}">${p.name}</option>`)}</select></div><div><label>Check-in</label><input name="checkIn" type="date" required></div><div><label>Check-out</label><input name="checkOut" type="date" required></div><div><label>Guests</label><input name="guestsCount" type="number" min="1" max="12" value="2" required></div></div><div><label>Notes</label><textarea name="notes"></textarea></div><div class="row"><button class="btn-primary">Submit Booking</button></div></form><div id="bookingResult"></div></div>`;
};
export const bindPublicBooking = () => q('#bookingForm')?.addEventListener('submit', (e)=>{ e.preventDefault(); const fd = Object.fromEntries(new FormData(e.target).entries()); if(fd.checkOut<=fd.checkIn) return alert('Check-out must be after check-in.'); const res = handlePublicBooking(fd); q('#bookingResult').innerHTML = `<div class="panel" style="margin-top:.6rem"><h3>Booking Confirmed</h3><p>Reservation ${res.reservation.id} was created, tasks were generated, quality check scheduled, and notifications dispatched.</p></div>`; e.target.reset(); });

const crudView = (entity, rows, cols, canEdit=true) => `<div class="panel"><div class="row" style="justify-content:space-between"><h2>${entity}</h2>${canEdit?`<button data-create="${entity.toLowerCase()}">Create</button>`:''}</div><table class="table"><thead><tr>${cols.map((c)=>`<th>${c}</th>`).join('')}${canEdit?'<th>Actions</th>':''}</tr></thead><tbody>${rows}</tbody></table></div>`;

export const renderProperties = () => { const ctx = getCtx(); const props = visibleProps(ctx); return `<div class="cards">${props.map((p)=>`<article class="card"><h3>${p.name}</h3><div class="small">${p.city} · ${p.type}</div><p>${p.shortDescription}</p><div>${statusBadge(p.status)}</div>${ctx.role==='manager'?`<div class="row"><button data-edit="property:${p.id}">Edit</button><button class="btn-danger" data-del="property:${p.id}">Delete</button></div>`:''}</article>`).join('')}</div>`; };

export const renderReservations = () => {
  const ctx = getCtx(); const list = visibleReservations(ctx);
  const rows = list.map((r)=>{ const c=byId(ctx.data.customers,r.customerId); const p=byId(ctx.data.properties,r.propertyId); const tasks=ctx.data.operations.filter((o)=>o.reservationId===r.id).length; return `<tr><td>${r.id}</td><td>${c?.name||'-'}</td><td>${p?.name||'-'}</td><td>${r.checkIn} → ${r.checkOut}</td><td>${statusBadge(r.status)}</td><td>${tasks}</td>${ctx.role==='manager'?`<td><button data-edit="reservation:${r.id}">Edit</button><button class="btn-danger" data-del="reservation:${r.id}">Delete</button></td>`:''}</tr>`; }).join('');
  return crudView('Reservations', rows, ['ID','Customer','Property','Dates','Status','Tasks'], ctx.role==='manager');
};

export const renderCustomers = () => { const ctx=getCtx(); const rows = ctx.data.customers.map((c)=>`<tr><td>${c.name}</td><td>${c.email}</td><td>${c.phone}</td><td>${ctx.data.reservations.filter((r)=>r.customerId===c.id).length}</td><td><button data-edit="customer:${c.id}">Edit</button><button class="btn-danger" data-del="customer:${c.id}">Delete</button></td></tr>`).join(''); return crudView('Customers', rows, ['Name','Email','Phone','Reservations'], ctx.role==='manager'); };

export const renderOperations = () => {
  const ctx=getCtx(); let list = ctx.data.operations;
  if (ctx.role==='housekeeping') list=list.filter((o)=>o.assignedRole==='housekeeping');
  if (ctx.role==='maintenance') list=list.filter((o)=>o.assignedRole==='maintenance');
  const rows = list.map((o)=>`<tr><td>${o.title}</td><td>${o.category}</td><td>${o.assignedRole}</td><td>${o.dueDate}</td><td>${statusBadge(o.status)}</td><td>${statusBadge(o.priority||'')}</td><td><button data-status="${o.id}:In progress">In progress</button><button data-status="${o.id}:Done">Done</button>${ctx.role==='manager'?`<button data-edit="operation:${o.id}">Edit</button><button class="btn-danger" data-del="operation:${o.id}">Delete</button>`:''}</td></tr>`).join('');
  return crudView(ctx.role==='housekeeping'?'Housekeeping':ctx.role==='maintenance'?'Maintenance':'Operations', rows, ['Title','Category','Assigned','Due','Status','Priority','Update'], ctx.role==='manager');
};

export const renderQuality = () => { const ctx=getCtx(); const list=ctx.data.qualityChecks; return `<div class="panel"><div class="row" style="justify-content:space-between"><h2>Quality Checks</h2>${['manager','inspector'].includes(ctx.role)?'<button id="newQc">Create Quality Check</button>':''}</div><table class="table"><thead><tr><th>ID</th><th>Property</th><th>Status</th><th>Anomalies</th><th>Actions</th></tr></thead><tbody>${list.map((x)=>`<tr><td>${x.id}</td><td>${byId(ctx.data.properties,x.propertyId)?.name||''}</td><td>${statusBadge(x.status)}</td><td>${x.anomalies.length}</td><td><button data-anomaly="${x.id}">Add anomaly</button>${ctx.role==='manager'||ctx.role==='inspector'?`<button data-edit="quality:${x.id}">Edit</button><button class="btn-danger" data-del="quality:${x.id}">Delete</button>`:''}</td></tr>`).join('')}</tbody></table></div>`; };
export const renderDocuments = () => { const ctx=getCtx(); let docs=ctx.data.documents; if(ctx.role==='owner'){ const propIds=visibleProps(ctx).map(p=>p.id); docs=docs.filter((d)=>propIds.includes(d.propertyId)); } return `<div class="panel"><div class="row" style="justify-content:space-between"><h2>Documents</h2>${ctx.role==='manager'?'<button data-create="documents">Create</button>':''}</div><table class="table"><thead><tr><th>Title</th><th>Type</th><th>Property</th><th>Uploaded</th>${ctx.role==='manager'?'<th>Actions</th>':''}</tr></thead><tbody>${docs.map((d)=>`<tr><td>${d.title}</td><td>${d.type}</td><td>${byId(ctx.data.properties,d.propertyId)?.name||''}</td><td>${d.uploadedAt}</td>${ctx.role==='manager'?`<td><button data-edit="document:${d.id}">Edit</button><button class="btn-danger" data-del="document:${d.id}">Delete</button></td>`:''}</tr>`).join('')}</tbody></table></div>`; };
export const renderNotifications = () => { const ctx=getCtx(); const list=ctx.data.notifications.filter((n)=>ctx.role==='manager'||n.userRole===ctx.role); return `<div class="panel"><h2>Notifications</h2>${list.map((n)=>`<div class="card"><div class="row" style="justify-content:space-between"><strong>${n.type}</strong>${statusBadge(n.read?'Read':'Unread')}</div><div>${n.text}</div><div class="small">${fmtDate(n.createdAt)} <button data-notif="${n.id}">Toggle read</button></div></div>`).join('')}</div>`; };
export const renderSettings = () => `<div class="panel"><h2>Settings</h2><p class="small">Use quick role switch in topbar for exhibition storytelling.</p></div>`;
export const renderDemoGuide = () => `<div class="panel"><h2>Exhibition Demo Guide</h2><ol><li>Open Public Booking and create a reservation.</li><li>Switch to Manager: show reservation, tasks, notifications, timeline.</li><li>Switch to Owner: show reservation in owned property list.</li><li>Switch to Housekeeping: show generated prep tasks.</li><li>Switch to Inspector: open quality checks and add anomaly.</li><li>Switch to Maintenance: show generated maintenance task.</li></ol><button id="resetDemo" class="btn-danger">Reset Demo Data</button></div>`;

const promptRequired = (label, initial='') => { const v = prompt(label, initial); if (v===null || !String(v).trim()) return null; return v.trim(); };

export const bindGenericActions = (rerender) => {
  document.body.onclick = (e) => {
    const t = e.target;
    const state = getState().data;
    if (t.dataset.del) {
      const [kind,id]=t.dataset.del.split(':'); if(!confirm('Delete record?'))return;
      const map={property:'properties',reservation:'reservations',customer:'customers',operation:'operations',quality:'qualityChecks',document:'documents'};
      const arr=state[map[kind]]; const ix=arr.findIndex((x)=>x.id===id); if(ix>-1) arr.splice(ix,1); persist(); return rerender();
    }
    if (t.dataset.status) { const [id,status]=t.dataset.status.split(':'); const notes=prompt('Optional notes','')||''; updateTaskStatus(id,status,notes); return rerender(); }
    if (t.dataset.notif) { const n=state.notifications.find((x)=>x.id===t.dataset.notif); n.read=!n.read; persist(); return rerender(); }
    if (t.dataset.anomaly) { const d=promptRequired('Describe anomaly'); if(d) createQualityAnomaly(t.dataset.anomaly,d); return rerender(); }
    if (t.id==='resetDemo') { if(confirm('Reset demo dataset?')) { resetDemo(); location.hash='/login'; location.reload(); } }
    if (t.id==='newQc') { state.qualityChecks.push({ id: uid('q'), propertyId: state.properties[0].id, reservationId:'', inspectorId:'u_inspector', status:'Draft', checklist:['Entry','Kitchen','Bathroom'], notes:'', anomalies:[] }); persist(); return rerender(); }

    if (t.dataset.create) {
      if (t.dataset.create==='properties') {
        const name=promptRequired('Property name'); if(!name) return; state.properties.push({ id: uid('p'), name, city: promptRequired('City','Lisbon')||'Lisbon', type: promptRequired('Type','Apartment')||'Apartment', capacity:Number(promptRequired('Capacity','4')||4), nightlyRate:Number(promptRequired('Nightly rate','180')||180), ownerId:'u_owner', status:'Active', shortDescription:promptRequired('Short description','Premium listing')||'' });
      }
      if (t.dataset.create==='reservations') {
        state.reservations.push({ id: uid('r'), customerId: state.customers[0].id, propertyId: state.properties[0].id, checkIn: new Date().toISOString().slice(0,10), checkOut: new Date(Date.now()+86400000).toISOString().slice(0,10), guestsCount:2, status:'Pending', notes:'Manual reservation' });
      }
      if (t.dataset.create==='customers') {
        const name=promptRequired('Customer name'); const email=promptRequired('Customer email'); if(!name||!email) return; state.customers.push({ id: uid('c'), name, email, phone: promptRequired('Phone','+1-555-0199')||'' });
      }
      if (t.dataset.create==='operations') {
        state.operations.push({ id: uid('o'), title: promptRequired('Task title','General Task')||'General Task', category:'General', propertyId: state.properties[0].id, reservationId:'', assignedRole:'housekeeping', status:'To do', dueDate:new Date().toISOString().slice(0,10), priority:'Medium', notes:'' });
      }
      if (t.dataset.create==='documents') {
        state.documents.push({ id: uid('d'), title: promptRequired('Document title','New document')||'New document', type:'Policy', propertyId: state.properties[0].id, reservationId:'', visibilityByRole:['manager','owner'], uploadedAt:new Date().toISOString().slice(0,10) });
      }
      persist(); return rerender();
    }

    if (t.dataset.edit) {
      const [kind,id]=t.dataset.edit.split(':');
      const map={property:'properties',reservation:'reservations',customer:'customers',operation:'operations',quality:'qualityChecks',document:'documents'};
      const rec=state[map[kind]]?.find((x)=>x.id===id); if(!rec) return;
      if(kind==='property'){ rec.name=promptRequired('Property name',rec.name)||rec.name; rec.city=promptRequired('City',rec.city)||rec.city; rec.shortDescription=promptRequired('Description',rec.shortDescription)||rec.shortDescription; }
      if(kind==='reservation'){ rec.status=promptRequired('Status',rec.status)||rec.status; rec.notes=prompt('Notes',rec.notes)||rec.notes; }
      if(kind==='customer'){ rec.name=promptRequired('Name',rec.name)||rec.name; rec.phone=prompt('Phone',rec.phone)||rec.phone; }
      if(kind==='operation'){ rec.priority=promptRequired('Priority Low/Medium/High/Urgent',rec.priority)||rec.priority; rec.status=promptRequired('Status',rec.status)||rec.status; }
      if(kind==='quality'){ rec.status=promptRequired('Status',rec.status)||rec.status; rec.notes=prompt('Notes',rec.notes)||rec.notes; }
      if(kind==='document'){ rec.title=promptRequired('Title',rec.title)||rec.title; rec.type=promptRequired('Type',rec.type)||rec.type; }
      persist(); return rerender();
    }
  };
};
