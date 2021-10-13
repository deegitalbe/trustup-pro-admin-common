<?php

return [
    /**
     * Models
     */
    'models' => [

        /**
         * Professional model.
         */
        'professional' => \App\Models\Professional::class,

        /**
         * account model.
         */
        'account' => \Deegitalbe\TrustupProAdminCommon\Models\Account::class,

        /**
         * account model.
         */
        'app' => \Deegitalbe\TrustupProAdminCommon\Models\App::class,

        /**
         * account access entry model.
         */
        'account_access_entry' => \Deegitalbe\TrustupProAdminCommon\Models\AccountAccessEntry::class,

        /**
         * account access entry user model.
         */
        'account_access_entry_user' => \Deegitalbe\TrustupProAdminCommon\Models\AccountAccessEntryUser::class,

        /**
         * account chargebee status.
         */
        'account_chargebee' => \Deegitalbe\TrustupProAdminCommon\Models\AccountChargebee::class,

    ],
    /**
     * Authorizations
     */
    'authorization' => env('TRUSTUP_SERVER_AUTHORIZATION')
];