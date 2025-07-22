import React, { useState, useEffect } from 'react';
import DashboardLayout from '@/Layouts/DashboardLayout';
import { useTranslation } from 'react-i18next';
import {
    UsersIcon,
    ShieldCheckIcon,
    BuildingOfficeIcon,
    CubeIcon,
    ChartBarIcon,
    ClockIcon,
    ArrowPathIcon,
} from '@heroicons/react/24/outline';
import { LineStatsChart, AreaStatsChart, BarStatsChart, PieStatsChart } from '@/Components/Charts/StatsChart';
import SystemAlerts from '@/Components/Dashboard/SystemAlerts';
import AdvancedStats from '@/Components/Dashboard/AdvancedStats';

export default function Dashboard({ stats, advancedStats, recentActivities, modules, systemAlerts }) {
    const { t } = useTranslation();
    const [loading, setLoading] = useState(false);
    const [refreshKey, setRefreshKey] = useState(0);

    const statCards = [
        {
            name: t('common:navigation.users'),
            value: stats?.users || 0,
            icon: UsersIcon,
            color: 'primary',
            change: '+12%',
            changeType: 'positive',
        },
        {
            name: t('common:navigation.roles'),
            value: stats?.roles || 0,
            icon: ShieldCheckIcon,
            color: 'success',
            change: '+2%',
            changeType: 'positive',
        },
        {
            name: t('common:navigation.departments'),
            value: stats?.departments || 0,
            icon: BuildingOfficeIcon,
            color: 'warning',
            change: '0%',
            changeType: 'neutral',
        },
        {
            name: t('common:navigation.modules'),
            value: modules?.length || 0,
            icon: CubeIcon,
            color: 'secondary',
            change: '+1',
            changeType: 'positive',
        },
    ];

    return (
        <DashboardLayout title={t('common:navigation.dashboard')}>
            <div className="space-y-8">
                {/* Welcome Section - Enhanced with modern gradient */}
                <div className="relative overflow-hidden bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 rounded-2xl shadow-2xl">
                    <div className="absolute inset-0 bg-black/10"></div>
                    <div className="absolute top-0 right-0 w-96 h-96 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                    <div className="absolute bottom-0 left-0 w-64 h-64 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                    <div className="relative px-8 py-12 text-white">
                        <div className="flex items-center justify-between">
                            <div>
                                <h1 className="text-4xl font-bold mb-3 bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">
                                    {t('dashboard:welcome.title')}
                                </h1>
                                <p className="text-xl text-blue-100 mb-4 max-w-2xl">
                                    {t('dashboard:welcome.subtitle')}
                                </p>
                                <div className="flex items-center space-x-4">
                                    <div className="flex items-center space-x-2">
                                        <div className="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                                        <span className="text-sm text-blue-100">النظام يعمل بشكل طبيعي</span>
                                    </div>
                                    <div className="text-sm text-blue-200">
                                        آخر تحديث: {new Date().toLocaleString('ar-SA')}
                                    </div>
                                </div>
                            </div>
                            <div className="hidden lg:block">
                                <div className="w-32 h-32 bg-white/10 rounded-full flex items-center justify-center backdrop-blur-sm">
                                    <ChartBarIcon className="w-16 h-16 text-white/80" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Advanced Stats */}
                <div className="mb-6">
                    <div className="flex items-center justify-between mb-4">
                        <h2 className="text-xl font-semibold text-gray-900 dark:text-white">
                            {t('dashboard:stats.title')}
                        </h2>
                        <button
                            onClick={() => {
                                setLoading(true);
                                setRefreshKey(prev => prev + 1);
                                setTimeout(() => setLoading(false), 1000);
                            }}
                            className="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                            disabled={loading}
                        >
                            <ArrowPathIcon className={`w-4 h-4 mr-2 ${loading ? 'animate-spin' : ''}`} />
                            {t('common:actions.refresh')}
                        </button>
                    </div>
                    <AdvancedStats stats={advancedStats} loading={loading} />
                </div>

                {/* System Alerts */}
                {systemAlerts && systemAlerts.length > 0 && (
                    <div className="mb-6">
                        <SystemAlerts alerts={systemAlerts} />
                    </div>
                )}

                {/* Charts Section */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <LineStatsChart 
                        data={[
                            { name: 'Jan', value: stats?.users || 0 },
                            { name: 'Feb', value: (stats?.users || 0) + 5 },
                            { name: 'Mar', value: (stats?.users || 0) + 12 },
                            { name: 'Apr', value: (stats?.users || 0) + 8 },
                            { name: 'May', value: (stats?.users || 0) + 15 },
                            { name: 'Jun', value: (stats?.users || 0) + 20 },
                        ]}
                        dataKey="value"
                        title={t('dashboard:charts.users_growth')}
                        color="#3B82F6"
                    />
                    <AreaStatsChart 
                        data={[
                            { name: 'Week 1', value: 45 },
                            { name: 'Week 2', value: 52 },
                            { name: 'Week 3', value: 48 },
                            { name: 'Week 4', value: 61 },
                        ]}
                        dataKey="value"
                        title={t('dashboard:charts.activity_trend')}
                        color="#10B981"
                    />
                </div>

                {/* Stats Cards - Enhanced with modern design */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {statCards.map((stat, index) => {
                        const Icon = stat.icon;
                        const gradientClasses = {
                            primary: 'bg-gradient-to-br from-blue-500 to-blue-600',
                            success: 'bg-gradient-to-br from-emerald-500 to-green-600',
                            warning: 'bg-gradient-to-br from-amber-500 to-orange-600',
                            secondary: 'bg-gradient-to-br from-purple-500 to-indigo-600',
                        };
                        
                        const bgGradientClasses = {
                            primary: 'bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20',
                            success: 'bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20',
                            warning: 'bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20',
                            secondary: 'bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20',
                        };

                        const borderClasses = {
                            primary: 'border-blue-200 dark:border-blue-700/50',
                            success: 'border-emerald-200 dark:border-emerald-700/50',
                            warning: 'border-amber-200 dark:border-amber-700/50',
                            secondary: 'border-purple-200 dark:border-purple-700/50',
                        };

                        return (
                            <div key={index} className={`${bgGradientClasses[stat.color]} rounded-2xl p-6 shadow-lg border ${borderClasses[stat.color]} hover:shadow-xl transition-all duration-300 hover:scale-105 group`}>
                                <div className="flex items-center justify-between mb-4">
                                    <div className={`p-4 rounded-xl ${gradientClasses[stat.color]} shadow-lg group-hover:shadow-xl transition-all duration-300`}>
                                        <Icon className="w-8 h-8 text-white" />
                                    </div>
                                    <div className={`px-3 py-1 rounded-full text-xs font-semibold ${
                                        stat.changeType === 'positive' 
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' 
                                            : stat.changeType === 'negative'
                                            ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                            : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400'
                                    }`}>
                                        {stat.change}
                                    </div>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">
                                        {stat.name}
                                    </p>
                                    <p className="text-3xl font-bold text-gray-900 dark:text-white group-hover:scale-110 transition-transform duration-300">
                                        {stat.value.toLocaleString('ar-SA')}
                                    </p>
                                </div>
                                {/* Decorative element */}
                                <div className="absolute top-0 right-0 w-20 h-20 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-150 transition-transform duration-500"></div>
                            </div>
                        );
                    })}
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Recent Activities */}
                    <div className="card">
                        <div className="card-header">
                            <h3 className="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                                <ClockIcon className="w-5 h-5 mr-2" />
                                {t('dashboard:recent_activities.title')}
                            </h3>
                        </div>
                        <div className="card-body">
                            {recentActivities && recentActivities.length > 0 ? (
                                <div className="space-y-4">
                                    {recentActivities.map((activity, index) => (
                                        <div key={index} className="flex items-start space-x-3">
                                            <div className="flex-shrink-0">
                                                <div className="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                                                    <span className="text-xs font-medium text-primary-600 dark:text-primary-400">
                                                        {activity.user?.initials || 'U'}
                                                    </span>
                                                </div>
                                            </div>
                                            <div className="flex-1 min-w-0">
                                                <p className="text-sm text-gray-900 dark:text-white">
                                                    <span className="font-medium">{activity.user?.name}</span>
                                                    {' '}{activity.description}
                                                </p>
                                                <p className="text-xs text-gray-500 dark:text-gray-400">
                                                    {activity.created_at}
                                                </p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-gray-500 dark:text-gray-400 text-center py-8">
                                    {t('common:messages.no_data')}
                                </p>
                            )}
                        </div>
                    </div>

                    {/* Active Modules */}
                    <div className="card">
                        <div className="card-header">
                            <h3 className="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                                <CubeIcon className="w-5 h-5 mr-2" />
                                {t('dashboard:active_modules.title')}
                            </h3>
                        </div>
                        <div className="card-body">
                            {modules && modules.length > 0 ? (
                                <div className="space-y-3">
                                    {modules.map((module) => (
                                        <div key={module.name} className="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <div className="flex items-center space-x-3">
                                                <div className={`w-8 h-8 rounded-lg flex items-center justify-center ${
                                                    module.active 
                                                        ? 'bg-success-100 dark:bg-success-900' 
                                                        : 'bg-gray-300 dark:bg-gray-600'
                                                }`}>
                                                    <CubeIcon className={`w-4 h-4 ${
                                                        module.active 
                                                            ? 'text-success-600 dark:text-success-400' 
                                                            : 'text-gray-500 dark:text-gray-400'
                                                    }`} />
                                                </div>
                                                <div>
                                                    <p className="text-sm font-medium text-gray-900 dark:text-white">
                                                        {module.display_name || module.name}
                                                    </p>
                                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                                        v{module.version || '1.0.0'}
                                                    </p>
                                                </div>
                                            </div>
                                            <span className={`badge ${
                                                module.active ? 'badge-success' : 'badge-secondary'
                                            }`}>
                                                {module.active ? t('common:status.active') : t('common:status.inactive')}
                                            </span>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-gray-500 dark:text-gray-400 text-center py-8">
                                    {t('common:messages.no_data')}
                                </p>
                            )}
                        </div>
                    </div>
                </div>

                {/* Quick Actions - Enhanced with modern design */}
                <div className="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div className="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                        <h3 className="text-xl font-bold text-gray-900 dark:text-white flex items-center">
                            <div className="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                                <ClockIcon className="w-5 h-5 text-white" />
                            </div>
                            {t('dashboard:quick_actions.title')}
                        </h3>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">إجراءات سريعة للوصول المباشر للمهام الأساسية</p>
                    </div>
                    <div className="p-6">
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            <a
                                href="/users/create"
                                className="group relative overflow-hidden bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6 border border-blue-200 dark:border-blue-700/50 hover:shadow-xl hover:scale-105 transition-all duration-300"
                            >
                                <div className="absolute top-0 right-0 w-20 h-20 bg-blue-500/10 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-150 transition-transform duration-500"></div>
                                <div className="relative">
                                    <div className="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                        <UsersIcon className="w-6 h-6 text-white" />
                                    </div>
                                    <h4 className="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">
                                        {t('users:create')}
                                    </h4>
                                    <p className="text-sm text-blue-700 dark:text-blue-300">
                                        {t('dashboard:quick_actions.create_user_desc')}
                                    </p>
                                </div>
                            </a>

                            <a
                                href="/roles/create"
                                className="group relative overflow-hidden bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 rounded-xl p-6 border border-emerald-200 dark:border-emerald-700/50 hover:shadow-xl hover:scale-105 transition-all duration-300"
                            >
                                <div className="absolute top-0 right-0 w-20 h-20 bg-emerald-500/10 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-150 transition-transform duration-500"></div>
                                <div className="relative">
                                    <div className="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                        <ShieldCheckIcon className="w-6 h-6 text-white" />
                                    </div>
                                    <h4 className="text-lg font-semibold text-emerald-900 dark:text-emerald-100 mb-2">
                                        {t('roles:create')}
                                    </h4>
                                    <p className="text-sm text-emerald-700 dark:text-emerald-300">
                                        {t('dashboard:quick_actions.create_role_desc')}
                                    </p>
                                </div>
                            </a>

                            <a
                                href="/departments/create"
                                className="group relative overflow-hidden bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-xl p-6 border border-amber-200 dark:border-amber-700/50 hover:shadow-xl hover:scale-105 transition-all duration-300"
                            >
                                <div className="absolute top-0 right-0 w-20 h-20 bg-amber-500/10 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-150 transition-transform duration-500"></div>
                                <div className="relative">
                                    <div className="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                        <BuildingOfficeIcon className="w-6 h-6 text-white" />
                                    </div>
                                    <h4 className="text-lg font-semibold text-amber-900 dark:text-amber-100 mb-2">
                                        {t('departments:create')}
                                    </h4>
                                    <p className="text-sm text-amber-700 dark:text-amber-300">
                                        {t('dashboard:quick_actions.create_department_desc')}
                                    </p>
                                </div>
                            </a>

                            <a
                                href="/modules"
                                className="group relative overflow-hidden bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 rounded-xl p-6 border border-purple-200 dark:border-purple-700/50 hover:shadow-xl hover:scale-105 transition-all duration-300"
                            >
                                <div className="absolute top-0 right-0 w-20 h-20 bg-purple-500/10 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-150 transition-transform duration-500"></div>
                                <div className="relative">
                                    <div className="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                        <CubeIcon className="w-6 h-6 text-white" />
                                    </div>
                                    <h4 className="text-lg font-semibold text-purple-900 dark:text-purple-100 mb-2">
                                        {t('common:navigation.modules')}
                                    </h4>
                                    <p className="text-sm text-purple-700 dark:text-purple-300">
                                        {t('dashboard:quick_actions.manage_modules_desc')}
                                    </p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}
