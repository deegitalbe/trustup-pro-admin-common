# Installation

## Via composer

    composer require deegitalbe/trustup-pro-admin-common

# Configuration

## Publish configuration

You have to publish configuration

    php artisan vendor:publish --provider="Deegitalbe\TrustupProAdminCommon\Providers\TrustupProAdminCommonServiceProvider" --tag="config"

You will then have access to `config/trustup_pro_admin_common.php` that you have to configure properly.

## Default configuration

### Implements professional model interface
Your professional model should implements this interface

    Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract

### Use default professional model trait

You can use this trait in your professional model

    Deegitalbe\TrustupProAdminCommon\Models\Traits\ProfessionalModel

## Custom configuration

### Implements interface

Same step as default configuration step

### Define interface methods yourself

    /**
     * Getting professional id.
     * 
     * @return int
     * 
     */
    public function getId(): int;

    /**
     * Getting professional authorization key.
     * 
     * @return string
     * 
     */
    public function getAuthorizationKey(): string;

    /**
     * Persisting instance.
     * 
     * @param array $options
     */
    public function persist(array $options = []);
