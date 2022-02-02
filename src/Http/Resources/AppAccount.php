<?php
namespace Deegitalbe\TrustupProAdminCommon\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
        return [
            'authorization_key' => $this->getProfessional()->authorization_key,
            'name' => $this->getProfessional()->company,
            'vat_number' => $this->getProfessional()->vat_number,
            'uuid' => $this->getUuid(),
            'chargebee_subscription_id' => $this->when($this->hasChargebee(), $this->getChargebee()->getId()),
            'chargebee_subscription_status' => $this->when($this->hasChargebee(), $this->getChargebee()->getStatus())
        ];
    }
}