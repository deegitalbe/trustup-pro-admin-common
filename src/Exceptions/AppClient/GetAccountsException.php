<?php
namespace Deegitalbe\TrustupProAdminCommon\Exceptions\AppClient;

use Illuminate\Contracts\Support\Arrayable;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Henrotaym\LaravelApiClient\Exceptions\RequestRelatedException;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;

class GetAccountsException extends RequestRelatedException
{
    /**
     * Exception message.
     * 
     * @var string
     */
    protected $message = "Request fetching accounts failed.";

    /**
     * Professional linked to request..
     * 
     * @var ProfessionalContract
     */
    protected $professional;

    /**
     * Âpplication linked to request
     * 
     * @var AppContract
     */
    protected $app;

    /**
     * Setting linked request
     * 
     * @param RequestContract $request
     * @return self
     */
    public function setProfessional(ProfessionalContract $professional): self
    {
        $this->professional = $professional;

        return $this;
    }

    /**
     * Setting linked request
     * 
     * @param RequestContract $request
     * @return self
     */
    public function setApp(AppContract $app): self
    {
        $this->app = $app;

        return $this;
    }

    /**
     * Exception additional context.
     * 
     * @return array
     */
    public function additionalContext(): array
    {
        return [
            'app' => $this->app->toArray(),
            'professional' => $this->professional->toArray(),
        ];
    }
}