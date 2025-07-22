import React, { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import {
    ArrowUpIcon,
    ArrowDownIcon,
    EyeIcon,
    UserGroupIcon,
    ShieldCheckIcon,
    ChartBarIcon,
    ClockIcon,
    CalendarDaysIcon,
} from '@heroicons/react/24/outline';

const MetricCard = ({ metric, index }) => {
    const { t } = useTranslation();
    const [isVisible, setIsVisible] = useState(false);

    useEffect(() => {
        const timer = setTimeout(() => setIsVisible(true), index * 150);
        return () => clearTimeout(timer);
    }, [index]);

    const getMetricIcon = (type) => {
        const icons = {
            users: UserGroupIcon,
            views: EyeIcon,
            security: ShieldCheckIcon,
            activity: ChartBarIcon,
            time: ClockIcon,
            calendar: CalendarDaysIcon,
        };
        return icons[type] || ChartBarIcon;
    };

    const getGradientClass = (color) => {
        const gradients = {
            blue: 'from-blue-500 to-blue-600',
            green: 'from-green-500 to-green-600',
            purple: 'from-purple-500 to-purple-600',
            yellow: 'from-yellow-500 to-yellow-600',
            red: 'from-red-500 to-red-600',
            indigo: 'from-indigo-500 to-indigo-600',
        };
        return gradients[color] || gradients.blue;
    };

    const Icon = getMetricIcon(metric.type);
    const isPositive = metric.trend > 0;
    const TrendIcon = isPositive ? ArrowUpIcon : ArrowDownIcon;

    return (
        <div 
            className={`
                relative overflow-hidden rounded-xl bg-white dark:bg-gray-800 
                shadow-lg hover:shadow-xl transition-all duration-500 transform
                ${isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'}
                hover:scale-105 border border-gray-200 dark:border-gray-700
            `}
        >
            {/* Background Gradient */}
            <div className={`absolute inset-0 bg-gradient-to-br ${getGradientClass(metric.color)} opacity-5`} />
            
            {/* Content */}
            <div className="relative p-6">
                <div className="flex items-center justify-between mb-4">
                    <div className={`
                        w-12 h-12 rounded-lg bg-gradient-to-br ${getGradientClass(metric.color)}
                        flex items-center justify-center shadow-lg
                    `}>
                        <Icon className="w-6 h-6 text-white" />
                    </div>
                    
                    {metric.trend !== undefined && (
                        <div className={`
                            flex items-center space-x-1 px-2 py-1 rounded-full text-xs font-medium
                            ${isPositive 
                                ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' 
                                : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                            }
                        `}>
                            <TrendIcon className="w-3 h-3" />
                            <span>{Math.abs(metric.trend)}%</span>
                        </div>
                    )}
                </div>

                <div className="space-y-2">
                    <h3 className="text-sm font-medium text-gray-600 dark:text-gray-400">
                        {metric.label}
                    </h3>
                    <p className="text-2xl font-bold text-gray-900 dark:text-white">
                        {typeof metric.value === 'number' 
                            ? metric.value.toLocaleString() 
                            : metric.value
                        }
                    </p>
                    {metric.subtitle && (
                        <p className="text-xs text-gray-500 dark:text-gray-400">
                            {metric.subtitle}
                        </p>
                    )}
                </div>

                {/* Progress Bar */}
                {metric.progress !== undefined && (
                    <div className="mt-4">
                        <div className="flex justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                            <span>{t('dashboard:progress')}</span>
                            <span>{metric.progress}%</span>
                        </div>
                        <div className="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div 
                                className={`h-2 rounded-full bg-gradient-to-r ${getGradientClass(metric.color)} transition-all duration-1000`}
                                style={{ width: `${metric.progress}%` }}
                            />
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default function DashboardMetrics({ metrics = [], loading = false }) {
    const { t } = useTranslation();

    if (loading) {
        return (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {[...Array(4)].map((_, index) => (
                    <div key={index} className="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 animate-pulse">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-lg" />
                            <div className="w-16 h-6 bg-gray-200 dark:bg-gray-700 rounded-full" />
                        </div>
                        <div className="space-y-2">
                            <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4" />
                            <div className="h-8 bg-gray-200 dark:bg-gray-700 rounded w-1/2" />
                            <div className="h-3 bg-gray-200 dark:bg-gray-700 rounded w-full" />
                        </div>
                    </div>
                ))}
            </div>
        );
    }

    return (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {metrics.map((metric, index) => (
                <MetricCard 
                    key={metric.id || index} 
                    metric={metric} 
                    index={index} 
                />
            ))}
        </div>
    );
}
