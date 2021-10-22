<?php
namespace Deegitalbe\TrustupProAdminCommon\Project;

use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Henrotaym\LaravelApiClient\Contracts\RequestContract;
use Henrotaym\LaravelApiClient\Contracts\CredentialContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Project\ProjectContract;

/**
 * This credential is used for authenticating requests between our projects using this package.
 */
class ProjectCredential implements CredentialContract
{
    /**
     * Project linked to this credential.
     * 
     * @var ProjectContract
     */
    protected $project;

    /**
     * This class should be instanciated with static methods.
     */
    public function __construct(ProjectContract $project)
    {
        $this->project = $project;
    }

    /**
     * Construct class based on given project url.
     * 
     * @param AppContract $app
     * @return self
     */
    public static function forProject(ProjectContract $project): self
    {
       return app()->make(self::class, ['project' => $project]);
    }

    /**
     * Preparing request.
     */
    public function prepare(RequestContract &$request)
    {
        $request->addHeaders([
            'X-Server-Authorization' => Package::authorization(),
            'X-Requested-With' => "XMLHttpRequest",
            'Content-Type' => "application/json"
        ])
            ->setBaseUrl($this->project->getUrl());
    }
}