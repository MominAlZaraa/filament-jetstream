<?php

namespace Filament\Jetstream\Commands;

use Filament\Jetstream\ComponentResolver;
use Illuminate\Console\Command;

class VerifyStubsCommand extends Command
{
    public $signature = 'filament-jetstream:verify-stubs
                        {--json : Output results as JSON}';

    public $description = 'Verify which Filament Jetstream components and views are being used';

    public function handle(): int
    {
        $diagnostics = ComponentResolver::getDiagnostics();

        if ($this->option('json')) {
            $this->line(json_encode($diagnostics, JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $this->displayDiagnostics($diagnostics);

        return self::SUCCESS;
    }

    protected function displayDiagnostics(array $diagnostics): void
    {
        $this->info('Filament Jetstream Component Discovery Status');
        $this->newLine();

        // Display configuration
        $this->comment('Configuration:');
        $this->line('  Auto Discover: ' . ($diagnostics['config']['auto_discover'] ? 'Enabled' : 'Disabled'));
        $this->line('  Stubs Path: ' . $diagnostics['config']['stubs_path']);
        $this->line('  Views Path: ' . $diagnostics['config']['views_path']);
        $this->newLine();

        // Display component status
        $this->comment('Components:');
        $discoveredCount = 0;
        $totalCount = count($diagnostics['components']);

        foreach ($diagnostics['components'] as $key => $info) {
            $status = $this->getComponentStatus($info);
            $this->line("  [{$status['icon']}] {$key}");
            $this->line("      Class: {$info['class_name']}");
            $this->line("      File: {$info['file_path']}");
            $this->line("      Status: {$status['message']}");

            if ($info['discovered']) {
                $discoveredCount++;
            }

            if ($info['config_override']) {
                $this->line("      Override: {$info['config_override']}");
            }

            $this->newLine();
        }

        $this->info("Discovered {$discoveredCount} of {$totalCount} published components");
        $this->newLine();

        // Display view status
        $this->comment('Views:');
        $discoveredViewsCount = 0;
        $totalViewsCount = count($diagnostics['views']);

        foreach ($diagnostics['views'] as $key => $info) {
            $status = $this->getViewStatus($info);
            $this->line("  [{$status['icon']}] {$key}");
            $this->line("      View: {$info['view_name']}");
            $this->line("      File: {$info['file_path']}");
            $this->line("      Status: {$status['message']}");

            if ($info['discovered']) {
                $discoveredViewsCount++;
            }

            $this->newLine();
        }

        $this->info("Discovered {$discoveredViewsCount} of {$totalViewsCount} published views");
        $this->newLine();

        // Summary
        if ($discoveredCount === 0 && $discoveredViewsCount === 0) {
            $this->warn('No published components or views detected. Run "php artisan filament-jetstream:publish-stubs" to publish them.');
        } elseif ($discoveredCount > 0 || $discoveredViewsCount > 0) {
            $this->info('✓ Published components and views are being used!');
            $this->comment('You can now customize these files to suit your needs.');
        }
    }

    protected function getComponentStatus(array $info): array
    {
        if ($info['config_override']) {
            return [
                'icon' => '⚙',
                'message' => 'Using config override',
            ];
        }

        if (! $info['file_exists']) {
            return [
                'icon' => '○',
                'message' => 'Using package default (file not published)',
            ];
        }

        if (! $info['class_exists']) {
            return [
                'icon' => '✗',
                'message' => 'File exists but class not found (check syntax or namespace)',
            ];
        }

        if ($info['discovered']) {
            return [
                'icon' => '✓',
                'message' => 'Using published component',
            ];
        }

        return [
            'icon' => '?',
            'message' => 'Unknown status',
        ];
    }

    protected function getViewStatus(array $info): array
    {
        if (! $info['file_exists']) {
            return [
                'icon' => '○',
                'message' => 'Using package default (file not published)',
            ];
        }

        if ($info['discovered']) {
            return [
                'icon' => '✓',
                'message' => 'Using published view',
            ];
        }

        return [
            'icon' => '?',
            'message' => 'Unknown status',
        ];
    }
}
