export const can = (role, action, module) => {
  if (role === 'manager') return true;
  if (role === 'owner') return action === 'read' && ['dashboard','properties','reservations','documents','notifications'].includes(module);
  if (role === 'housekeeping') return ['dashboard','housekeeping','notifications','reservations'].includes(module) && ['read','update'].includes(action);
  if (role === 'maintenance') return ['dashboard','maintenance','notifications'].includes(module) && ['read','update'].includes(action);
  if (role === 'inspector') return ['dashboard','quality','notifications','reservations','properties'].includes(module) && ['read','update','create'].includes(action);
  return false;
};

export const menuByRole = (role) => {
  const base = ['dashboard','public-booking','notifications','demo-guide','settings'];
  if (role === 'manager') return ['dashboard','public-booking','properties','reservations','customers','operations','housekeeping','maintenance','quality','documents','notifications','settings','demo-guide'];
  if (role === 'owner') return ['dashboard','properties','reservations','documents','notifications','demo-guide'];
  if (role === 'housekeeping') return ['dashboard','housekeeping','reservations','notifications','demo-guide'];
  if (role === 'maintenance') return ['dashboard','maintenance','notifications','demo-guide'];
  if (role === 'inspector') return ['dashboard','quality','properties','reservations','notifications','demo-guide'];
  return base;
};
