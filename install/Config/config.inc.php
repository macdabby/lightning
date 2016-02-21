<?php

$conf = [
    'database' => 'mysql:user=user;password=pass;host=localhost;dbname=db',
    'user' => [
        'cookie_domain' => '',
        // Generate a new key by going to the Lightning directory and running
        // ./lightning security generate-aes-key
        // **** THIS KEY IS INCLUDED WITH THE DISTRIBUTION AND IS NOT SECURE ****
        'key' => 'o2sdxfGwHn2YGcQ3Xh2Z8p5y/BP0dHdbtdmU3ATdMwE=',
        'multiple_devices' => false,
        'min_password_length' => 6,
        'requires_confirmation' => false,
    ],
    'session' => [
        'single_ip' => true,
    ],
    'site' => [
        'mail_from' => 'donotreply@Website.com',
        'mail_from_name' => 'My Mailer',
        'name' => 'My Website',
        'domain' => 'Website.com',
        'email_domain' => 'www.Website.com',
        'log' => '../logs/web.log',
        'logtype' => 'stacktrace',
    ],
    'contact' => [
        'subject' => 'Message from Website.com',
        'auto_responder' => 0, // Change to this a message_id of an auto respond.
        'spam_test' => false, // Whether to tell them to immediately look for the auto respond in their junk folder.
        'to' => ['youremail@gmail.com'],
        'cc' => [],
        'bcc' => [],
        'optin' => false,
    ],
    'mailer' => [
        'test' => [],
        'spam_test' => [],
        'spam_test_from' => null,
        'mail_from' => null,
        'mail_from_name' => null,
        'bounce_address' => null,
        'default_template' => null,
        'default_list' => null,
        'mail_template' => null,
        'confirm_message' => null, // Set a message_id here to enable double confirmation.
    ],
    'tracker' => [
        'allow_unencrypted' => true,
        // Generate a new key by going to the Lightning directory and running
        // ./lightning security generate-aes-key
        // **** THIS KEY IS INCLUDED WITH THE DISTRIBUTION AND IS NOT SECURE ****
        'key' => '0Xx+v7xGDanBpTgDoIqwlA==:JPJdzm5ifvePYztVj1ICrQ==',
    ],
    'html_editor' => [
        'editor' => 'text',
        'browser' => '',
    ],
    'imageBrowser' => [
        'containers' => [
            'images' => [
                // Relative to the home directory
                'storage' => 'images',
                // Will be prefixed to an image file. Include the trailing slash.
                'url' => '/images/',
            ],
        ],
        'type' => 'elfinder',
    ],
    'meta_data' => [
        'title' => '',
        'keywords' => '',
        'description' => '.',
    ],
    'google_analytics_id' => '',
    'use_mobile_site' => true,
    'recaptcha' => [
        'public' => '',
        'private' => '',
    ],
    'web_root' => 'http://www.Website.com',
    'daemon' => [
        'max_threads' => 5,
        'log' => '../logs/daemon.log',
    ],
    'cli' => [
        'log' => '../logs/cli.log',
    ],
    'jobs' => [
        'session_cleanup' => [
            'class' => 'Lightning\\Jobs\\UserCleanup',
            'offset' => 7200, // 2 am server time
            'interval' => 86400,
            'max_threads' => 1,
        ],
    ],
    'routes' => [
        'dynamic' => [
            '.*\.html' => 'Lightning\\Pages\\Page',
            '^blog(/.*)?$' => 'Lightning\\Pages\\Blog',
            '.*\.htm' => 'Lightning\\Pages\\Blog',
        ],
        'static' => [
            '' => 'Lightning\\Pages\\Page',
            'user' => 'Lightning\\Pages\\User',
            'contact' => 'Lightning\\Pages\\Contact',
            'page' => 'Lightning\\Pages\\Page',
            'message' => 'Lightning\\Pages\\Message',
            'track' => 'Lightning\\Pages\\Track',
            'landing' => 'Lightning\\Pages\\OptIn',
            'profile' => 'Lightning\\Pages\\Profile',

            // SEO
            'sitemap' => 'Lightning\\Pages\\Sitemap',

            // Admin
            'admin/blog/edit' => 'Lightning\\Pages\\BlogTable',
            'admin/blog/categories' => 'Lightning\\Pages\\BlogCategories',
            'admin/mailing/lists' => 'Lightning\\Pages\\Mailing\\Lists',
            'admin/mailing/messages' => 'Lightning\\Pages\\Mailing\\Messages',
            'admin/mailing/send' => 'Lightning\\Pages\\Mailing\\Send',
            'admin/mailing/stats' => 'Lightning\\Pages\\Mailing\\Stats',
            'admin/mailing/templates' => 'Lightning\\Pages\\Mailing\\Templates',
            'admin/tracker' => 'Lightning\\Pages\\AdminTracker',
            'admin/pages' => 'Lightning\\Pages\\Admin\\Pages',
            'admin/users' => 'Lightning\\Pages\\Admin\\Users',
            'admin/cms' => 'Lightning\\Pages\\Admin\\CMS',
            'admin/rolesdashboard' => 'Lightning\\Pages\\Admin\\RolesDashboard',
            'admin/roles' => 'Lightning\\Pages\\Admin\\Roles',
            'admin/permissions' => 'Lightning\\Pages\\Admin\\Permissions',

            // Image admin
            'elfinder' => 'Lightning\\API\\ElFinder',
            'imageBrowser' => 'Lightning\\Pages\\ImageBrowser',
        ],
        'cli_only' => [
            'bounce' => 'Lightning\\CLI\\BouncedEmail',
            'test' => 'Source\\CLI\\Test',
        ],
    ],
    'language' => 'en_us',
];
