import { todayPlus } from './helpers.js';
export const seedData = () => ({
  users: [
    { id: 'u_manager', email: 'manager@restinnov.demo', password: 'Demo123!', role: 'manager', name: 'Maya Manager' },
    { id: 'u_owner', email: 'owner@restinnov.demo', password: 'Demo123!', role: 'owner', name: 'Owen Owner' },
    { id: 'u_house', email: 'housekeeping@restinnov.demo', password: 'Demo123!', role: 'housekeeping', name: 'Hannah Housekeeping' },
    { id: 'u_maint', email: 'maintenance@restinnov.demo', password: 'Demo123!', role: 'maintenance', name: 'Miles Maintenance' },
    { id: 'u_inspector', email: 'inspector@restinnov.demo', password: 'Demo123!', role: 'inspector', name: 'Irene Inspector' }
  ],
  properties: [
    { id: 'p1', name: 'Azure Loft', city: 'Barcelona', type: 'Apartment', capacity: 4, nightlyRate: 170, ownerId: 'u_owner', status: 'Active', shortDescription: 'Modern loft with terrace.' },
    { id: 'p2', name: 'Palm Villa', city: 'Lisbon', type: 'Villa', capacity: 8, nightlyRate: 320, ownerId: 'u_owner', status: 'Active', shortDescription: 'Private pool family villa.' },
    { id: 'p3', name: 'Canal Studio', city: 'Amsterdam', type: 'Studio', capacity: 2, nightlyRate: 140, ownerId: 'u_owner', status: 'Active', shortDescription: 'Compact canal-side studio.' },
    { id: 'p4', name: 'Old Town Suite', city: 'Prague', type: 'Suite', capacity: 3, nightlyRate: 155, ownerId: 'u_owner', status: 'Maintenance', shortDescription: 'Historic center premium suite.' }
  ],
  customers: Array.from({ length: 8 }).map((_, i) => ({ id: `c${i+1}`, name: `Guest ${i+1}`, email: `guest${i+1}@demo.com`, phone: `+1-555-010${i}` })),
  reservations: Array.from({ length: 8 }).map((_, i) => ({
    id: `r${i+1}`, customerId: `c${(i%8)+1}`, propertyId: `p${(i%4)+1}`, checkIn: todayPlus(i+1), checkOut: todayPlus(i+3), guestsCount: (i%4)+1,
    status: ['Pending', 'Confirmed', 'Checked-in', 'Checked-out'][i%4], notes: 'Seed reservation notes.'
  })),
  operations: Array.from({ length: 14 }).map((_, i) => ({
    id: `o${i+1}`, title: `${['Arrival Prep', 'Departure Turnover', 'Quality', 'Maintenance', 'General'][i%5]} Task ${i+1}`,
    category: ['Arrival Prep', 'Departure Turnover', 'Quality', 'Maintenance', 'General'][i%5], propertyId: `p${(i%4)+1}`, reservationId: `r${(i%8)+1}`,
    assignedRole: ['housekeeping','maintenance','inspector','housekeeping','manager'][i%5],
    status: ['To do','In progress','Done','Blocked'][i%4], dueDate: todayPlus(i+1), priority: ['Low','Medium','High','Urgent'][i%4], notes: ''
  })),
  qualityChecks: Array.from({ length: 4 }).map((_, i) => ({
    id: `q${i+1}`, propertyId: `p${(i%4)+1}`, reservationId: `r${i+1}`, inspectorId: 'u_inspector', status: ['Draft','Scheduled','In progress','Completed'][i],
    checklist: ['Safety devices', 'Bathroom quality', 'Kitchen cleanliness'], notes: 'Initial QA pass', anomalies: []
  })),
  documents: Array.from({ length: 8 }).map((_, i) => ({
    id: `d${i+1}`, title: `Document ${i+1}`, type: ['Invoice','Contract','Inspection','Policy'][i%4], propertyId: `p${(i%4)+1}`,
    reservationId: i%2 ? `r${(i%8)+1}` : '', visibilityByRole: ['manager','owner'], uploadedAt: todayPlus(-i)
  })),
  notifications: Array.from({ length: 16 }).map((_, i) => ({
    id: `n${i+1}`, userRole: ['manager','owner','housekeeping','maintenance','inspector'][i%5], type: ['Reservation','Task','Quality','Document'][i%4],
    text: `Notification ${i+1} generated for demo context.`, read: i%3===0, createdAt: new Date(Date.now()-i*3600000).toISOString(), propertyId: `p${(i%4)+1}`
  })),
  activityTimeline: Array.from({ length: 20 }).map((_, i) => ({ id: `t${i+1}`, text: `Timeline event ${i+1}`, createdAt: new Date(Date.now()-i*5400000).toISOString() }))
});
