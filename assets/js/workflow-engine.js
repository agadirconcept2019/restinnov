import { getState, persist } from './state.js';
import { uid } from './helpers.js';

const addTimeline = (text) => getState().data.activityTimeline.unshift({ id: uid('t'), text, createdAt: new Date().toISOString() });
const addNotif = (userRole, type, text, propertyId='') => getState().data.notifications.unshift({ id: uid('n'), userRole, type, text, read: false, createdAt: new Date().toISOString(), propertyId });

export function handlePublicBooking(payload) {
  const s = getState().data;
  let customer = s.customers.find((c) => c.email.toLowerCase() === payload.guestEmail.toLowerCase());
  if (!customer) {
    customer = { id: uid('c'), name: payload.guestName, email: payload.guestEmail, phone: payload.phone };
    s.customers.push(customer);
  }
  const reservation = {
    id: uid('r'), customerId: customer.id, propertyId: payload.propertyId, checkIn: payload.checkIn, checkOut: payload.checkOut,
    guestsCount: Number(payload.guestsCount), status: 'Confirmed', notes: payload.notes || ''
  };
  s.reservations.push(reservation);
  const prop = s.properties.find((p) => p.id === payload.propertyId);
  const tasks = [
    { title: 'Arrival Prep', category: 'Arrival Prep', assignedRole: 'housekeeping', dueDate: payload.checkIn },
    { title: 'Check-in Readiness', category: 'Arrival Prep', assignedRole: 'housekeeping', dueDate: payload.checkIn },
    { title: 'Departure Turnover', category: 'Departure Turnover', assignedRole: 'housekeeping', dueDate: payload.checkOut }
  ].map((t) => ({ id: uid('o'), propertyId: payload.propertyId, reservationId: reservation.id, status: 'To do', priority: 'Medium', notes: '', ...t }));
  s.operations.push(...tasks);
  const qc = { id: uid('q'), propertyId: payload.propertyId, reservationId: reservation.id, inspectorId: 'u_inspector', status: 'Scheduled', checklist: ['Entry quality', 'Linen', 'Safety devices'], notes: 'Auto-created from booking', anomalies: [] };
  s.qualityChecks.push(qc);
  addTimeline(`Public booking created for ${payload.guestName} at ${prop?.name}.`);
  ['manager','owner','housekeeping','inspector'].forEach((r) => addNotif(r, 'Reservation', `New confirmed reservation ${reservation.id} for ${prop?.name}.`, payload.propertyId));
  persist();
  return { reservation, tasks, qc, customerCreated: customer.name === payload.guestName && customer.email === payload.guestEmail };
}

export function createQualityAnomaly(qualityCheckId, description) {
  const s = getState().data;
  const q = s.qualityChecks.find((x) => x.id === qualityCheckId);
  if (!q) return null;
  const anomaly = { id: uid('an'), description, createdAt: new Date().toISOString() };
  q.anomalies.push(anomaly);
  const mTask = { id: uid('o'), title: `Fix anomaly: ${description.slice(0,40)}`, category: 'Maintenance', propertyId: q.propertyId, reservationId: q.reservationId || '', assignedRole: 'maintenance', status: 'To do', dueDate: new Date().toISOString().slice(0,10), priority: 'High', notes: `From quality check ${q.id}` };
  s.operations.push(mTask);
  addTimeline(`Quality anomaly created on ${q.id}: ${description}`);
  addNotif('manager','Quality',`Anomaly detected in quality check ${q.id}.`, q.propertyId);
  addNotif('maintenance','Task',`New maintenance task ${mTask.title}`, q.propertyId);
  persist();
  return { anomaly, mTask };
}

export function updateTaskStatus(taskId, status, notes='') {
  const task = getState().data.operations.find((o) => o.id === taskId);
  if (!task) return;
  task.status = status;
  if (notes) task.notes = notes;
  addTimeline(`Task ${task.title} changed to ${status}.`);
  if (['Done','Blocked'].includes(status)) addNotif('manager','Task',`Task ${task.title} is ${status}.`, task.propertyId);
  persist();
}
