<?php

declare(strict_types=1);

namespace Tavp\Hub;

/**
 * CRUD resource generator for TAVPhub admin panel.
 */
class ResourceGenerator
{
    /**
     * Generate a full CRUD resource (model + controller + views).
     */
    public function generate(string $name, array $fields): array
    {
        $files = [];

        // Model
        $files["app/Models/{$name}.php"] = $this->generateModel($name, $fields);

        // Controller
        $files["app/Controllers/Admin/{$name}Controller.php"] = $this->generateController($name, $fields);

        // Views
        $viewDir = strtolower($name);
        $files["resources/views/admin/{$viewDir}/index.volt"] = $this->generateIndexView($name, $fields);
        $files["resources/views/admin/{$viewDir}/create.volt"] = $this->generateCreateView($name, $fields);
        $files["resources/views/admin/{$viewDir}/edit.volt"] = $this->generateEditView($name, $fields);
        $files["resources/views/admin/{$viewDir}/show.volt"] = $this->generateShowView($name, $fields);

        // Migration
        $timestamp = date('Y_m_d_His');
        $files["database/migrations/{$timestamp}_create_" . strtolower($name) . "_table.php"] = $this->generateMigration($name, $fields);

        // Routes
        $files["routes/admin.php"] = $this->generateRoutes($name);

        return $files;
    }

    private function generateModel(string $name, array $fields): string
    {
        $fillable = implode(', ', array_map(fn($f) => "'{$f}'", array_keys($fields)));
        $table = strtolower($name) . 's';

        return <<<PHP
<?php

declare(strict_types=1);

namespace App\Models;

use Tavp\Core\Database\Model;

class {$name} extends Model
{
    protected string \$table = '{$table}';
    public array \$fillable = [{$fillable}];
}
PHP;
    }

    private function generateController(string $name, array $fields): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Tavp\Core\Http\Controller;
use App\Models\\{$name};

class {$name}Controller extends Controller
{
    public function index(): string
    {
        \$items = {$name}::all();
        return \$this->view('admin.' . strtolower('{$name}') . '.index', ['items' => \$items]);
    }

    public function create(): string
    {
        return \$this->view('admin.' . strtolower('{$name}') . '.create');
    }

    public function store(): void
    {
        \$item = new {$name}();
        \$item->fill(\$this->request->getPost());
        \$item->save();
        \$this->response->redirect('/admin/' . strtolower('{$name}'));
    }

    public function show(int \$id): string
    {
        \$item = {$name}::findFirst(\$id);
        return \$this->view('admin.' . strtolower('{$name}') . '.show', ['item' => \$item]);
    }

    public function edit(int \$id): string
    {
        \$item = {$name}::findFirst(\$id);
        return \$this->view('admin.' . strtolower('{$name}') . '.edit', ['item' => \$item]);
    }

    public function update(int \$id): void
    {
        \$item = {$name}::findFirst(\$id);
        \$item->fill(\$this->request->getPost());
        \$item->save();
        \$this->response->redirect('/admin/' . strtolower('{$name}'));
    }

    public function destroy(int \$id): void
    {
        \$item = {$name}::findFirst(\$id);
        \$item->delete();
        \$this->response->redirect('/admin/' . strtolower('{$name}'));
    }
}
PHP;
    }

    private function generateIndexView(string $name, array $fields): string
    {
        $headers = '';
        foreach ($fields as $field => $type) {
            $headers .= "                    <th class=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider\">{$field}</th>\n";
        }

        return <<<VOLT
{% extends 'admin/layouts/app.volt' %}

{% block content %}
<div class="max-w-7xl mx-auto py-8 px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">{{ '{$name}' }}</h1>
        <a href="/admin/{ strtolower($name) }/create" class="bg-blue-600 text-white px-4 py-2 rounded">Create</a>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
{$headers}
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                {% for item in items %}
                <tr>
                    {% for field in fields %}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.{field} }}</td>
                    {% endfor %}
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="/admin/{ strtolower($name) }/{{ item.id }}" class="text-blue-600 hover:underline">View</a>
                        <a href="/admin/{ strtolower($name) }/{{ item.id }}/edit" class="text-yellow-600 hover:underline ml-2">Edit</a>
                    </td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
</div>
{% endblock %}
VOLT;
    }

    private function generateCreateView(string $name, array $fields): string
    {
        return <<<VOLT
{% extends 'admin/layouts/app.volt' %}

{% block content %}
<div class="max-w-3xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-bold mb-6">Create {$name}</h1>
    <form method="POST" action="/admin/{ strtolower($name) }" class="bg-white shadow rounded-lg p-6">
        {% for field, type in fields %}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">{{ field }}</label>
            <input type="text" name="{{ field }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        {% endfor %}
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Save</button>
    </form>
</div>
{% endblock %}
VOLT;
    }

    private function generateEditView(string $name, array $fields): string
    {
        return <<<VOLT
{% extends 'admin/layouts/app.volt' %}

{% block content %}
<div class="max-w-3xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-bold mb-6">Edit {$name}</h1>
    <form method="POST" action="/admin/{ strtolower($name) }/{{ item.id }}" class="bg-white shadow rounded-lg p-6">
        {% for field, type in fields %}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">{{ field }}</label>
            <input type="text" name="{{ field }}" value="{{ item.{field} }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        {% endfor %}
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
    </form>
</div>
{% endblock %}
VOLT;
    }

    private function generateShowView(string $name, array $fields): string
    {
        return <<<VOLT
{% extends 'admin/layouts/app.volt' %}

{% block content %}
<div class="max-w-3xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-bold mb-6">{$name} Details</h1>
    <div class="bg-white shadow rounded-lg p-6">
        {% for field, type in fields %}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">{{ field }}</label>
            <p class="mt-1 text-gray-900">{{ item.{field} }}</p>
        </div>
        {% endfor %}
    </div>
</div>
{% endblock %}
VOLT;
    }

    private function generateMigration(string $name, array $fields): string
    {
        $table = strtolower($name) . 's';
        $timestamp = date('Y_m_d_His');

        $columns = '';
        foreach ($fields as $field => $type) {
            $columns .= "            \$table->{$field}('{$field}', '{$type}');\n";
        }

        return <<<PHP
<?php

use Tavp\Core\Database\Migrations\SchemaBuilder;

class Create{$name}sTable
{
    public function up(SchemaBuilder \$schema): void
    {
        \$schema->createTable('{$table}', function (\$table) use (\$schema) {
            \$table->add(\$schema->column('id', 'integer', ['identity' => true, 'primary' => true]));
{$columns}            \$table->add(\$schema->column('created_at', 'datetime', ['null' => true]));
            \$table->add(\$schema->column('updated_at', 'datetime', ['null' => true]));
        });
    }

    public function down(SchemaBuilder \$schema): void
    {
        \$schema->dropTable('{$table}');
    }
}
PHP;
    }

    private function generateRoutes(string $name): string
    {
        $snake = strtolower($name) . 's';

        return <<<PHP
<?php

\$router->addGet('/admin/{$snake}', [{$name}Controller::class, 'index']);
\$router->addGet('/admin/{$snake}/create', [{$name}Controller::class, 'create']);
\$router->addPost('/admin/{$snake}', [{$name}Controller::class, 'store']);
\$router->addGet('/admin/{$snake}/{id:int}', [{$name}Controller::class, 'show']);
\$router->addGet('/admin/{$snake}/{id:int}/edit', [{$name}Controller::class, 'edit']);
\$router->addPost('/admin/{$snake}/{id:int}', [{$name}Controller::class, 'update']);
\$router->addPost('/admin/{$snake}/{id:int}/delete', [{$name}Controller::class, 'destroy']);
PHP;
    }
}
