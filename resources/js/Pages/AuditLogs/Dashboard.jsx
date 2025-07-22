import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import DashboardLayout from '@/Layouts/DashboardLayout';
import {
    ShieldCheckIcon,
    ExclamationTriangleIcon,
    UserIcon,
    ClockIcon,
    ChartBarIcon,
    EyeIcon,
} from '@heroicons/react/24/outline';
import {
    LineChart,
    Line,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    ResponsiveContainer,
    BarChart,
    Bar,
    PieChart,
    Pie,
    Cell,
} from 'recharts';

export default function Dashboard({ 
    overview, 
    criticalEvents, 
    securityAlerts, 
    moduleActivity, 
    activityChart, 
    topUsers, 
    hours 
}) {
    const { t } = useTranslation();
    const [selectedTimeRange, setSelectedTimeRange] = useState(hours);

    const timeRanges = [
        { value: 1, label: t('audit.time_ranges.last_hour') },
        { value: 24, label: t('audit.time_ranges.last_24_hours') },
        { value: 168, label: t('audit.time_ranges.last_week') },
        { value: 720, label: t('audit.time_ranges.last_month') },
    ];

    const handleTimeRangeChange = (newHours) => {
        setSelectedTimeRange(newHours);
        window.location.href = route('audit-logs.dashboard', { hours: newHours });
    };

    const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884D8'];

    const StatCard = ({ title, value, icon: Icon, color = 'blue', change = null }) => (
        <div className="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div className="p-5">
                <div className="flex items-center">
                    <div className="flex-shrink-0">
                        <Icon className={`h-6 w-6 text-${color}-600`} />
                    </div>
                    <div className="ml-5 w-0 flex-1">
                        <dl>
                            <dt className="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                {title}
                            </dt>
                            <dd className="flex items-baseline">
                                <div className="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {value.toLocaleString()}
                                </div>
                                {change && (
                                    <div className={`ml-2 flex items-baseline text-sm font-semibold ${
                                        change > 0 ? 'text-red-600' : 'text-green-600'
                                    }`}>
                                        {change > 0 ? '+' : ''}{change}%
                                    </div>
                                )}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    );

    return (
        <DashboardLayout>
            <Head title={t('audit.dashboard')} />

            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="md:flex md:items-center md:justify-between mb-6">
                        <div className="min-w-0 flex-1">
                            <h2 className="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                                {t('audit.security_dashboard')}
                            </h2>
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {t('audit.dashboard_subtitle')}
                            </p>
                        </div>
                        <div className="mt-4 flex md:ml-4 md:mt-0 space-x-3">
                            <select
                                value={selectedTimeRange}
                                onChange={(e) => handleTimeRangeChange(parseInt(e.target.value))}
                                className="block border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            >
                                {timeRanges.map(range => (
                                    <option key={range.value} value={range.value}>
                                        {range.label}
                                    </option>
                                ))}
                            </select>
                            <Link
                                href={route('audit-logs.index')}
                                className="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                            >
                                <EyeIcon className="w-4 h-4 mr-2" />
                                {t('audit.view_all_logs')}
                            </Link>
                        </div>
                    </div>

                    {/* Stats Overview */}
                    <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                        <StatCard
                            title={t('audit.stats.total_events')}
                            value={overview.total_events}
                            icon={ChartBarIcon}
                            color="blue"
                        />
                        <StatCard
                            title={t('audit.stats.critical_events')}
                            value={overview.critical_events}
                            icon={ExclamationTriangleIcon}
                            color="red"
                        />
                        <StatCard
                            title={t('audit.stats.successful_logins')}
                            value={overview.successful_logins}
                            icon={UserIcon}
                            color="green"
                        />
                        <StatCard
                            title={t('audit.stats.failed_logins')}
                            value={overview.failed_logins}
                            icon={ShieldCheckIcon}
                            color="yellow"
                        />
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                        {/* Activity Chart */}
                        <div className="bg-white dark:bg-gray-800 shadow rounded-lg">
                            <div className="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h3 className="text-lg font-medium text-gray-900 dark:text-white">
                                    {t('audit.activity_over_time')}
                                </h3>
                            </div>
                            <div className="p-6">
                                <ResponsiveContainer width="100%" height={300}>
                                    <LineChart data={activityChart}>
                                        <CartesianGrid strokeDasharray="3 3" />
                                        <XAxis 
                                            dataKey="label" 
                                            tick={{ fontSize: 12 }}
                                        />
                                        <YAxis tick={{ fontSize: 12 }} />
                                        <Tooltip />
                                        <Line 
                                            type="monotone" 
                                            dataKey="count" 
                                            stroke="#8884d8" 
                                            strokeWidth={2}
                                        />
                                    </LineChart>
                                </ResponsiveContainer>
                            </div>
                        </div>

                        {/* Module Activity */}
                        <div className="bg-white dark:bg-gray-800 shadow rounded-lg">
                            <div className="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h3 className="text-lg font-medium text-gray-900 dark:text-white">
                                    {t('audit.activity_by_module')}
                                </h3>
                            </div>
                            <div className="p-6">
                                <ResponsiveContainer width="100%" height={300}>
                                    <BarChart data={moduleActivity}>
                                        <CartesianGrid strokeDasharray="3 3" />
                                        <XAxis 
                                            dataKey="module" 
                                            tick={{ fontSize: 12 }}
                                            angle={-45}
                                            textAnchor="end"
                                            height={80}
                                        />
                                        <YAxis tick={{ fontSize: 12 }} />
                                        <Tooltip />
                                        <Bar dataKey="count" fill="#8884d8" />
                                    </BarChart>
                                </ResponsiveContainer>
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {/* Critical Events */}
                        <div className="bg-white dark:bg-gray-800 shadow rounded-lg">
                            <div className="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h3 className="text-lg font-medium text-gray-900 dark:text-white">
                                    {t('audit.recent_critical_events')}
                                </h3>
                            </div>
                            <div className="divide-y divide-gray-200 dark:divide-gray-700">
                                {criticalEvents.length > 0 ? (
                                    criticalEvents.slice(0, 5).map((event) => (
                                        <div key={event.id} className="px-6 py-4">
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center min-w-0 flex-1">
                                                    <div className="flex-shrink-0">
                                                        <ExclamationTriangleIcon className="h-5 w-5 text-red-500" />
                                                    </div>
                                                    <div className="ml-3 min-w-0 flex-1">
                                                        <p className="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                            {event.description || event.event}
                                                        </p>
                                                        <p className="text-sm text-gray-500 dark:text-gray-400">
                                                            {event.user?.name || t('common.system')} • {new Date(event.created_at).toLocaleString()}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div className="ml-4 flex-shrink-0">
                                                    <Link
                                                        href={route('audit-logs.show', event.id)}
                                                        className="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                    >
                                                        {t('common.view')}
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <div className="px-6 py-8 text-center">
                                        <ShieldCheckIcon className="mx-auto h-12 w-12 text-gray-400" />
                                        <p className="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            {t('audit.no_critical_events')}
                                        </p>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Top Active Users */}
                        <div className="bg-white dark:bg-gray-800 shadow rounded-lg">
                            <div className="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h3 className="text-lg font-medium text-gray-900 dark:text-white">
                                    {t('audit.top_active_users')}
                                </h3>
                            </div>
                            <div className="divide-y divide-gray-200 dark:divide-gray-700">
                                {topUsers.length > 0 ? (
                                    topUsers.slice(0, 5).map((userActivity) => (
                                        <div key={userActivity.user_id} className="px-6 py-4">
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center min-w-0 flex-1">
                                                    <div className="flex-shrink-0">
                                                        <div className="h-8 w-8 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                                            <UserIcon className="h-4 w-4 text-gray-600 dark:text-gray-300" />
                                                        </div>
                                                    </div>
                                                    <div className="ml-3 min-w-0 flex-1">
                                                        <p className="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                            {userActivity.user?.name || t('common.unknown_user')}
                                                        </p>
                                                        <p className="text-sm text-gray-500 dark:text-gray-400">
                                                            {userActivity.user?.email}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div className="ml-4 flex-shrink-0">
                                                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                        {userActivity.count} {t('audit.activities')}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <div className="px-6 py-8 text-center">
                                        <UserIcon className="mx-auto h-12 w-12 text-gray-400" />
                                        <p className="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            {t('audit.no_user_activity')}
                                        </p>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Security Alerts */}
                    {securityAlerts.length > 0 && (
                        <div className="mt-8">
                            <div className="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg">
                                <div className="px-6 py-4 border-b border-red-200 dark:border-red-700">
                                    <h3 className="text-lg font-medium text-red-900 dark:text-red-100">
                                        {t('audit.security_alerts')}
                                    </h3>
                                </div>
                                <div className="divide-y divide-red-200 dark:divide-red-700">
                                    {securityAlerts.slice(0, 3).map((alert) => (
                                        <div key={alert.id} className="px-6 py-4">
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center min-w-0 flex-1">
                                                    <div className="flex-shrink-0">
                                                        <ExclamationTriangleIcon className="h-5 w-5 text-red-500" />
                                                    </div>
                                                    <div className="ml-3 min-w-0 flex-1">
                                                        <p className="text-sm font-medium text-red-900 dark:text-red-100 truncate">
                                                            {alert.description || alert.event}
                                                        </p>
                                                        <p className="text-sm text-red-700 dark:text-red-300">
                                                            {alert.ip_address} • {new Date(alert.created_at).toLocaleString()}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div className="ml-4 flex-shrink-0">
                                                    <Link
                                                        href={route('audit-logs.show', alert.id)}
                                                        className="text-red-700 hover:text-red-900 dark:text-red-300 dark:hover:text-red-100"
                                                    >
                                                        {t('common.investigate')}
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </DashboardLayout>
    );
}
