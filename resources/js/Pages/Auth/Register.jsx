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

export default function Register() {
    const { t } = useTranslation(['auth', 'common']);
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <>
            <Head title={t('auth:register.title')} />

            <div className="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
                <div>
                    <Link href="/">
                        <h1 className="text-2xl font-bold text-gray-900 dark:text-white">
                            {t('common:appName')}
                        </h1>
                    </Link>
                </div>

                <div className="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                    <form onSubmit={submit}>
                        <div>
                            <label htmlFor="name" className="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                {t('auth:register.name')}
                            </label>
                            <input
                                id="name"
                                name="name"
                                value={data.name}
                                className="form-input mt-1 block w-full"
                                autoComplete="name"
                                onChange={(e) => setData('name', e.target.value)}
                                required
                            />
                            {errors.name && <div className="text-red-600 text-sm mt-1">{errors.name}</div>}
                        </div>

                        <div className="mt-4">
                            <label htmlFor="email" className="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                {t('auth:register.email')}
                            </label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value={data.email}
                                className="form-input mt-1 block w-full"
                                autoComplete="username"
                                onChange={(e) => setData('email', e.target.value)}
                                required
                            />
                            {errors.email && <div className="text-red-600 text-sm mt-1">{errors.email}</div>}
                        </div>

                        <div className="mt-4">
                            <label htmlFor="password" className="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                {t('auth:register.password')}
                            </label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                value={data.password}
                                className="form-input mt-1 block w-full"
                                autoComplete="new-password"
                                onChange={(e) => setData('password', e.target.value)}
                                required
                            />
                            {errors.password && <div className="text-red-600 text-sm mt-1">{errors.password}</div>}
                        </div>

                        <div className="mt-4">
                            <label htmlFor="password_confirmation" className="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                {t('auth:register.password_confirmation')}
                            </label>
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                value={data.password_confirmation}
                                className="form-input mt-1 block w-full"
                                autoComplete="new-password"
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                required
                            />
                            {errors.password_confirmation && <div className="text-red-600 text-sm mt-1">{errors.password_confirmation}</div>}
                        </div>

                        <div className="flex items-center justify-end mt-4">
                            <Link
                                href={route('login')}
                                className="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                            >
                                {t('auth:register.login')}
                            </Link>

                            <button
                                type="submit"
                                className="ml-4 btn-primary"
                                disabled={processing}
                            >
                                {t('auth:register.submit')}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
