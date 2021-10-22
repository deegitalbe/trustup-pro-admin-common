<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Project;

use Deegitalbe\TrustupProAdminCommon\Contracts\Project\ProjectContract;

/**
 * Representing requests that are available between our projects.
 */
interface ProjectClientContract
{
    /**
     * Project linked to this client.
     * 
     * @return ProjectContract
     */
    public function getProject(): ProjectContract;

    /**
     * Setting project linked to this client.
     * 
     * @return ProjectClientContract
     */
    public function setProject(ProjectContract $project): ProjectClientContract;

    /**
     * Getting package version used by this project.
     * 
     * @return string|null null if error occurs.
     */
    public function getPackageVersion(): ?string;

    /**
     * Construct project client based on given project.
     * 
     * @param ProjectContract $project
     * @return ProjectClientContract
     */
    public static function forProject(ProjectContract $project): ProjectClientContract;
}