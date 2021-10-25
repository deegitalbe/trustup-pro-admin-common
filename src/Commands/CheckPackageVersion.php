<?php
namespace Deegitalbe\TrustupProAdminCommon\Commands;

use Illuminate\Console\Command;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Contracts\Project\ProjectContract;

class CheckPackageVersion extends Command
{
    protected $signature = 'trustup-pro-admin-common:check';

    protected $description = 'Checking if package is outdated somewhere is our projects.';

    public function handle()
    {
        $this->line("Checking projects...");
        Package::projects()->each([$this, 'checkProject']);
    }

    /**
     * Checking project package version.
     * 
     * @param ProjectContract $project
     * @return void
     */
    protected function checkProject(ProjectContract $project)
    {
        $this->line("Checking {$project->getUrl()}..." . PHP_EOL);
        $is_outdated = $project->getProjectClient()->checkPackageVersion();

        if ($is_outdated):
            $this->error("[{$project->getUrl()}] is outdated. Please update to version [" . Package::version() . "]");
            return;
        endif;

        $this->info("[{$project->getUrl()}] is up-to-date.");
    }
}