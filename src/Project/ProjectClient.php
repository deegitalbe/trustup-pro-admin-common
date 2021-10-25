<?php
namespace Deegitalbe\TrustupProAdminCommon\Project;

use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Henrotaym\LaravelApiClient\Contracts\ClientContract;
use Henrotaym\LaravelApiClient\Contracts\RequestContract;
use Deegitalbe\TrustupProAdminCommon\Project\ProjectCredential;
use Deegitalbe\TrustupProAdminCommon\Contracts\Project\ProjectContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Project\ProjectClientContract;
use Deegitalbe\TrustupProAdminCommon\Exceptions\ProjectClient\NoPackageVersionFound;

/**
 * API client able to communicate with application
 * 
 */
class ProjectClient implements ProjectClientContract
{
    /**
     * Application api client.
     * 
     * @var ClientContract
     */
    protected $client;

    /**
     * Application api client.
     * 
     * @var ProjectContract
     */
    protected $project;

    /**
     * This class should be instanciated with static methods.
     */
    public function __construct(ClientContract $client)
    {
        $this->client = $client;
    }

    /**
     * Checking package version for this project.
     * 
     * @return bool telling is package is outdated.
     */
    public function checkPackageVersion(): bool
    {
        $request = app()->make(RequestContract::class)
            ->setVerb('GET')
            ->addQuery([
                'version' => Package::version()
            ])
            ->setUrl('admin-package/version');
        $response = $this->client->start($request);

        return $response->get()->data->is_outdated;
    }

    /**
     * Construct class based on given project.
     * 
     * @param ProjectContract $project
     * @return self
     */
    public static function forProject(ProjectContract $project): ProjectClientContract
    {
        $client = app()->make(ClientContract::class)
            ->setCredential(ProjectCredential::forProject($project));
        
        return app()->make(ProjectClientContract::class, ['client' => $client])
            ->setProject($project);
    }

    /**
     * Project linked to this client.
     * 
     * @return ProjectContract
     */
    public function getProject(): ProjectContract
    {
        return $this->project;
    }

    /**
     * Setting project linked to this client.
     * 
     * @return ProjectClientContract
     */
    public function setProject(ProjectContract $project): ProjectClientContract
    {
        $this->project = $project;

        return $this;
    }
}