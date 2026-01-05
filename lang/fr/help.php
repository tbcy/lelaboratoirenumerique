<?php

return [
    'social_connection' => [
        'name' => 'Ex: "Mon compte X principal", "LinkedIn entreprise"',
        'x_api_key' => 'Clé API de votre application X',
        'x_api_secret' => 'Secret API de votre application X',
        'x_access_token' => 'Token d\'accès utilisateur',
        'x_access_token_secret' => 'Secret du token d\'accès',
        'linkedin_client_id' => 'Client ID de votre app LinkedIn',
        'linkedin_client_secret' => 'Client Secret de votre app LinkedIn',
        'linkedin_access_token' => 'Token OAuth 2.0 (optionnel si vous gérez le flow)',
        'linkedin_redirect_uri' => 'URL de callback OAuth (ex: https://votre-app.com/oauth/linkedin)',
        'instagram_access_token' => 'Token long-lived (60 jours) via Graph API Explorer',
        'instagram_business_account_id' => 'ID du compte Instagram Business lié à votre Page Facebook',
        'facebook_access_token' => 'Token d\'accès de votre Page Facebook',
        'facebook_page_id' => 'ID de votre Page Facebook',
        'select_platform' => 'Les champs de credentials apparaîtront une fois la plateforme sélectionnée.',
    ],

    'social_post' => [
        'content_max_chars' => '/:max caractères',
    ],

    'catalog_item' => [
        'inactive_note' => 'Les articles inactifs n\'apparaissent pas dans les devis/factures',
        'default_quantity' => 'Quantité pré-remplie lors de l\'ajout de cet article à un devis ou une facture',
    ],

    'time_entry' => [
        'end_time_empty' => 'Laisser vide pour timer actif',
    ],

    'task' => [
        'catalog_item_required' => 'Requis pour démarrer un timer - doit être un taux horaire',
    ],
];
