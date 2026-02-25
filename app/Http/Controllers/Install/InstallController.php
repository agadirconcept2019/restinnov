<?php

namespace App\Http\Controllers\Install;

use App\Core\Modules\ModuleManager;
use App\Core\Settings\SettingsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Install\AdminAccountRequest;
use App\Http\Requests\Install\DatabaseConfigRequest;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class InstallController extends Controller
{
    public function welcome()
    {
        $checks = [
            'php' => PHP_VERSION,
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'mbstring' => extension_loaded('mbstring'),
            'openssl' => extension_loaded('openssl'),
            'writable_storage' => is_writable(storage_path()),
            'writable_cache' => is_writable(base_path('bootstrap/cache')),
        ];

        return view('install.welcome', compact('checks'));
    }

    public function saveDatabase(DatabaseConfigRequest $request)
    {
        $this->writeEnv([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $request->db_host,
            'DB_PORT' => $request->db_port,
            'DB_DATABASE' => $request->db_database,
            'DB_USERNAME' => $request->db_username,
            'DB_PASSWORD' => $request->db_password ?? '',
        ]);

        config([
            'database.connections.mysql.host' => $request->db_host,
            'database.connections.mysql.port' => $request->db_port,
            'database.connections.mysql.database' => $request->db_database,
            'database.connections.mysql.username' => $request->db_username,
            'database.connections.mysql.password' => $request->db_password,
        ]);

        DB::connection('mysql')->getPdo();

        session(['install.db' => true]);

        return redirect()->route('install.app');
    }

    public function appConfig()
    {
        abort_unless(session('install.db'), 403);

        return view('install.app');
    }

    public function saveAppConfig()
    {
        $data = request()->validate([
            'app_name' => ['required', 'string'],
            'app_url' => ['required', 'url'],
            'locale' => ['required', 'in:en,fr,es'],
            'timezone' => ['required', 'timezone'],
        ]);

        $this->writeEnv([
            'APP_NAME' => '"'.$data['app_name'].'"',
            'APP_URL' => $data['app_url'],
            'APP_LOCALE' => $data['locale'],
            'APP_TIMEZONE' => $data['timezone'],
        ]);

        Artisan::call('key:generate', ['--force' => true]);
        session(['install.app' => true]);

        return redirect()->route('install.system');
    }

    public function systemInstall()
    {
        abort_unless(session('install.app'), 403);

        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder', '--force' => true]);
        Artisan::call('storage:link');

        session(['install.system' => true]);

        return view('install.system');
    }

    public function createAdmin(AdminAccountRequest $request, ModuleManager $moduleManager, SettingsService $settingsService)
    {
        abort_unless(session('install.system'), 403);

        User::query()->updateOrCreate(
            ['email' => $request->email],
            ['name' => $request->name, 'password' => $request->password],
        );

        $moduleManager->activate(['real-estate', 'cms-pages', 'blog', 'forms']);
        $settingsService->put('site_name', config('app.name'));

        File::ensureDirectoryExists(storage_path('app'));
        File::put(storage_path('app/install.lock'), now()->toDateTimeString());

        session()->forget('install');

        return redirect()->route('install.done');
    }

    public function done()
    {
        return view('install.done');
    }

    private function writeEnv(array $pairs): void
    {
        $envPath = base_path('.env');
        $env = File::get($envPath);

        foreach ($pairs as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $line = $key.'='.$value;
            $env = preg_match($pattern, $env) ? preg_replace($pattern, $line, $env) : $env.PHP_EOL.$line;
        }

        File::put($envPath, $env);
    }
}
