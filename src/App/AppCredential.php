<?php
namespace Deegitalbe\TrustupProAdminCommon\App;

use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Henrotaym\LaravelApiClient\Contracts\RequestContract;
use Henrotaym\LaravelApiClient\Contracts\CredentialContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;

class AppCredential implements CredentialContract
{
    /**
     * Application linked to credential.
     * 
     * @var AppContract
     */
    protected $app;

    /**
     * This class should be instanciated with static methods.
     */
    public function __construct(AppContract $app)
    {
        $this->app = $app;
    }

    /**
     * Construct class based on given app.
     * 
     * @param AppContract $app
     * @return self
     */
    public static function forApp(AppContract $app): self
    {
       return app()->make(self::class, ['app' => $app]);
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
            ->setBaseUrl($this->app->getUrl());
    }
}