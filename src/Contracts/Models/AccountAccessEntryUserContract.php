<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Models;

use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\EmbeddableContract;

/**
 * User who successfully accessed to account.
 * 
 */
interface AccountAccessEntryUserContract extends EmbeddableContract
{
    /**
     * Access entry linked to this user.
     * 
     * @return AccountAccessEntryContract
     */
    public function getAccountAccessEntry(): AccountAccessEntryContract;

    /**
     * User full name.
     * 
     * @return string
     */
    public function getFullName(): string;

    /**
     * User first name.
     * 
     * @return string
     */
    public function getFirstName(): string;

    /**
     * User last name.
     * 
     * @return string
     */
    public function getLastName(): string;

    /**
     * User id.
     * 
     * @return int
     */
    public function getId(): int;

     /**
     * User avatar url (as base64).
     * 
     * @return string
     */
    public function getAvatar(): string;

    /**
     * Setting user first name.
     * 
     * @param string $first_name
     */
    public function setFirstName(string $first_name);

    /**
     * Setting user last name.
     * 
     * @param string $last_name
     */
    public function setLastName(string $last_name);

    /**
     * Setting user id.
     * 
     * @param string $id
     */
    public function setId(int $id);

    /**
     * Setting user avatar (base64 url).
     * 
     * @param string $avatar
     */
    public function setAvatar(string $avatar);
}