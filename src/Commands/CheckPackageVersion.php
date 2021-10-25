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
        Package::projects()->each(function(ProjectContract $project) {
            $project->getProjectClient()
                ->checkPackageVersion();
        });
    }
}