<?php
/**
 * Laravel Deployment Helper for Shared Hosting (InfinityFree)
 * Protects actions using a security token.
 * 
 * Usage: /deploy-helper.php?token=secret_bj_token
 * 
 * IMPORTANT: Delete this file after successful deployment!
 */

$token = 'secret_bj_token';

if (!isset($_GET['token']) || $_GET['token'] !== $token) {
    http_response_code(403);
    die('Access Denied: Invalid Security Token.');
}

define('LARAVEL_START', microtime(true));

// Autoload and bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

use Illuminate\Support\Facades\Artisan;

$action = $_GET['action'] ?? null;
$message = '';
$output = '';

if ($action) {
    try {
        switch ($action) {
            case 'migrate':
                $status = Artisan::call('migrate', ['--force' => true]);
                $message = "Migration completed with status: " . $status;
                $output = Artisan::output();
                break;

            case 'migrate-fresh':
                $status = Artisan::call('migrate:fresh', ['--force' => true]);
                $message = "Migration Fresh completed with status: " . $status;
                $output = Artisan::output();
                break;

            case 'db-seed':
                $status = Artisan::call('db:seed', ['--force' => true]);
                $message = "Database Seeding completed with status: " . $status;
                $output = Artisan::output();
                break;

            case 'symlink':
                $target = __DIR__.'/../storage/app/public';
                $shortcut = __DIR__.'/storage';
                
                if (file_exists($shortcut)) {
                    if (is_link($shortcut)) {
                        unlink($shortcut);
                    } else {
                        $message = "Error: 'public/storage' exists and is not a symlink. Please rename or delete it first.";
                        break;
                    }
                }
                
                if (symlink($target, $shortcut)) {
                    $message = "Symlink created successfully!";
                } else {
                    $message = "Error creating symlink. Please check folder permissions.";
                }
                break;

            case 'clear-cache':
                $status = Artisan::call('optimize:clear');
                $message = "Cache optimization cleared with status: " . $status;
                $output = Artisan::output();
                break;

            default:
                $message = "Unknown action.";
        }
    } catch (\Exception $e) {
        $message = "Exception occurred: " . $e->getMessage();
        $output = $e->getTraceAsString();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Deployment Helper</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f3f4f6; color: #1f2937; margin: 0; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        h1 { color: #111827; border-bottom: 2px solid #e5e7eb; padding-bottom: 1rem; margin-top: 0; }
        .btn { display: inline-block; padding: 0.75rem 1.5rem; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; margin-right: 0.5rem; margin-bottom: 0.5rem; transition: background 0.2s; border: none; cursor: pointer; }
        .btn:hover { background: #1d4ed8; }
        .btn-danger { background: #dc2626; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-secondary { background: #4b5563; }
        .btn-secondary:hover { background: #374151; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
        .output { background: #1e293b; color: #f8fafc; padding: 1rem; border-radius: 6px; overflow-x: auto; font-family: monospace; }
        .warning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Laravel Deployment Helper</h1>
        
        <div class="warning">
            <strong>WARNING:</strong> Delete this file (<code>public/deploy-helper.php</code>) from your server once you are done! Leaving it active poses a security risk.
        </div>

        <?php if ($message): ?>
            <div class="alert">
                <strong>Result:</strong> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($output): ?>
            <h3>Command Output:</h3>
            <pre class="output"><?php echo htmlspecialchars($output); ?></pre>
        <?php endif; ?>

        <h2>Actions</h2>
        <div>
            <a href="?token=<?php echo $token; ?>&action=migrate" class="btn">Run Migrations (migrate)</a>
            <a href="?token=<?php echo $token; ?>&action=db-seed" class="btn btn-secondary">Seed Database (db:seed)</a>
            <a href="?token=<?php echo $token; ?>&action=symlink" class="btn">Create Storage Symlink</a>
            <a href="?token=<?php echo $token; ?>&action=clear-cache" class="btn btn-secondary">Clear Cache & Optimize</a>
            <a href="?token=<?php echo $token; ?>&action=migrate-fresh" class="btn btn-danger" onclick="return confirm('Are you sure? This will delete all tables and re-run migrations!');">Fresh Migration (migrate:fresh)</a>
        </div>
    </div>
</body>
</html>
