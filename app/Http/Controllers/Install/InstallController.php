<?php

namespace App\Http\Controllers\Install;

use App\Core\Install\EnvWriter;
use App\Core\Install\InstallLogger;
use App\Core\Install\InstallStateStore;
use App\Core\Modules\ModuleManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Install\AdminAccountRequest;
use App\Http\Requests\Install\AppConfigRequest;
use App\Http\Requests\Install\DatabaseConfigRequest;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class InstallController extends Controller
{
    public function __construct(
        private readonly InstallStateStore $stateStore,
        private readonly InstallLogger $logger,
        private readonly EnvWriter $envWriter,
    ) {
    }

    public function step1()
    {
        $checks = $this->serverChecks();

        return view('install.step1', [
            'checks' => $checks,
            'allPassed' => collect($checks)->every(fn ($check) => (bool) $check['passed']),
            'state' => $this->stateStore->get(),
        ]);
    }

    public function step2()
    {
        return view('install.step2', ['state' => $this->stateStore->get()]);
    }

    public function storeStep2(DatabaseConfigRequest $request)
    {
        try {
            config([
                'database.connections.mysql.host' => $request->string('db_host')->toString(),
                'database.connections.mysql.port' => $request->integer('db_port'),
                'database.connections.mysql.database' => $request->string('db_database')->toString(),
                'database.connections.mysql.username' => $request->string('db_username')->toString(),
                'database.connections.mysql.password' => $request->input('db_password', ''),
                'database.connections.mysql.prefix' => $request->input('db_prefix', ''),
            ]);

            DB::connection('mysql')->getPdo();
            $this->stateStore->setStep(2, ['db' => $request->validated()]);

            return redirect()->route('install.step3');
        } catch (\Throwable $e) {
            $this->logger->error('Database connection test failed', ['error' => $e->getMessage()]);

            return back()->withErrors(['db_connection' => 'Connexion à la base impossible. Vérifiez les paramètres.'])->withInput();
        }
    }

    public function step3()
    {
        return view('install.step3', ['state' => $this->stateStore->get()]);
    }

    public function storeStep3(AppConfigRequest $request)
    {
        try {
            $this->envWriter->write([
                'APP_NAME' => $request->input('app_name'),
                'APP_URL' => $request->input('app_url'),
                'APP_TIMEZONE' => $request->input('app_timezone'),
                'APP_LOCALE' => $request->input('default_locale'),
                'APP_FALLBACK_LOCALE' => $request->input('default_locale'),
                'MAIL_FROM_NAME' => $request->input('mail_from_name', ''),
                'MAIL_FROM_ADDRESS' => $request->input('mail_from_address', ''),
            ]);

            if (empty(config('app.key'))) {
                Artisan::call('key:generate', ['--force' => true]);
            }

            $this->stateStore->setStep(3, ['app' => $request->validated()]);

            return redirect()->route('install.step4');
        } catch (\Throwable $e) {
            $this->logger->error('App configuration failed', ['error' => $e->getMessage()]);

            return back()->withErrors(['app_config' => 'Impossible d’écrire la configuration application.'])->withInput();
        }
    }

    public function step4(ModuleManager $moduleManager)
    {
        try {
            $state = $this->stateStore->get();
            $db = $state['payload']['db'] ?? null;

            if (is_array($db)) {
                $this->envWriter->write([
                    'DB_CONNECTION' => 'mysql',
                    'DB_HOST' => $db['db_host'],
                    'DB_PORT' => $db['db_port'],
                    'DB_DATABASE' => $db['db_database'],
                    'DB_USERNAME' => $db['db_username'],
                    'DB_PASSWORD' => $db['db_password'] ?? '',
                    'DB_PREFIX' => $db['db_prefix'] ?? '',
                ]);
            }

            $this->logger->info('Running migrations');
            Artisan::call('migrate', ['--force' => true]);

            $this->logger->info('Seeding core data');
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Core\\CoreSeeder', '--force' => true]);

            $moduleManager->syncRegistry();

            $storageLinked = true;
            try {
                Artisan::call('storage:link');
            } catch (\Throwable $e) {
                $storageLinked = false;
                $this->logger->error('storage:link failed (non blocking)', ['error' => $e->getMessage()]);
            }

            Artisan::call('optimize:clear');

            $this->stateStore->setStep(4, ['storage_linked' => $storageLinked]);

            return view('install.step4', ['storageLinked' => $storageLinked]);
        } catch (\Throwable $e) {
            $this->logger->error('System installation failed', ['error' => $e->getMessage()]);

            return redirect()->route('install.step1')->withErrors(['install' => 'Installation système échouée. Consultez storage/logs/install.log']);
        }
    }

    public function step5()
    {
        return view('install.step5');
    }

    public function storeStep5(AdminAccountRequest $request, ModuleManager $moduleManager)
    {
        $user = User::query()->create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'role' => 'super_admin',
        ]);

        $moduleManager->enable(config('modules.default_enabled', []));
        $this->stateStore->setStep(5, ['admin_user_id' => $user->id]);

        return redirect()->route('install.step6');
    }

    public function step6()
    {
        File::put(storage_path('app/install.lock'), now()->toDateTimeString());
        $this->stateStore->complete();

        return view('install.step6');
    }

    private function serverChecks(): array
    {
        $this->envWriter->ensureEnvExists();

        $requiredExt = ['pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'fileinfo'];

        $checks = [
            'php' => ['label' => 'PHP >= 8.3', 'passed' => version_compare(PHP_VERSION, '8.3.0', '>=')],
            'storage' => ['label' => 'storage writable', 'passed' => is_writable(storage_path())],
            'cache' => ['label' => 'bootstrap/cache writable', 'passed' => is_writable(base_path('bootstrap/cache'))],
            'env' => ['label' => '.env writable', 'passed' => is_writable(base_path('.env'))],
        ];

        foreach ($requiredExt as $ext) {
            $checks['ext_'.$ext] = ['label' => "Extension {$ext}", 'passed' => extension_loaded($ext)];
        }

        return $checks;
    }
}
