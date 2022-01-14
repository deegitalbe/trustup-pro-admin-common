<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Query;

use App\Models\App;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Collection;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Henrotaym\LaravelModelQueries\Queries\Abstracts\AbstractQuery;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query\AppQueryContract;

/**
 * Query used to retrieve apps.
 */
class AppQuery extends AbstractQuery implements AppQueryContract
{
    /**
     * Model linked to this query.
     * 
     * @return string
     */
    public function getModel(): string
    {
        return Package::app();
    }

    /**
     * Limiting app to those available.
     * 
     * @return AppQueryContract
     */
    public function available(): AppQueryContract
    {
        $this->getQuery()->whereAvailable();

        return $this;
    }

    /**
     * Limiting app to those available.
     * 
     * @return AppQueryContract
     */
    public function notAvailable(): AppQueryContract
    {
        $this->getQuery()->whereNotAvailable();

        return $this;
    }
    
    /**
     * Limiting app to those not having given key.
     * 
     * @param string $app_key
     * @return AppQueryContract
     */
    public function whereKeyIsNot(string $app_key): AppQueryContract
    {
        $this->getQuery()->whereKeyIsNot($app_key);

        return $this;
    }

    /**
     * Limiting app to those having given key.
     * 
     * @param string $app_key
     * @return AppQueryContract
     */
    public function whereKeyIs(string $app_key): AppQueryContract
    {
        $this->getQuery()->whereAppKey($app_key);

        return $this;
    }

    /**
     * Ordering apps respecting order column.
     * 
     * @param string $app_key
     * @return AppQueryContract
     */
    public function ordered(): AppQueryContract
    {
        $this->getQuery()->byOrder();

        return $this;
    }

    /**
     * Getting request validator.
     * 
     * @param Request $request
     * @return ValidatorContract
     */
    protected function getRequestValidator(Request $request): ValidatorContract
    {
        return Validator::make($request->all(), [
            'is' => 'nullable|string',
            'not' => 'nullable|string',
            'available' => 'nullable|boolean'
        ]);
    }

    /**
     * Limiting apps to those matching given request.
     * 
     * @param Request $request
     * @return AppQueryContract
     */
    public function matchingRequest(Request $request): AppQueryContract
    {
        $validator = $this->getRequestValidator($request);
        
        if ($validator->fails()):
            $this->getQuery()->where('id', '<', 0);
            return $this;
        endif;

        $data = $validator->validated();
        $not_key = $data['not'] ?? null;
        $is_key = $data['is'] ?? null;
        $available = $data['available'] ?? null;

        // transforming available to boolean if not null
        if ($available):
            $available = intval($available) === 1;
        endif;
        
        // if there is a not parameter
        if ($not_key):
            $this->whereKeyIsNot($not_key);
        endif;

        // if there is a "is" parameter
        if ($is_key):
            $this->whereKeyIs($is_key);
        endif;

        // if there is an available parameter set to true
        if ($available):
            $this->available();
        endif;

        // if there is an available parameter set to false
        if ($available === false):
            $this->notAvailable();
        endif;

        return $this;
    }
}