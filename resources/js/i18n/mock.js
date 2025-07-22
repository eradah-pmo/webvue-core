// Mock i18n system for temporary English-only mode
// This replaces the full i18next system during development

// English translations mapping
const translations = {
  // Common translations
  'common:app.name': 'Modular Admin Dashboard',
  'common:app.version': '1.0.0',
  'common:welcome': 'Welcome',
  'common:welcomeMessage': 'A comprehensive modular administration system with advanced security, RBAC, and audit logging capabilities.',
  'common:login': 'Login',
  'common:register': 'Register',
  'common:dashboard': 'Dashboard',
  
  // Navigation
  'common:navigation.dashboard': 'Dashboard',
  'common:navigation.test_navigation': 'Navigation Test',
  'common:navigation.users': 'Users',
  'common:navigation.roles': 'Roles & Permissions',
  'common:navigation.departments': 'Departments',
  'common:navigation.modules': 'Modules',
  'common:navigation.settings': 'Settings',
  'common:navigation.audit_logs': 'Audit Logs',
  
  // Common actions
  'common:actions.create': 'Create',
  'common:actions.edit': 'Edit',
  'common:actions.delete': 'Delete',
  'common:actions.save': 'Save',
  'common:actions.cancel': 'Cancel',
  'common:actions.search': 'Search',
  'common:actions.filter': 'Filter',
  'common:actions.export': 'Export',
  'common:actions.import': 'Import',
  'common:actions.refresh': 'Refresh',
  
  // Status
  'common:status.active': 'Active',
  'common:status.inactive': 'Inactive',
  'common:status.enabled': 'Enabled',
  'common:status.disabled': 'Disabled',
  
  // Users module
  'users:title': 'Users Management',
  'users:create': 'Create User',
  'users:edit': 'Edit User',
  'users:list': 'Users List',
  'users:name': 'Name',
  'users:email': 'Email',
  'users:role': 'Role',
  'users:status': 'Status',
  'users:created_at': 'Created At',
  'users:actions': 'Actions',
  
  // Roles module
  'roles:title': 'Roles & Permissions',
  'roles:create': 'Create Role',
  'roles:edit': 'Edit Role',
  'roles:list': 'Roles List',
  'roles:name': 'Role Name',
  'roles:permissions': 'Permissions',
  'roles:users_count': 'Users Count',
  
  // Departments module
  'departments:title': 'Departments',
  'departments:create': 'Create Department',
  'departments:edit': 'Edit Department',
  'departments:list': 'Departments List',
  'departments:name': 'Department Name',
  'departments:parent': 'Parent Department',
  'departments:manager': 'Manager',
  
  // Settings module
  'settings:title': 'System Settings',
  'settings:general': 'General Settings',
  'settings:security': 'Security Settings',
  'settings:notifications': 'Notification Settings',
  'settings:key': 'Setting Key',
  'settings:value': 'Value',
  'settings:description': 'Description',
  
  // Auth module
  'auth:login.title': 'Sign In',
  'auth:login.email': 'Email Address',
  'auth:login.password': 'Password',
  'auth:login.remember': 'Remember Me',
  'auth:login.submit': 'Sign In',
  'auth:register.title': 'Create Account',
  'auth:register.name': 'Full Name',
  'auth:register.email': 'Email Address',
  'auth:register.password': 'Password',
  'auth:register.password_confirmation': 'Confirm Password',
  'auth:register.submit': 'Create Account',
  
  // Dashboard
  'dashboard:welcome': 'Welcome to Dashboard',
  'dashboard:overview': 'System Overview',
  'dashboard:stats': 'Statistics',
  'dashboard:recent_activity': 'Recent Activity',
  'dashboard:quick_actions': 'Quick Actions',
  
  // Audit Logs
  'audit:title': 'Audit Logs',
  'audit:dashboard': 'Audit Dashboard',
  'audit:event': 'Event',
  'audit:user': 'User',
  'audit:timestamp': 'Timestamp',
  'audit:description': 'Description',
  'audit:ip_address': 'IP Address',
  'audit:user_agent': 'User Agent'
};

// Mock translation function
export const mockT = (key, options = {}) => {
  // Handle interpolation if needed
  let translation = translations[key] || key;
  
  // Simple interpolation support
  if (options && typeof options === 'object') {
    Object.keys(options).forEach(optionKey => {
      const placeholder = `{{${optionKey}}}`;
      translation = translation.replace(placeholder, options[optionKey]);
    });
  }
  
  return translation;
};

// Mock useTranslation hook
export const mockUseTranslation = (namespaces = []) => {
  return {
    t: mockT,
    i18n: {
      language: 'en',
      changeLanguage: (lang) => {
        console.log(`Mock i18n: Language change to ${lang} ignored (English-only mode)`);
        return Promise.resolve();
      },
      dir: () => 'ltr'
    },
    ready: true
  };
};

// Mock I18nextProvider component
export const MockI18nextProvider = ({ children }) => {
  return children;
};

console.log('🔧 Mock i18n system loaded - English-only mode active');
