<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Project;

use Deegitalbe\TrustupProAdminCommon\Contracts\Project\ProjectClientContract;

/**
 * Representing a project using this package
 */
interface ProjectContract
{
    /**
     * Returning this project url.
     * 
     * @return string
     */
    public function getUrl(): string;
    /**
     * Setting this project url.
     * 
     * @return ProjectContract
     */
    public function setUrl(string $url): ProjectContract;

    /**
     * Getting project client.
     * 
     * @return ProjectClientContract
     */
    public function getProjectClient(): ProjectClientContract;
}