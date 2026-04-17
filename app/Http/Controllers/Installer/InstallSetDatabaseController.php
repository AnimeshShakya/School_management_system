<?php

declare(strict_types=1);

namespace App\Http\Controllers\Installer;

use dacoto\EnvSet\EnvSetEditor;
use dacoto\LaravelWizardInstaller\Controllers\InstallFolderController;
use dacoto\LaravelWizardInstaller\Controllers\InstallServerController;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use PDO;
use PDOException;

class InstallSetDatabaseController extends Controller
{
    public function __construct(
        public readonly EnvSetEditor $envEditor,
    ) {}

    public function __invoke(Request $request): RedirectResponse
    {
        if (!(new InstallServerController())->check() || !(new InstallFolderController())->check()) {
            return redirect()->route('install.folders');
        }

        $request->validate([
            'database_hostname' => ['required', 'string'],
            'database_port' => ['required', 'integer', 'between:1,65535'],
            'database_name' => ['required', 'string'],
            'database_username' => ['required', 'string'],
            'database_password' => ['nullable', 'string'],
            'database_prefix' => ['nullable', 'string'],
        ]);

        if (!extension_loaded('pdo_mysql')) {
            return back()
                ->withErrors('Database connection failed: PHP extension <strong>pdo_mysql</strong> is not installed or enabled.')
                ->withInput();
        }

        $host = trim((string) $request->input('database_hostname'));
        $port = trim((string) $request->input('database_port', '3306'));
        $database = trim((string) $request->input('database_name'));
        $username = trim((string) $request->input('database_username'));
        $password = (string) $request->input('database_password', '');

        $hostsToTry = [$host];
        if (strtolower($host) === 'localhost') {
            $hostsToTry[] = '127.0.0.1';
        }

        $portsToTry = [$port];
        if ($port !== '3306') {
            $portsToTry[] = '3306';
        }

        $lastException = null;
        $resolvedHost = $host;
        $resolvedPort = $port;

        foreach (array_unique($hostsToTry) as $hostToTry) {
            foreach (array_unique($portsToTry) as $portToTry) {
                try {
                    $connection = new PDO(
                        sprintf('mysql:host=%s;port=%s;dbname=%s', $hostToTry, $portToTry, $database),
                        $username,
                        $password,
                        [
                            PDO::ATTR_TIMEOUT => 5,
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        ]
                    );

                    // Force an actual query to verify credentials and selected database.
                    $connection->query('SELECT 1');
                    $resolvedHost = $hostToTry;
                    $resolvedPort = $portToTry;
                    $lastException = null;
                    break 2;
                } catch (PDOException $e) {
                    $lastException = $e;
                }
            }
        }

        if ($lastException instanceof PDOException) {
            $message = $this->buildConnectionErrorMessage($lastException->getMessage(), $host, $port);
            return back()->withErrors($message)->withInput();
        }

        try {
            $this->envEditor->setKey('DB_HOST', $resolvedHost);
            $this->envEditor->setKey('DB_PORT', $resolvedPort ?: '3306');
            $this->envEditor->setKey('DB_DATABASE', $database);
            $this->envEditor->setKey('DB_USERNAME', $username);
            $this->envEditor->setKey('DB_PASSWORD', $password);

            if ($request->filled('database_prefix')) {
                $this->envEditor->setKey('DB_PREFIX', (string) $request->input('database_prefix'));
            }

            $this->envEditor->save();
        } catch (Exception $e) {
            return back()->withErrors('Unable to save database settings: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('install.migrations');
    }

    private function buildConnectionErrorMessage(string $rawError, string $host, string $port): string
    {
        $safeError = e($rawError);

        $hints = [
            '1) Confirm host, port, database, username, and password.',
            '2) Ensure MySQL service is running and reachable from this server.',
            '3) Ensure PHP extension <strong>pdo_mysql</strong> is installed and enabled.',
        ];

        if (stripos($rawError, 'Connection refused') !== false) {
            $hints[] = sprintf('4) Connection to <strong>%s:%s</strong> was refused; verify MySQL is listening on that host/port and firewall allows it.', e($host), e($port));
        }

        if (stripos($rawError, 'No such file or directory') !== false && strtolower($host) === 'localhost') {
            $hints[] = '4) Try host <strong>127.0.0.1</strong> instead of <strong>localhost</strong> to force TCP instead of socket.';
        }

        if (stripos($rawError, 'Access denied') !== false) {
            $hints[] = '4) Verify the MySQL user has permission for the selected database from this server host.';
        }

        return 'Database connection failed: <strong>' . $safeError . '</strong><br><br><strong>Quick checks:</strong><br>' . implode('<br>', $hints);
    }
}
