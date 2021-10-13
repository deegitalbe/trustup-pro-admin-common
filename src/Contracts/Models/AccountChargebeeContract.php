<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Models;

use Carbon\Carbon;
use Deegitalbe\TrustupProAdminCommon\Contracts\PersistableContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;

interface AccountChargebeeContract extends PersistableContract
{
    public function getStatus(): string;

    public function setStatus(string $status);

    public function getId(): string;

    public function setId(string $id);

    public function text(): string;

    public function isTrial(): bool;

    public function isActive(): bool;

    public function isCancelled(): bool;
    
    public function isNonRenewing(): bool;

    public function getAccount(): AccountContract;
}