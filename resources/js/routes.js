// resources/js/routes.js

import Dashboard from './pages/Dashboard.vue';
import Login from './pages/auth/Login.vue';
import Register from './pages/auth/Register.vue';
import NotFound from './pages/NotFound.vue';

// Member routes
import MemberList from './pages/members/MemberList.vue';
import MemberCreate from './pages/members/MemberCreate.vue';
import MemberEdit from './pages/members/MemberEdit.vue';
import MemberView from './pages/members/MemberView.vue';

// Family routes
import FamilyList from './pages/families/FamilyList.vue';
import FamilyCreate from './pages/families/FamilyCreate.vue';
import FamilyEdit from './pages/families/FamilyEdit.vue';
import FamilyView from './pages/families/FamilyView.vue';

// Attendance routes
import AttendanceList from './pages/attendance/AttendanceList.vue';
import AttendanceCreate from './pages/attendance/AttendanceCreate.vue';

// Finance routes
import DonationList from './pages/finance/DonationList.vue';
import DonationCreate from './pages/finance/DonationCreate.vue';
import DonationEdit from './pages/finance/DonationEdit.vue';
import ExpenseList from './pages/finance/ExpenseList.vue';
import ExpenseCreate from './pages/finance/ExpenseCreate.vue';
import ExpenseEdit from './pages/finance/ExpenseEdit.vue';
import CampaignList from './pages/finance/CampaignList.vue';
import BudgetList from './pages/finance/BudgetList.vue';
import BudgetCreate from './pages/finance/BudgetCreate.vue';
import BudgetEdit from './pages/finance/BudgetEdit.vue';
import BudgetDetail from './pages/finance/BudgetDetail.vue';
import PledgeList from './pages/finance/PledgeList.vue';
import PledgeCreate from './pages/finance/PledgeCreate.vue';
import PledgeEdit from './pages/finance/PledgeEdit.vue';
import PledgeDetail from './pages/finance/PledgeDetail.vue';
import FinancialDashboard from './pages/finance/FinancialDashboard.vue';
import TaxReceiptList from './pages/finance/TaxReceiptList.vue';

// Event routes
import EventList from './pages/events/EventList.vue';
import EventCreate from './pages/events/EventCreate.vue';
import EventEdit from './pages/events/EventEdit.vue';

// Communication routes
import CommunicationList from './pages/communication/CommunicationList.vue';
import CommunicationCreate from './pages/communication/CommunicationCreate.vue';
import PrayerRequestList from './pages/communication/PrayerRequestList.vue';

// Volunteer routes
import VolunteerList from './pages/volunteers/VolunteerList.vue';
import VolunteerRoleList from './pages/volunteers/VolunteerRoleList.vue';

// Report routes
import ReportList from './pages/reports/ReportList.vue';
import ReportCreate from './pages/reports/ReportCreate.vue';
import ReportView from './pages/reports/ReportView.vue';
import ReportEdit from './pages/reports/ReportEdit.vue';
import ReportDashboard from './pages/reports/ReportDashboard.vue';
import TemplateManager from './pages/reports/TemplateManager.vue';

export default [
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/login',
    component: Login,
    name: 'login',
    meta: { requiresAuth: false }
  },
  {
    path: '/register',
    component: Register,
    name: 'register',
    meta: { requiresAuth: false }
  },
  {
    path: '/dashboard',
    component: Dashboard,
    name: 'dashboard',
    meta: { requiresAuth: true }
  },
  
  // Member routes
  {
    path: '/members',
    component: MemberList,
    name: 'members',
    meta: { requiresAuth: true }
  },
  {
    path: '/members/create',
    component: MemberCreate,
    name: 'members.create',
    meta: { requiresAuth: true }
  },
  {
    path: '/members/:id',
    component: MemberView,
    name: 'members.view',
    meta: { requiresAuth: true }
  },
  {
    path: '/members/:id/edit',
    component: MemberEdit,
    name: 'members.edit',
    meta: { requiresAuth: true }
  },
  
  // Family routes
  {
    path: '/families',
    component: FamilyList,
    name: 'families',
    meta: { requiresAuth: true }
  },
  {
    path: '/families/create',
    component: FamilyCreate,
    name: 'families.create',
    meta: { requiresAuth: true }
  },
  {
    path: '/families/:id',
    component: FamilyView,
    name: 'families.view',
    meta: { requiresAuth: true }
  },
  {
    path: '/families/:id/edit',
    component: FamilyEdit,
    name: 'families.edit',
    meta: { requiresAuth: true }
  },
  
  // Attendance routes
  {
    path: '/attendance',
    component: AttendanceList,
    name: 'attendance',
    meta: { requiresAuth: true }
  },
  {
    path: '/attendance/create',
    component: AttendanceCreate,
    name: 'attendance.create',
    meta: { requiresAuth: true }
  },
  
  // Finance routes
  {
    path: '/finance/dashboard',
    component: FinancialDashboard,
    name: 'finance.dashboard',
    meta: { requiresAuth: true }
  },
  {
    path: '/donations',
    component: DonationList,
    name: 'donations',
    meta: { requiresAuth: true }
  },
  {
    path: '/donations/create',
    component: DonationCreate,
    name: 'donations.create',
    meta: { requiresAuth: true }
  },
  {
    path: '/donations/:id/edit',
    component: DonationEdit,
    name: 'donations.edit',
    meta: { requiresAuth: true }
  },
  {
    path: '/expenses',
    component: ExpenseList,
    name: 'expenses',
    meta: { requiresAuth: true }
  },
  {
    path: '/expenses/create',
    component: ExpenseCreate,
    name: 'expenses.create',
    meta: { requiresAuth: true }
  },
  {
    path: '/expenses/:id/edit',
    component: ExpenseEdit,
    name: 'expenses.edit',
    meta: { requiresAuth: true }
  },
  {
    path: '/campaigns',
    component: CampaignList,
    name: 'campaigns',
    meta: { requiresAuth: true }
  },
  {
    path: '/budgets',
    component: BudgetList,
    name: 'budgets',
    meta: { requiresAuth: true }
  },
  {
    path: '/budgets/create',
    component: BudgetCreate,
    name: 'budgets.create',
    meta: { requiresAuth: true }
  },
  {
    path: '/budgets/:id',
    component: BudgetDetail,
    name: 'budgets.view',
    meta: { requiresAuth: true }
  },
  {
    path: '/budgets/:id/edit',
    component: BudgetEdit,
    name: 'budgets.edit',
    meta: { requiresAuth: true }
  },
  {
    path: '/pledges',
    component: PledgeList,
    name: 'pledges',
    meta: { requiresAuth: true }
  },
  {
    path: '/pledges/create',
    component: PledgeCreate,
    name: 'pledges.create',
    meta: { requiresAuth: true }
  },
  {
    path: '/pledges/:id',
    component: PledgeDetail,
    name: 'pledges.view',
    meta: { requiresAuth: true }
  },
  {
    path: '/pledges/:id/edit',
    component: PledgeEdit,
    name: 'pledges.edit',
    meta: { requiresAuth: true }
  },
  {
    path: '/tax-receipts',
    component: TaxReceiptList,
    name: 'tax-receipts',
    meta: { requiresAuth: true }
  },
  
  // Event routes
  {
    path: '/events',
    component: EventList,
    name: 'events',
    meta: { requiresAuth: true }
  },
  {
    path: '/events/create',
    component: EventCreate,
    name: 'events.create',
    meta: { requiresAuth: true }
  },
  {
    path: '/events/:id/edit',
    component: EventEdit,
    name: 'events.edit',
    meta: { requiresAuth: true }
  },
  
  // Communication routes
  {
    path: '/communications',
    component: CommunicationList,
    name: 'communications',
    meta: { requiresAuth: true }
  },
  {
    path: '/communications/create',
    component: CommunicationCreate,
    name: 'communications.create',
    meta: { requiresAuth: true }
  },
  {
    path: '/prayer-requests',
    component: PrayerRequestList,
    name: 'prayer-requests',
    meta: { requiresAuth: true }
  },
  
  // Volunteer routes
  {
    path: '/volunteers',
    component: VolunteerList,
    name: 'volunteers',
    meta: { requiresAuth: true }
  },
  {
    path: '/volunteer-roles',
    component: VolunteerRoleList,
    name: 'volunteer-roles',
    meta: { requiresAuth: true }
  },
  
  // Report routes
  {
    path: '/reports',
    component: ReportList,
    name: 'reports',
    meta: { requiresAuth: true }
  },
  {
    path: '/reports/dashboard',
    component: ReportDashboard,
    name: 'reports.dashboard',
    meta: { requiresAuth: true }
  },
  {
    path: '/reports/create',
    component: ReportCreate,
    name: 'reports.create',
    meta: { requiresAuth: true }
  },
  {
    path: '/reports/:id',
    component: ReportView,
    name: 'reports.view',
    meta: { requiresAuth: true }
  },
  {
    path: '/reports/:id/edit',
    component: ReportEdit,
    name: 'reports.edit',
    meta: { requiresAuth: true }
  },
  {
    path: '/reports/templates',
    component: TemplateManager,
    name: 'reports.templates',
    meta: { requiresAuth: true }
  },
  
  // 404 route
  {
    path: '/:pathMatch(.*)*',
    component: NotFound,
    name: 'not-found'
  }
];
