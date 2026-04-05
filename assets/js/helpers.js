export const uid = (p = 'id') => `${p}_${Math.random().toString(36).slice(2, 10)}`;
export const fmtDate = (d) => new Date(d).toLocaleDateString();
export const todayPlus = (days = 0) => {
  const d = new Date(); d.setDate(d.getDate() + days); return d.toISOString().slice(0, 10);
};
export const byId = (arr, id) => arr.find((x) => x.id === id);
export const roleLabel = {
  manager: 'Manager', owner: 'Property Owner', housekeeping: 'Housekeeping Agent', maintenance: 'Maintenance Technician', inspector: 'Inspector'
};
