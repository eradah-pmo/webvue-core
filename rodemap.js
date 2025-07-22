{
    "project": {
      "name": "Modular Admin Dashboard",
      "frameworks": {
        "backend": "Laravel 11",
        "frontend": "React + Inertia.js",
        "styling": "Tailwind CSS"
      },
      "spa": true,
      "auth": {
        "method": "Sanctum"
      },
      "architecture": {
        "type": "Modular",
        "directory": "app/Modules"
      },
      "rbac": {
        "library": "spatie/laravel-permission",
        "horizontal_roles": ["admin", "manager", "user"],
        "vertical_scopes": ["department", "business_unit", "project"],
        "methods": ["hasRole", "hasPermission", "hasAccess"]
      },
      "logging": {
        "generic": "Spatie Activitylog",
        "sensitive": {
          "table": "audit_logs",
          "method": "Observers"
        }
      },
      "localization": {
        "default": "en",
        "fallback": "ar",
        "rtl_support": true,
        "backend": "Laravel lang files",
        "frontend": {
          "library": "i18next",
          "namespaces": ["common", "auth", "dashboard"]
        },
        "storage": "db_or_session"
      },
      "modules": {
        "structure": {
          "controller": true,
          "model": true,
          "service": true,
          "routes": true
        },
        "config_file": "module.json",
        "config_fields": ["name", "version", "active", "permissions", "dependencies"],
        "features": [
          "auto_register_routes",
          "dynamic_sidebar",
          "on_off_switch",
          "rbac_compliance",
          "i18n_compliance"
        ]
      },
      "frontend": {
        "layout": "DashboardLayout.jsx",
        "features": [
          "Sidebar",
          "Header",
          "InternalNavigation",
          "NoURLReloads",
          "RBACGuards",
          "LocalizedComponents"
        ]
      },
      "database_schema": [
        "users",
        "roles",
        "permissions",
        "activity_log",
        "audit_logs",
        "modules",
        "departments"
      ],
      "cli": {
        "artisan_command": "make:module"
      },
      "testing": {
        "backend": ["unit", "integration", "e2e"],
        "frontend": ["cypress", "playwright"]
      },
      "rbac_matrix": "horizontal × vertical",
      "bonus_features": {
        "safe_mode": true,
        "module_dependencies_check": true,
        "logs": ["module_toggle", "permission_change", "locale_switch"],
        "module_versioning": true
      },
      "deployment": {
        "docker": true,
        "sail": true,
        "phase": "future"
      },
      "initial_modules": ["users", "roles", "departments"]
    }
  }
  