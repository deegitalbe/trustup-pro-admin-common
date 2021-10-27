<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query;

use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Query used to retrieve apps.
 */
interface AppQueryContract
{
    /**
     * Limiting app to those available.
     * 
     * @return AppQueryContract
     */
    public function available(): AppQueryContract;

    /**
     * Limiting app to those not available.
     * 
     * @return AppQueryContract
     */
    public function notAvailable(): AppQueryContract;
    
    /**
     * Limiting app to those not having given key.
     * 
     * @param string $app_key
     * @return AppQueryContract
     */
    public function whereKeyIsNot(string $app_key): AppQueryContract;

    /**
     * Limiting apps to those matching given request.
     * 
     * @param Request $request
     * @return AppQueryContract
     */
    public function matchingRequest(Request $request): AppQueryContract;
    
    /**
     * Getting apps.
     * 
     * @return Collection
     */
    public function get(): Collection;

    /**
     * Getting number of apps matching this query.
     * 
     * @return int
     */
    public function count(): int;
}