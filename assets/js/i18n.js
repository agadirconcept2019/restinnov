import { LANG_KEY } from './storage.js';
const dict = {
  en: { dashboard:'Dashboard', properties:'Properties', reservations:'Reservations', customers:'Customers', operations:'Operations', housekeeping:'Housekeeping', maintenance:'Maintenance', quality:'Quality', documents:'Documents', notifications:'Notifications', settings:'Settings', 'public-booking':'Public Booking', 'demo-guide':'Demo Guide', logout:'Logout', create:'Create', edit:'Edit', delete:'Delete', detail:'Detail', save:'Save' },
  fr: { dashboard:'Tableau de bord', properties:'Propriétés', reservations:'Réservations', customers:'Clients', operations:'Opérations', housekeeping:'Ménage', maintenance:'Maintenance', quality:'Qualité', documents:'Documents', notifications:'Notifications', settings:'Paramètres', 'public-booking':'Réservation publique', 'demo-guide':'Guide démo', logout:'Déconnexion', create:'Créer', edit:'Modifier', delete:'Supprimer', detail:'Détail', save:'Enregistrer' },
  es: { dashboard:'Panel', properties:'Propiedades', reservations:'Reservas', customers:'Clientes', operations:'Operaciones', housekeeping:'Limpieza', maintenance:'Mantenimiento', quality:'Calidad', documents:'Documentos', notifications:'Notificaciones', settings:'Configuración', 'public-booking':'Reserva pública', 'demo-guide':'Guía demo', logout:'Salir', create:'Crear', edit:'Editar', delete:'Eliminar', detail:'Detalle', save:'Guardar' }
};
export const getLang = () => localStorage.getItem(LANG_KEY) || 'en';
export const setLang = (lang) => localStorage.setItem(LANG_KEY, lang);
export const t = (k) => dict[getLang()]?.[k] || dict.en[k] || k;
