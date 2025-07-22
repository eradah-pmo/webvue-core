import React, { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import {
    UserIcon,
    ShieldCheckIcon,
    DocumentIcon,
    CogIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    ClockIcon,
    EyeIcon,
} from '@heroicons/react/24/outline';

const ActivityItem = ({ activity, index }) => {
    const { t } = useTranslation();
    const [isVisible, setIsVisible] = useState(false);

    useEffect(() => {
        const timer = setTimeout(() => setIsVisible(true), index * 100);
        return () => clearTimeout(timer);
    }, [index]);

    const getActivityIcon = (type) => {
        const icons = {
            user: UserIcon,
            security: ShieldCheckIcon,
            document: DocumentIcon,
            system: CogIcon,
            warning: ExclamationTriangleIcon,
            success: CheckCircleIcon,
            view: EyeIcon,
        };
        return icons[type] || DocumentIcon;
    };

    const getActivityColor = (type, severity = 'normal') => {
        const colors = {
            user: 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
            security: 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
            document: 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
            system: 'bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
            warning: 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400',
            success: 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
            view: 'bg-gray-100 text-gray-600 dark:bg-gray-900/30 dark:text-gray-400',
        };
        return colors[type] || colors.document;
    };

    const formatTimeAgo = (timestamp) => {
        const now = new Date();
        const time = new Date(timestamp);
        const diffInMinutes = Math.floor((now - time) / (1000 * 60));
        
        if (diffInMinutes < 1) return t('dashboard:activity.just_now');
        if (diffInMinutes < 60) return t('dashboard:activity.minutes_ago', { count: diffInMinutes });
        
        const diffInHours = Math.floor(diffInMinutes / 60);
        if (diffInHours < 24) return t('dashboard:activity.hours_ago', { count: diffInHours });
        
        const diffInDays = Math.floor(diffInHours / 24);
        return t('dashboard:activity.days_ago', { count: diffInDays });
    };

    const Icon = getActivityIcon(activity.type);

    return (
        <div 
            className={`
                flex items-start space-x-3 p-4 rounded-lg transition-all duration-500
                ${isVisible ? 'translate-x-0 opacity-100' : 'translate-x-4 opacity-0'}
                hover:bg-gray-50 dark:hover:bg-gray-700/50 group
            `}
        >
            <div className={`
                w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                ${getActivityColor(activity.type)} group-hover:scale-110 transition-transform duration-200
            `}>
                <Icon className="w-5 h-5" />
            </div>
            
            <div className="flex-1 min-w-0">
                <div className="flex items-center justify-between">
                    <p className="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {activity.title}
                    </p>
                    <div className="flex items-center space-x-1 text-xs text-gray-500 dark:text-gray-400">
                        <ClockIcon className="w-3 h-3" />
                        <span>{formatTimeAgo(activity.timestamp)}</span>
                    </div>
                </div>
                
                <p className="text-sm text-gray-600 dark:text-gray-300 mt-1">
                    {activity.description}
                </p>
                
                {activity.user && (
                    <p className="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        {t('dashboard:activity.by_user', { user: activity.user })}
                    </p>
                )}
                
                {activity.metadata && (
                    <div className="flex flex-wrap gap-1 mt-2">
                        {Object.entries(activity.metadata).map(([key, value]) => (
                            <span 
                                key={key}
                                className="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200"
                            >
                                {key}: {value}
                            </span>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
};

export default function ActivityFeed({ activities = [], loading = false, showViewAll = true }) {
    const { t } = useTranslation();
    const [filter, setFilter] = useState('all');

    const filterOptions = [
        { value: 'all', label: t('dashboard:activity.all') },
        { value: 'user', label: t('dashboard:activity.user_actions') },
        { value: 'security', label: t('dashboard:activity.security') },
        { value: 'system', label: t('dashboard:activity.system') },
    ];

    const filteredActivities = activities.filter(activity => 
        filter === 'all' || activity.type === filter
    );

    if (loading) {
        return (
            <div className="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
                <div className="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div className="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/3 animate-pulse" />
                </div>
                <div className="p-6 space-y-4">
                    {[...Array(5)].map((_, index) => (
                        <div key={index} className="flex items-start space-x-3 animate-pulse">
                            <div className="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-full" />
                            <div className="flex-1 space-y-2">
                                <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4" />
                                <div className="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2" />
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        );
    }

    return (
        <div className="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
            {/* Header */}
            <div className="p-6 border-b border-gray-200 dark:border-gray-700">
                <div className="flex items-center justify-between">
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                        {t('dashboard:activity.recent_activity')}
                    </h3>
                    
                    {/* Filter Dropdown */}
                    <select
                        value={filter}
                        onChange={(e) => setFilter(e.target.value)}
                        className="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        {filterOptions.map(option => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                </div>
            </div>

            {/* Activity List */}
            <div className="max-h-96 overflow-y-auto">
                {filteredActivities.length > 0 ? (
                    <div className="divide-y divide-gray-200 dark:divide-gray-700">
                        {filteredActivities.slice(0, 10).map((activity, index) => (
                            <ActivityItem 
                                key={activity.id || index} 
                                activity={activity} 
                                index={index} 
                            />
                        ))}
                    </div>
                ) : (
                    <div className="p-8 text-center">
                        <div className="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                            <DocumentIcon className="w-8 h-8 text-gray-400" />
                        </div>
                        <p className="text-gray-500 dark:text-gray-400">
                            {t('dashboard:activity.no_activities')}
                        </p>
                    </div>
                )}
            </div>

            {/* Footer */}
            {showViewAll && filteredActivities.length > 10 && (
                <div className="p-4 border-t border-gray-200 dark:border-gray-700">
                    <button className="w-full text-center text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium transition-colors duration-200">
                        {t('dashboard:activity.view_all')} ({filteredActivities.length})
                    </button>
                </div>
            )}
        </div>
    );
}
