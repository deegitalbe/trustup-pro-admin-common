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
         * Account model.
         */
        'account' => \Deegitalbe\TrustupProAdminCommon\Models\Account::class,

        /**
         * App model.
         */
        'app' => \Deegitalbe\TrustupProAdminCommon\Models\App::class,

        /**
         * Plan model.
         */
        'plan' => \Deegitalbe\TrustupProAdminCommon\Models\Plan::class,

        /**
         * Account access entry model.
         */
        'account_access_entry' => \Deegitalbe\TrustupProAdminCommon\Models\AccountAccessEntry::class,

        /**
         * Account access entry user model.
         */
        'account_access_entry_user' => \Deegitalbe\TrustupProAdminCommon\Models\AccountAccessEntryUser::class,

        /**
         * Account chargebee status.
         */
        'account_chargebee' => \Deegitalbe\TrustupProAdminCommon\Models\AccountChargebee::class,

    ],
    
    /** 
     * Connections used for models.
     */
    'connections' => [
        /**
         * Admin DB connection name.
         */
        'admin' => env("DB_ADMIN_CONNECTION", "admin"),

        /**
         * Trustup DB connection name.
         */
        'trustup' => env("DB_TRUSTUP_CONNECTION", "trustup"),
    ],

    /**
     * Projects using this package (only their url).
     */
    'projects' => [
        env('TRUSTUP_ADMIN_PACKAGE_ADMIN_URL'),
        env('TRUSTUP_ADMIN_PACKAGE_TRUSTUP_PRO_URL'),
    ]
];