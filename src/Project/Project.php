<?php
namespace Deegitalbe\TrustupProAdminCommon\Project;

use Deegitalbe\TrustupProAdminCommon\Contracts\Project\ProjectContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Project\ProjectClientContract;

/**
 * Representing a project using this package
 */
class Project implements ProjectContract
{
    /**
     * Project url
     * 
     * @var string
     */
    protected $url;

    /**
     * Setting this project url.
     * 
     * @return ProjectContract
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Returning this project url.
     * 
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Getting project client.
     * 
     * @return ProjectClientContract
     */
    public function getProjectClient(): ProjectClientContract
    {
        return ProjectClient::forProject($this);
    }
}