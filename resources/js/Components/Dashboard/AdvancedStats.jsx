import React from 'react';
import { useTranslation } from 'react-i18next';
import {
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    MinusIcon,
    UsersIcon,
    ShieldCheckIcon,
    BuildingOfficeIcon,
    CubeIcon,
    ChartBarIcon,
    ClockIcon,
} from '@heroicons/react/24/outline';

const StatCard = ({ stat, icon: Icon, color, index }) => {
    const { t } = useTranslation();

    const getTrendIcon = (changeType) => {
        switch (changeType) {
            case 'positive':
                return ArrowTrendingUpIcon;
            case 'negative':
                return ArrowTrendingDownIcon;
            default:
                return MinusIcon;
        }
    };

    const getTrendColor = (changeType) => {
        switch (changeType) {
            case 'positive':
                return 'text-green-600 dark:text-green-400';
            case 'negative':
                return 'text-red-600 dark:text-red-400';
            default:
                return 'text-gray-500 dark:text-gray-400';
        }
    };

    const getColorClasses = (color) => {
        const colorMap = {
            blue: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
            green: 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
            purple: 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400',
            yellow: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400',
            indigo: 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400',
            pink: 'bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400',
            red: 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
        };
        return colorMap[color] || colorMap.blue;
    };

    const TrendIcon = getTrendIcon(stat.changeType);

    return (
        <div 
            className="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg hover:scale-105 transition-all duration-300 transform"
            style={{ animationDelay: `${index * 100}ms` }}
        >
            <div className="flex items-center justify-between">
                <div className="flex items-center">
                    <div className={`w-12 h-12 rounded-lg flex items-center justify-center ${getColorClasses(color).split(' ').slice(0, 2).join(' ')}`}>
                        <Icon className={`w-6 h-6 ${getColorClasses(color).split(' ').slice(2).join(' ')}`} />
                    </div>
                    <div className="ml-4">
                        <p className="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {stat.name}
                        </p>
                        <p className="text-2xl font-bold text-gray-900 dark:text-white">
                            {stat.value.toLocaleString()}
                        </p>
                    </div>
                </div>
                <div className="text-right">
                    <div className={`flex items-center ${getTrendColor(stat.changeType)}`}>
                        <TrendIcon className="w-4 h-4 mr-1" />
                        <span className="text-sm font-medium">
                            {stat.change}
                        </span>
                    </div>
                    <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {stat.period || t('dashboard:stats.vs_last_month')}
                    </p>
                </div>
            </div>
            
            {/* Mini chart or progress bar */}
            {stat.chartData && (
                <div className="mt-4">
                    <div className="flex items-end space-x-1 h-8">
                        {stat.chartData.map((value, chartIndex) => {
                            const height = (value / Math.max(...stat.chartData)) * 100;
                            const chartColorClasses = {
                                blue: 'bg-blue-200 dark:bg-blue-700',
                                green: 'bg-green-200 dark:bg-green-700',
                                purple: 'bg-purple-200 dark:bg-purple-700',
                                yellow: 'bg-yellow-200 dark:bg-yellow-700',
                                indigo: 'bg-indigo-200 dark:bg-indigo-700',
                                pink: 'bg-pink-200 dark:bg-pink-700',
                                red: 'bg-red-200 dark:bg-red-700',
                            };
                            return (
                                <div
                                    key={chartIndex}
                                    className={`${chartColorClasses[color] || chartColorClasses.blue} rounded-sm flex-1 transition-all duration-300`}
                                    style={{ 
                                        height: `${height}%`,
                                        animationDelay: `${chartIndex * 50}ms`
                                    }}
                                />
                            );
                        })}
                    </div>
                </div>
            )}
        </div>
    );
};

export default function AdvancedStats({ stats, loading = false }) {
    const { t } = useTranslation();

    const statConfigs = [
        {
            key: 'users',
            icon: UsersIcon,
            color: 'blue',
            name: t('dashboard:stats.total_users'),
        },
        {
            key: 'active_users',
            icon: UsersIcon,
            color: 'green',
            name: t('dashboard:stats.active_users'),
        },
        {
            key: 'roles',
            icon: ShieldCheckIcon,
            color: 'purple',
            name: t('dashboard:stats.total_roles'),
        },
        {
            key: 'departments',
            icon: BuildingOfficeIcon,
            color: 'yellow',
            name: t('dashboard:stats.total_departments'),
        },
        {
            key: 'modules',
            icon: CubeIcon,
            color: 'indigo',
            name: t('dashboard:stats.active_modules'),
        },
        {
            key: 'activities',
            icon: ChartBarIcon,
            color: 'pink',
            name: t('dashboard:stats.daily_activities'),
        },
    ];

    if (loading) {
        return (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {statConfigs.map((config) => (
                    <div key={config.key} className="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 animate-pulse">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center">
                                <div className="w-12 h-12 rounded-lg bg-gray-200 dark:bg-gray-700" />
                                <div className="ml-4">
                                    <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded w-24 mb-2" />
                                    <div className="h-8 bg-gray-200 dark:bg-gray-700 rounded w-16" />
                                </div>
                            </div>
                            <div className="text-right">
                                <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded w-12 mb-1" />
                                <div className="h-3 bg-gray-200 dark:bg-gray-700 rounded w-16" />
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        );
    }

    return (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-4 md:gap-6">
            {statConfigs.map((config, index) => {
                const statData = stats[config.key];
                if (!statData) return null;

                return (
                    <StatCard
                        key={config.key}
                        index={index}
                        stat={{
                            name: config.name,
                            value: statData.current || 0,
                            change: statData.change || '0%',
                            changeType: statData.changeType || 'neutral',
                            period: statData.period,
                            chartData: statData.chartData,
                        }}
                        icon={config.icon}
                        color={config.color}
                    />
                );
            })}
        </div>
    );
}
