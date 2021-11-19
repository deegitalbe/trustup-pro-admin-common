<?php
namespace Deegitalbe\TrustupProAdminCommon\App;

use stdClass;
use App\Models\Professional;
use Illuminate\Support\Collection;
use Deegitalbe\TrustupProAdminCommon\App\AppCredential;
use Henrotaym\LaravelApiClient\Contracts\ClientContract;
use Henrotaym\LaravelApiClient\Contracts\RequestContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\App\AppClientContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Exceptions\AppClient\GetAccountsException;
use Deegitalbe\TrustupProAdminCommon\Exceptions\AppClient\UpdateAccountException;
use Deegitalbe\TrustupProAdminCommon\Exceptions\AppClient\GetAllAccountsException;

/**
 * API client able to communicate with application
 * 
 */
class AppClient implements AppClientContract
{
    /**
     * Application api client.
     * 
     * @var ClientContract
     */
    protected $client;

    /**
     * This class should be instanciated with static methods.
     */
    public function __construct(ClientContract $client)
    {
        $this->client = $client;
    }

    /**
     * Updating application account.
     * 
     * @param AccountContract $account
     * @return bool success status.
     */
    public function updateAccount(AccountContract $account): ?stdClass
    {
        $request = app()->make(RequestContract::class)
            ->setVerb('PUT')
            ->setUrl("common-package/webhooks/accounts/{$account->getUuid()}")
            ->addData($this->accountToAttributes($account));
        
        $response = $this->client->start($request);

        if (!$response->ok()):
            $error = new UpdateAccountException;
            report(
                $error
                    ->setRequest($request)
                    ->setResponse($response)
                    ->setProfessional($account->getProfessional())
                    ->setApp($this->getApp())
            );
            return null;
        endif;

        return $response->get()->data;
    }

    /**
     * Getting raw professional accounts.
     * 
     * @param Professional $professional
     * @return Collection|null
     */
    public function getProfessionalAccounts($professional): ?Collection
    {
        $request = $this->newAccountsRequest()->addQuery(["authorization_key" => $professional->authorization_key]);

        $response = $this->client->start($request);

        // Request failed
        if (!$response->ok()) {
            $error = new GetAccountsException;
            report(
                $error
                    ->setRequest($request)
                    ->setResponse($response)
                    ->setProfessional($professional)
                    ->setApp($this->getApp())
            );
            return null;
        }

        return collect($response->get()->data);
    }

    /**
     * Getting raw professional account matching given uuid.
     * 
     * @param Professional $professional
     * @return stdClass|null
     */
    public function getProfessionalAccount($professional, string $account_uuid): ?stdClass
    {
        return optional($this->getProfessionalAccounts())->first(function($account) use ($account_uuid) {
            return $account->uuid === $account_uuid;
        });
    }

    /**
     * Getting all accounts.
     * 
     * @return Collection|null
     */
    public function getAllAccounts(): ?Collection
    {
        $request = $this->newAccountsRequest();
        $response = $this->client->start($request);

        // Request failed
        if (!$response->ok()) {
            $error = new GetAllAccountsException;
            report(
                $error
                    ->setRequest($request)
                    ->setResponse($response)
            );
            return null;
        }

        return collect($response->get()->data);
    }

    /**
     * Creating a new account index request.
     * 
     * @return RequestContract
     */
    protected function newAccountsRequest(): RequestContract
    {
        return app()->make(RequestContract::class)
            ->setVerb('GET')
            ->setUrl('common-package/accounts');
    }

    /**
     * Getting app linked to this client.
     * 
     * @return AppContract
     */
    public function getApp(): AppContract
    {
        return $this->app;
    }

    /**
     * Linking app to client.
     * 
     * @param AppContract $app
     * @return self
     */
    public function setApp(AppContract $app): self
    {
        $this->app = $app;

        return $this;
    }

    /**
     * Transforming given account to attributes comprehensible for application.
     */
    protected function accountToAttributes(AccountContract $account): array
    {
        return [
            'uuid' => $account->getUuid(),
            'authorization_key' => $account->getProfessional()->authorization_key,
            'chargebee_subscription_id' => optional($account->getChargebee())->getId(),
            'chargebee_subscription_status' => optional($account->getChargebee())->getStatus()
        ];
    }

    /**
     * Construct class based on given app.
     * 
     * @param AppContract $app
     * @return self
     */
    public static function forApp(AppContract $app): AppClientContract
    {
        $client = app()->make(ClientContract::class)
            ->setCredential(AppCredential::forApp($app));
        
        return app()->make(AppClientContract::class, ['client' => $client])
            ->setApp($app);
    }
}