import React, { useEffect } from 'react';
import { Link, Head, router } from '@inertiajs/react';
// import { useTranslation } from 'react-i18next'; // Temporarily disabled
import { LockClosedIcon, ShieldCheckIcon, ChartBarIcon, UserGroupIcon } from '@heroicons/react/24/outline';

// Define route helper function if not available globally
const route = (name, params) => {
  // Try to use window.route if available (from Laravel's ziggy)
  if (window.route) {
    return window.route(name, params);
  }
  
  // Fallback to basic routes
  const routes = {
    'login': '/login',
    'register': '/register',
    'dashboard': '/dashboard'
  };
  
  return routes[name] || '/';
};

export default function Welcome({ canLogin, canRegister, laravelVersion, phpVersion }) {
  // Translation function disabled - using direct English text
  // const { t } = useTranslation(['common']);

  // English text constants
  const translations = {
    'welcome': 'Welcome',
    'appName': 'Admin Dashboard',
    'welcomeMessage': 'A powerful and flexible admin dashboard for managing your application',
    'login': 'Login',
    'register': 'Register',
    'dashboard': 'Dashboard',
    'common:features.rbac': 'Role-Based Access Control',
    'common:featureDescriptions.rbac': 'Manage users with granular permissions and role hierarchies',
    'common:features.security': 'Advanced Security',
    'common:featureDescriptions.security': 'Built-in protection against common web vulnerabilities',
    'common:features.analytics': 'Real-time Analytics',
    'common:featureDescriptions.analytics': 'Monitor system performance and user activity',
    'common:features.audit': 'Comprehensive Audit Logs',
    'common:featureDescriptions.audit': 'Track all changes with detailed audit trails'
  };

  // Simple translation function replacement
  const t = (key) => translations[key] || key;

  return (
    <>
      <Head title={t('welcome')} />
      
      <div className="relative min-h-screen bg-gray-100 dark:bg-gray-900">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
          <div className="text-center">
            <h1 className="text-4xl font-extrabold text-gray-900 dark:text-white sm:text-5xl sm:tracking-tight lg:text-6xl">
              {t('appName')}
            </h1>
            <p className="mt-3 max-w-md mx-auto text-base text-gray-500 dark:text-gray-400 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
              {t('welcomeMessage')}
            </p>
          </div>

          <div className="mt-16">
            <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
              {/* Feature 1 */}
              <div className="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div className="px-4 py-5 sm:p-6">
                  <div className="flex items-center">
                    <div className="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                      <UserGroupIcon className="h-6 w-6 text-white" aria-hidden="true" />
                    </div>
                    <div className="ml-5 w-0 flex-1">
                      <dt className="text-lg font-medium text-gray-900 dark:text-white truncate">
                        {t('common:features.rbac')}
                      </dt>
                      <dd className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {t('common:featureDescriptions.rbac')}
                      </dd>
                    </div>
                  </div>
                </div>
              </div>

              {/* Feature 2 */}
              <div className="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div className="px-4 py-5 sm:p-6">
                  <div className="flex items-center">
                    <div className="flex-shrink-0 bg-green-500 rounded-md p-3">
                      <ShieldCheckIcon className="h-6 w-6 text-white" aria-hidden="true" />
                    </div>
                    <div className="ml-5 w-0 flex-1">
                      <dt className="text-lg font-medium text-gray-900 dark:text-white truncate">
                        {t('common:features.security')}
                      </dt>
                      <dd className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {t('common:featureDescriptions.security')}
                      </dd>
                    </div>
                  </div>
                </div>
              </div>

              {/* Feature 3 */}
              <div className="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div className="px-4 py-5 sm:p-6">
                  <div className="flex items-center">
                    <div className="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                      <ChartBarIcon className="h-6 w-6 text-white" aria-hidden="true" />
                    </div>
                    <div className="ml-5 w-0 flex-1">
                      <dt className="text-lg font-medium text-gray-900 dark:text-white truncate">
                        {t('common:features.analytics')}
                      </dt>
                      <dd className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {t('common:featureDescriptions.analytics')}
                      </dd>
                    </div>
                  </div>
                </div>
              </div>

              {/* Feature 4 */}
              <div className="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div className="px-4 py-5 sm:p-6">
                  <div className="flex items-center">
                    <div className="flex-shrink-0 bg-red-500 rounded-md p-3">
                      <LockClosedIcon className="h-6 w-6 text-white" aria-hidden="true" />
                    </div>
                    <div className="ml-5 w-0 flex-1">
                      <dt className="text-lg font-medium text-gray-900 dark:text-white truncate">
                        {t('common:features.audit')}
                      </dt>
                      <dd className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {t('common:featureDescriptions.audit')}
                      </dd>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div className="mt-10 flex justify-center">
            {canLogin && (
              <div className="space-x-4">
                <Link
                  href={route('login')}
                  className="inline-flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  {t('login')}
                </Link>
                {canRegister && (
                  <Link
                    href={route('register')}
                    className="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                  >
                    {t('register')}
                  </Link>
                )}
              </div>
            )}
            {!canLogin && (
              <Link
                href={route('dashboard')}
                className="inline-flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                {t('dashboard')}
              </Link>
            )}
          </div>

          <div className="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
            Laravel v{laravelVersion} (PHP v{phpVersion})
          </div>
        </div>
      </div>
    </>
  );
}
