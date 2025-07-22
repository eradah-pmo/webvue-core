<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Core\Services\ModuleService;
use App\Services\DashboardService;
use App\Models\User;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    protected ModuleService $moduleService;
    protected DashboardService $dashboardService;

    public function __construct(ModuleService $moduleService, DashboardService $dashboardService)
    {
        $this->moduleService = $moduleService;
        $this->dashboardService = $dashboardService;
        $this->middleware(['auth', 'verified']);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        
        // التحقق من صلاحية الوصول للوحة التحكم - مؤقتاً معطل
        // if (!$user->can('dashboard.view')) {
        //     abort(403, __('common:errors.unauthorized'));
        // }

        // جمع الإحصائيات المتقدمة
        $advancedStats = $this->dashboardService->getAdvancedStats($user);
        
        // جمع الإحصائيات الأساسية (للتوافق مع الواجهة الحالية)
        $basicStats = $this->getDashboardStats($user);
        
        // جمع الأنشطة الأخيرة
        $recentActivities = $this->getRecentActivities($user);
        
        // جمع الموديولات النشطة
        $activeModules = $this->getActiveModules($user);
        
        // جمع تنبيهات النظام
        $systemAlerts = $this->dashboardService->getSystemAlerts($user);
        
        return Inertia::render('Dashboard', [
            'stats' => $basicStats,
            'advancedStats' => $advancedStats,
            'recentActivities' => $recentActivities,
            'modules' => $activeModules,
            'systemAlerts' => $systemAlerts,
        ]);
    }

    private function getDashboardStats($user): array
    {
        $stats = [
            'users' => 0,
            'departments' => 0,
            'roles' => 0,
            'modules' => 0,
        ];

        // إحصائيات المستخدمين
        if ($user->can('users.view')) {
            if ($user->hasRole('super-admin')) {
                $stats['users'] = User::count();
            } else {
                // إحصائيات حسب نطاق الصلاحية
                $accessibleDepartments = $user->getAccessibleDepartments();
                $stats['users'] = User::whereIn('department_id', $accessibleDepartments->pluck('id'))->count();
            }
        }

        // إحصائيات الأقسام
        if ($user->can('departments.view')) {
            if ($user->hasRole('super-admin')) {
                $stats['departments'] = Department::count();
            } else {
                $stats['departments'] = $user->getAccessibleDepartments()->count();
            }
        }

        // إحصائيات الأدوار
        if ($user->can('roles.view')) {
            $stats['roles'] = Role::count();
        }

        // إحصائيات الموديولات
        $stats['modules'] = $this->moduleService->getActiveModules()->count();

        return $stats;
    }

    private function getRecentActivities($user, int $limit = 10): array
    {
        $query = Activity::with(['causer', 'subject'])
            ->latest()
            ->limit($limit);

        // تطبيق فلتر النطاق إذا لم يكن super-admin
        if (!$user->hasRole('super-admin')) {
            $accessibleUserIds = [];
            
            if ($user->can('users.view')) {
                $accessibleDepartments = $user->getAccessibleDepartments();
                $accessibleUserIds = User::whereIn('department_id', $accessibleDepartments->pluck('id'))
                    ->pluck('id')
                    ->toArray();
            }
            
            if (!empty($accessibleUserIds)) {
                $query->whereIn('causer_id', $accessibleUserIds);
            } else {
                // إذا لم يكن لديه صلاحية عرض أي مستخدمين، عرض أنشطته فقط
                $query->where('causer_id', $user->id);
            }
        }

        return $query->get()->map(function ($activity) {
            return [
                'id' => $activity->id,
                'description' => $activity->description,
                'created_at' => $activity->created_at->diffForHumans(),
                'user' => $activity->causer ? [
                    'id' => $activity->causer->id,
                    'name' => $activity->causer->name,
                    'initials' => $this->getInitials($activity->causer->name),
                    'avatar' => $activity->causer->avatar_url,
                ] : null,
                'subject_type' => $activity->subject_type,
                'subject_id' => $activity->subject_id,
                'properties' => $activity->properties,
            ];
        })->toArray();
    }

    private function getActiveModules($user): array
    {
        return $this->moduleService->getNavigationForUser($user)
            ->map(function ($moduleNav) {
                $module = $this->moduleService->getModule($moduleNav['name']);
                return [
                    'name' => $module['name'],
                    'display_name' => $module['display_name'] ?? $module['name'],
                    'description' => $module['description'] ?? '',
                    'version' => $module['version'] ?? '1.0.0',
                    'active' => $module['active'] ?? false,
                    'critical' => $module['critical'] ?? false,
                    'navigation' => $module['navigation'] ?? null,
                ];
            })
            ->values()
            ->toArray();
    }

    private function getInitials(string $name): string
    {
        $words = explode(' ', trim($name));
        $initials = '';
        
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        
        return $initials ?: 'U';
    }

    public function quickStats(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'users_count' => $user->can('users.view') ? User::count() : 0,
            'active_users_count' => $user->can('users.view') ? User::where('active', true)->count() : 0,
            'departments_count' => $user->can('departments.view') ? Department::count() : 0,
            'roles_count' => $user->can('roles.view') ? Role::count() : 0,
            'recent_activities_count' => Activity::where('created_at', '>=', now()->subDay())->count(),
        ]);
    }
}
