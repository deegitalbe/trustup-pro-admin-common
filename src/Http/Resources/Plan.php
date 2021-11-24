<?php
namespace Deegitalbe\TrustupProAdminCommon\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Deegitalbe\TrustupProAdminCommon\Http\Resources\Plan;

class Plan extends JsonResource
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
            "name" => $this->getName(),
            "trial_duration" => $this->getTrialDuration(),
            "is_default" => $this->isDefault()
        ];
    }
}