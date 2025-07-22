<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:module {name : The name of the module}
                           {--force : Overwrite existing module}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new modular component with all necessary files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $moduleName = Str::studly($name);
        $moduleSlug = Str::slug($name);
        $modulePath = app_path("Modules/{$moduleName}");

        // Check if module already exists
        if (File::exists($modulePath) && !$this->option('force')) {
            $this->error("Module {$moduleName} already exists! Use --force to overwrite.");
            return 1;
        }

        $this->info("Creating module: {$moduleName}");

        // Create module directory structure
        $this->createDirectoryStructure($modulePath);

        // Create module files
        $this->createModuleConfig($modulePath, $moduleName, $moduleSlug);
        $this->createController($modulePath, $moduleName);
        $this->createModel($modulePath, $moduleName);
        $this->createService($modulePath, $moduleName);
        $this->createRoutes($modulePath, $moduleSlug);
        $this->createMigration($moduleName);
        $this->createSeeder($modulePath, $moduleName);
        $this->createPolicy($modulePath, $moduleName);
        $this->createRequest($modulePath, $moduleName);
        $this->createResource($modulePath, $moduleName);
        $this->createTranslations($modulePath, $moduleSlug);
        $this->createReactComponents($modulePath, $moduleName);

        $this->info("Module {$moduleName} created successfully!");
        $this->info("Don't forget to:");
        $this->info("1. Run: php artisan migrate");
        $this->info("2. Run: php artisan db:seed --class={$moduleName}Seeder");
        $this->info("3. Add permissions to your RBAC system");

        return 0;
    }

    /**
     * Create directory structure
     */
    protected function createDirectoryStructure(string $modulePath): void
    {
        $directories = [
            '',
            'Controllers',
            'Models',
            'Services',
            'Policies',
            'Requests',
            'Resources',
            'database/migrations',
            'database/seeders',
            'resources/views',
            'resources/lang/en',
            'resources/lang/ar',
            'resources/js/Components',
            'resources/js/Pages',
        ];

        foreach ($directories as $dir) {
            File::makeDirectory($modulePath . '/' . $dir, 0755, true, true);
        }
    }

    /**
     * Create module configuration
     */
    protected function createModuleConfig(string $modulePath, string $moduleName, string $moduleSlug): void
    {
        $config = [
            'name' => $moduleName,
            'display_name' => Str::title(str_replace('-', ' ', $moduleSlug)),
            'description' => "Module for managing {$moduleSlug}",
            'version' => '1.0.0',
            'active' => true,
            'critical' => false,
            'dependencies' => [],
            'permissions' => [
                "{$moduleSlug}.view",
                "{$moduleSlug}.create",
                "{$moduleSlug}.edit",
                "{$moduleSlug}.delete",
            ],
            'navigation' => [
                'name' => $moduleSlug,
                'href' => "/{$moduleSlug}",
                'icon' => 'CubeIcon',
                'order' => 100,
            ],
            'config' => [
                'per_page' => 15,
                'cache_enabled' => true,
            ],
        ];

        File::put($modulePath . '/module.json', json_encode($config, JSON_PRETTY_PRINT));
    }

    /**
     * Create controller
     */
    protected function createController(string $modulePath, string $moduleName): void
    {
        $stub = $this->getStub('controller');
        $content = str_replace(
            ['{{ModuleName}}', '{{moduleName}}'],
            [$moduleName, Str::camel($moduleName)],
            $stub
        );

        File::put($modulePath . "/Controllers/{$moduleName}Controller.php", $content);
    }

    /**
     * Create model
     */
    protected function createModel(string $modulePath, string $moduleName): void
    {
        $stub = $this->getStub('model');
        $content = str_replace(
            ['{{ModuleName}}', '{{tableName}}'],
            [$moduleName, Str::snake(Str::plural($moduleName))],
            $stub
        );

        File::put($modulePath . "/Models/{$moduleName}.php", $content);
    }

    /**
     * Create service
     */
    protected function createService(string $modulePath, string $moduleName): void
    {
        $stub = $this->getStub('service');
        $content = str_replace('{{ModuleName}}', $moduleName, $stub);

        File::put($modulePath . "/Services/{$moduleName}Service.php", $content);
    }

    /**
     * Create routes
     */
    protected function createRoutes(string $modulePath, string $moduleSlug): void
    {
        $stub = $this->getStub('routes');
        $content = str_replace(
            ['{{ModuleName}}', '{{moduleSlug}}'],
            [Str::studly($moduleSlug), $moduleSlug],
            $stub
        );

        File::put($modulePath . '/routes.php', $content);
    }

    /**
     * Create migration
     */
    protected function createMigration(string $moduleName): void
    {
        $tableName = Str::snake(Str::plural($moduleName));
        $migrationName = "create_{$tableName}_table";
        
        $this->call('make:migration', [
            'name' => $migrationName,
            '--path' => "app/Modules/{$moduleName}/database/migrations",
        ]);
    }

    /**
     * Create other files (seeder, policy, request, resource, translations, react components)
     */
    protected function createSeeder(string $modulePath, string $moduleName): void
    {
        $stub = $this->getStub('seeder');
        $content = str_replace('{{ModuleName}}', $moduleName, $stub);
        File::put($modulePath . "/database/seeders/{$moduleName}Seeder.php", $content);
    }

    protected function createPolicy(string $modulePath, string $moduleName): void
    {
        $stub = $this->getStub('policy');
        $content = str_replace('{{ModuleName}}', $moduleName, $stub);
        File::put($modulePath . "/Policies/{$moduleName}Policy.php", $content);
    }

    protected function createRequest(string $modulePath, string $moduleName): void
    {
        $stub = $this->getStub('request');
        $content = str_replace('{{ModuleName}}', $moduleName, $stub);
        File::put($modulePath . "/Requests/Store{$moduleName}Request.php", $content);
    }

    protected function createResource(string $modulePath, string $moduleName): void
    {
        $stub = $this->getStub('resource');
        $content = str_replace('{{ModuleName}}', $moduleName, $stub);
        File::put($modulePath . "/Resources/{$moduleName}Resource.php", $content);
    }

    protected function createTranslations(string $modulePath, string $moduleSlug): void
    {
        $enTranslations = [
            'title' => Str::title(str_replace('-', ' ', $moduleSlug)),
            'create' => 'Create ' . Str::title(str_replace('-', ' ', $moduleSlug)),
            'edit' => 'Edit ' . Str::title(str_replace('-', ' ', $moduleSlug)),
            'delete' => 'Delete ' . Str::title(str_replace('-', ' ', $moduleSlug)),
        ];

        File::put($modulePath . '/resources/lang/en/' . $moduleSlug . '.json', json_encode($enTranslations, JSON_PRETTY_PRINT));
        File::put($modulePath . '/resources/lang/ar/' . $moduleSlug . '.json', json_encode($enTranslations, JSON_PRETTY_PRINT));
    }

    protected function createReactComponents(string $modulePath, string $moduleName): void
    {
        $indexStub = $this->getStub('react-index');
        $formStub = $this->getStub('react-form');
        
        $indexContent = str_replace('{{ModuleName}}', $moduleName, $indexStub);
        $formContent = str_replace('{{ModuleName}}', $moduleName, $formStub);

        File::put($modulePath . "/resources/js/Pages/Index.jsx", $indexContent);
        File::put($modulePath . "/resources/js/Pages/Form.jsx", $formContent);
    }

    /**
     * Get stub content
     */
    protected function getStub(string $type): string
    {
        $stubPath = base_path("stubs/module-{$type}.stub");
        
        if (!File::exists($stubPath)) {
            return $this->getDefaultStub($type);
        }

        return File::get($stubPath);
    }

    /**
     * Get default stub content
     */
    protected function getDefaultStub(string $type): string
    {
        $stubs = [
            'controller' => '<?php

namespace App\Modules\{{ModuleName}}\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\{{ModuleName}}\Models\{{ModuleName}};
use App\Modules\{{ModuleName}}\Services\{{ModuleName}}Service;
use App\Modules\{{ModuleName}}\Requests\Store{{ModuleName}}Request;
use Illuminate\Http\Request;
use Inertia\Inertia;

class {{ModuleName}}Controller extends Controller
{
    protected {{ModuleName}}Service ${{moduleName}}Service;

    public function __construct({{ModuleName}}Service ${{moduleName}}Service)
    {
        $this->{{moduleName}}Service = ${{moduleName}}Service;
    }

    public function index()
    {
        ${{moduleName}}s = $this->{{moduleName}}Service->paginate();
        
        return Inertia::render("{{ModuleName}}/Index", [
            "{{moduleName}}s" => ${{moduleName}}s,
        ]);
    }

    public function create()
    {
        return Inertia::render("{{ModuleName}}/Form");
    }

    public function store(Store{{ModuleName}}Request $request)
    {
        $this->{{moduleName}}Service->create($request->validated());
        
        return redirect()->route("{{moduleName}}.index")
            ->with("success", "{{ModuleName}} created successfully");
    }

    public function edit({{ModuleName}} ${{moduleName}})
    {
        return Inertia::render("{{ModuleName}}/Form", [
            "{{moduleName}}" => ${{moduleName}},
        ]);
    }

    public function update(Store{{ModuleName}}Request $request, {{ModuleName}} ${{moduleName}})
    {
        $this->{{moduleName}}Service->update(${{moduleName}}, $request->validated());
        
        return redirect()->route("{{moduleName}}.index")
            ->with("success", "{{ModuleName}} updated successfully");
    }

    public function destroy({{ModuleName}} ${{moduleName}})
    {
        $this->{{moduleName}}Service->delete(${{moduleName}});
        
        return redirect()->route("{{moduleName}}.index")
            ->with("success", "{{ModuleName}} deleted successfully");
    }
}',

            'model' => '<?php

namespace App\Modules\{{ModuleName}}\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Core\Traits\HasAuditLog;

class {{ModuleName}} extends Model
{
    use HasFactory, LogsActivity, HasAuditLog;

    protected $table = "{{tableName}}";

    protected $fillable = [
        "name",
        "description",
        "active",
    ];

    protected function casts(): array
    {
        return [
            "active" => "boolean",
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(["name", "description", "active"])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function scopeActive($query)
    {
        return $query->where("active", true);
    }
}',

            'service' => '<?php

namespace App\Modules\{{ModuleName}}\Services;

use App\Modules\{{ModuleName}}\Models\{{ModuleName}};
use Illuminate\Pagination\LengthAwarePaginator;

class {{ModuleName}}Service
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return {{ModuleName}}::latest()->paginate($perPage);
    }

    public function create(array $data): {{ModuleName}}
    {
        return {{ModuleName}}::create($data);
    }

    public function update({{ModuleName}} ${{moduleName}}, array $data): {{ModuleName}}
    {
        ${{moduleName}}->update($data);
        return ${{moduleName}};
    }

    public function delete({{ModuleName}} ${{moduleName}}): bool
    {
        return ${{moduleName}}->delete();
    }
}',

            'routes' => '<?php

use Illuminate\Support\Facades\Route;
use App\Modules\{{ModuleName}}\Controllers\{{ModuleName}}Controller;

Route::resource("{{moduleSlug}}", {{ModuleName}}Controller::class);',

            'seeder' => '<?php

namespace App\Modules\{{ModuleName}}\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\{{ModuleName}}\Models\{{ModuleName}};

class {{ModuleName}}Seeder extends Seeder
{
    public function run(): void
    {
        {{ModuleName}}::create([
            "name" => "Sample {{ModuleName}}",
            "description" => "This is a sample {{ModuleName}}",
            "active" => true,
        ]);
    }
}',

            'policy' => '<?php

namespace App\Modules\{{ModuleName}}\Policies;

use App\Models\User;
use App\Modules\{{ModuleName}}\Models\{{ModuleName}};

class {{ModuleName}}Policy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.view");
    }

    public function view(User $user, {{ModuleName}} ${{moduleName}}): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.view");
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.create");
    }

    public function update(User $user, {{ModuleName}} ${{moduleName}}): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.edit");
    }

    public function delete(User $user, {{ModuleName}} ${{moduleName}}): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.delete");
    }
}',

            'request' => '<?php

namespace App\Modules\{{ModuleName}}\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Store{{ModuleName}}Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "name" => "required|string|max:255",
            "description" => "nullable|string",
            "active" => "boolean",
        ];
    }
}',

            'resource' => '<?php

namespace App\Modules\{{ModuleName}}\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class {{ModuleName}}Resource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description,
            "active" => $this->active,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}',

            'react-index' => 'import React from "react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { useTranslation } from "react-i18next";

export default function Index({ {{moduleName}}s }) {
    const { t } = useTranslation();

    return (
        <DashboardLayout title="{{ModuleName}}s">
            <div className="space-y-6">
                <div className="flex justify-between items-center">
                    <h1 className="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ModuleName}}s
                    </h1>
                </div>
                
                <div className="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div className="p-6">
                        <p>{{ModuleName}} management interface</p>
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}',

            'react-form' => 'import React from "react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { useTranslation } from "react-i18next";

export default function Form({ {{moduleName}} = null }) {
    const { t } = useTranslation();
    const isEditing = !!{{moduleName}};

    return (
        <DashboardLayout title={isEditing ? "Edit {{ModuleName}}" : "Create {{ModuleName}}"}>
            <div className="space-y-6">
                <div className="flex justify-between items-center">
                    <h1 className="text-2xl font-semibold text-gray-900 dark:text-white">
                        {isEditing ? "Edit {{ModuleName}}" : "Create {{ModuleName}}"}
                    </h1>
                </div>
                
                <div className="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div className="p-6">
                        <p>{{ModuleName}} form interface</p>
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}',
        ];

        return $stubs[$type] ?? '';
    }
}
