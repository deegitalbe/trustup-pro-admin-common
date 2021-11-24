<?php
namespace Deegitalbe\TrustupProAdminCommon\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class App extends JsonResource
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
            "id" => $this->getId(),
            "key" => $this->getKey(),
            "url" => $this->getUrl(),
            "name" => $this->getName(),
            "description" => $this->getDescription(),
            "available" => $this->getAvailable(),
            "translated" => $this->getTranslated(),
            "plans" => Plan::collection($this->getPlans()),
            "paid" => $this->getPaid()
        ];
    }
}