export const getRoute = () => (location.hash.replace('#/','') || 'login');
export const go = (route) => { location.hash = `/${route}`; };
