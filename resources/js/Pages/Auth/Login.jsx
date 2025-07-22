import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

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
    'dashboard': '/dashboard',
    'password.request': '/forgot-password'
  };
  
  return routes[name] || '/';
};

export default function Login({ status, canResetPassword }) {
    const { t } = useTranslation(['auth', 'common']);
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <>
            <Head title={t('auth:login.title')} />

            <div className="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
                <div>
                    <Link href="/">
                        <h1 className="text-2xl font-bold text-gray-900 dark:text-white">
                            {t('common:appName')}
                        </h1>
                    </Link>
                </div>

                <div className="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                    {status && <div className="mb-4 font-medium text-sm text-green-600">{status}</div>}

                    <form onSubmit={submit}>
                        <div>
                            <label htmlFor="email" className="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                {t('auth:login.email')}
                            </label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value={data.email}
                                className="form-input mt-1 block w-full"
                                autoComplete="username"
                                onChange={(e) => setData('email', e.target.value)}
                            />
                            {errors.email && <div className="text-red-600 text-sm mt-1">{errors.email}</div>}
                        </div>

                        <div className="mt-4">
                            <label htmlFor="password" className="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                {t('auth:login.password')}
                            </label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                value={data.password}
                                className="form-input mt-1 block w-full"
                                autoComplete="current-password"
                                onChange={(e) => setData('password', e.target.value)}
                            />
                            {errors.password && <div className="text-red-600 text-sm mt-1">{errors.password}</div>}
                        </div>

                        <div className="block mt-4">
                            <label className="flex items-center">
                                <input
                                    type="checkbox"
                                    name="remember"
                                    checked={data.remember}
                                    className="form-checkbox"
                                    onChange={(e) => setData('remember', e.target.checked)}
                                />
                                <span className="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                    {t('auth:login.remember')}
                                </span>
                            </label>
                        </div>

                        <div className="flex items-center justify-end mt-4">
                            {canResetPassword && (
                                <Link
                                    href={route('password.request')}
                                    className="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                                >
                                    {t('auth:login.forgot')}
                                </Link>
                            )}

                            <button
                                type="submit"
                                className="ml-3 btn-primary"
                                disabled={processing}
                            >
                                {t('auth:login.submit')}
                            </button>
                        </div>

                        <div className="mt-4 text-center">
                            <Link
                                href={route('register')}
                                className="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                            >
                                {t('auth:login.register')}
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
