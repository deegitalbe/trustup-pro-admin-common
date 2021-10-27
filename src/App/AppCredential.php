<?php
namespace Deegitalbe\TrustupProAdminCommon\App;

use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Henrotaym\LaravelApiClient\Contracts\RequestContract;
use Henrotaym\LaravelApiClient\Contracts\CredentialContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\ServerAuthorization\Credential\AuthorizedServerCredential;

class AppCredential extends AuthorizedServerCredential
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
        parent::prepare($request);
        $request->setBaseUrl($this->app->getUrl());
    }
}