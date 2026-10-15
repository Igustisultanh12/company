<?php

return [
    // Rute yang diekspos ke pengunjung publik (tamu). Rute panel internal tidak dibocorkan.
    'groups' => [
        'guest' => [
            'home', 'about', 'services.*', 'pricing', 'portfolio.*', 'blog.*', 'contact', 'contact.*',
            'order.*', 'track', 'track.*', 'verify.*', 'login', 'login.*', 'password.*', 'sitemap',
        ],
    ],
    'skip-route-function' => true,
];
