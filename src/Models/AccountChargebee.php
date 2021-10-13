<?php
namespace Deegitalbe\TrustupProAdminCommon\Models;

use Carbon\Carbon;
use Deegitalbe\TrustupProAdminCommon\Models\_Abstract\MongoModel;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountChargebeeContract;

class AccountChargebee extends MongoModel implements AccountChargebeeContract
{
    protected $fillable = [
        'status'
    ];

    public function getStatus(): string
    {
        return $this->status;
    }

    public function text(): string
    {
        if ( $this->isTrial() ) {
            return "Essai";
        }

        if ( $this->isActive() ) {
            return "Actif";
        }
        
        if ( $this->isCancelled() ) {
            return "Annulé";
        }

        if ( $this->isNonRenewing() ) {
            return "Terminé";
        }

        return "";
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id)
    {
        $this->id = $id;

        return $this;
    }

    public function isTrial(): bool
    {
        return $this->status === "in_trial";
    }

    public function isActive(): bool
    {
        return $this->status === "active";
    }

    public function isCancelled(): bool
    {
        return $this->status === "cancelled";
    }

    public function isNonRenewing(): bool
    {
        return $this->status === "non_renewing";
    }

    public function getAccount(): AccountContract
    {
        return $this->account;
    }
}