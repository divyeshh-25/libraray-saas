<?php

namespace App\Console\Commands;

use App\Support\Mock;
use Illuminate\Console\Command;
use Illuminate\Foundation\Vite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class ExportStatic extends Command
{
    protected $signature = 'export:static {--path=dist : Output folder (relative to project root)}';

    protected $description = 'Render every UI page to plain HTML so the design can be hosted on any static host';

    public function handle(): int
    {
        if (! File::exists(public_path('build/manifest.json'))) {
            $this->error('Run "npm run build" first (public/build/manifest.json is missing).');

            return self::FAILURE;
        }

        // Never emit dev-server asset URLs, even if `npm run dev` is running.
        app(Vite::class)->useHotFile(storage_path('framework/no-vite-hot'));
        config(['session.driver' => 'array', 'cache.default' => 'array']);

        $out = base_path($this->option('path'));
        File::deleteDirectory($out);
        File::ensureDirectoryExists($out);
        File::copyDirectory(public_path('build'), $out.'/build');
        File::copy(public_path('favicon.ico'), $out.'/favicon.ico');

        $ids = [
            'tenants' => count(Mock::tenants()), 'plans' => count(Mock::plans()), 'branches' => count(Mock::branches()),
            'halls' => count(Mock::halls()), 'staff' => count(Mock::staff()), 'students' => count(Mock::students()),
        ];

        $uris = [];
        foreach (Route::getRoutes() as $route) {
            if (! in_array('GET', $route->methods()) || $route->getActionName() === 'Illuminate\Routing\RedirectController') {
                continue;
            }
            $uri = '/'.ltrim($route->uri(), '/');
            if (str_contains($uri, '{id}')) {
                $section = explode('/', trim($uri, '/'))[0];
                foreach (range(1, $ids[$section] ?? 1) as $id) {
                    $uris[] = str_replace('{id}', (string) $id, $uri);
                }
            } elseif (! str_contains($uri, '{') && ! str_starts_with($uri, '/up') && ! str_starts_with($uri, '/storage')) {
                $uris[] = $uri;
            }
        }

        $count = 0;
        foreach ($uris as $uri) {
            $response = app()->handle(Request::create($uri, 'GET'));
            if ($response->getStatusCode() !== 200) {
                $this->warn("{$uri} -> {$response->getStatusCode()} (skipped)");

                continue;
            }
            File::ensureDirectoryExists($out.$uri);
            // Make every absolute URL root-relative so the site works on any domain.
            $html = str_replace('http://localhost/', '/', $response->getContent());
            $html = preg_replace('#http://localhost(?=["\'])#', '/', $html);
            File::put($out.$uri.'/index.html', $html);
            $count++;
        }

        // Redirect entry points (the routes use server-side redirects, so recreate them as static pages)
        foreach (['/' => '/login', '/settings' => '/settings/fee-structure'] as $from => $to) {
            File::ensureDirectoryExists($out.$from);
            File::put(rtrim($out.$from, '/').'/index.html', '<!doctype html><meta charset="utf-8"><meta http-equiv="refresh" content="0;url='.$to.'"><link rel="canonical" href="'.$to.'"><script>location.replace("'.$to.'")</script>');
        }

        $this->info("Exported {$count} pages to {$this->option('path')}/");

        return self::SUCCESS;
    }
}
