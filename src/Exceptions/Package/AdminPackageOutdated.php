<?php
namespace Deegitalbe\TrustupProAdminCommon\Exceptions\Package;

use Exception;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;

class AdminPackageOutdated extends Exception
{
    /**
     * New package version available.
     * 
     * @var string
     */
    protected $new_version;

    /**
     * Construction exception with dedicated message.
     * 
     * @return self
     */
    public static function getException(): self
    {
        return new self("Package " . Package::prefix(). " is outdated.");
    }

    /**
     * Setting up new version available.
     * 
     * @param string $version
     * @return self
     */
    public function setNewVersion(string $new_version): self
    {
        $this->new_version = $new_version;

        return $this;
    }

    /**
     * Exception context.
     * 
     * @return array
     */
    public function context()
    {
        return [
            'actual_version' => Package::version(),
            'new_version' => $this->new_version
        ];
    }
}