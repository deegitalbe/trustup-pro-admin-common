<?php
namespace Deegitalbe\TrustupProAdminCommon\Models;

use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryUserContract;
use Deegitalbe\TrustupProAdminCommon\Models\_Abstract\AdminModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User who successfully accessed to account.
 * 
 */
class AccountAccessEntryUser extends AdminModel implements AccountAccessEntryUserContract
{
    protected $fillable = [
        'first_name',
        'last_name',
        'user_id',
        'avatar'
    ];

    public function accountAccessEntry(): BelongsTo
    {
        return $this->belongsTo(AccountAccessEntry::class);
    }

    /**
     * User full name.
     * 
     * @return string
     */
    public function getFullName(): string
    {
        return ucfirst($this->getFirstName()) . ($this->last_name ? " {$this->getLastName()}" : "");
    }

    /**
     * User first name.
     * 
     * @return string
     */
    public function getFirstName(): string
    {
        return ucfirst($this->first_name);
    }

    /**
     * User last name.
     * 
     * @return string
     */
    public function getLastName(): string
    {
        return ucfirst($this->last_name);
    }

    /**
     * User id.
     * 
     * @return int
     */
    public function getId(): int
    {
        return $this->user_id;
    }

     /**
     * User avatar url (as base64).
     * 
     * @return string
     */
    public function getAvatar(): string
    {
        return $this->avatar;
    }

    /**
     * Setting user first name.
     * 
     * @param string $first_name
     * @return self
     */
    public function setFirstName(string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;        
    }

    /**
     * Setting user last name.
     * 
     * @param string $last_name
     * @return self
     */
    public function setLastName(string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }

    /**
     * Setting user id.
     * 
     * @param string $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->user_id = $id;

        return $this;
    }

    /**
     * Setting user avatar.
     * 
     * @param string $avatar
     * @return self
     */
    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Access entry linked to this user.
     * 
     * @return AccountAccessEntryContract
     */
    public function getAccountAccessEntry(): AccountAccessEntryContract
    {
        return $this->accountAccessEntry;
    }
}