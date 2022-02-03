<?php
namespace Deegitalbe\TrustupProAdminCommon\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;

/**
 * Representing an account transformed for app environment.
 * 
 * @see \Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract
 */
class AppAccount extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var AccountContract*/
        $resource = $this->resource;

        return [
            'authorization_key' => $resource->getProfessional()->getAuthorizationKey(),
            'name' => $resource->getProfessional()->getCompanyName(),
            'vat_number' => $resource->getProfessional()->getVatNumber(),
            'uuid' => $resource->getUuid(),
            'chargebee_subscription_id' => $this->when($resource->hasChargebee(), $resource->getChargebee()->getId()),
            'chargebee_subscription_status' => $this->when($resource->hasChargebee(), $resource->getChargebee()->getStatus())
        ];
    }
}