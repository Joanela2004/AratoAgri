<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'register', '*'],
    'allowed_methods' => ['*'],
  'allowed_origins' => [
    'http://localhost:5173',
    'http://192.168.1.222:5173', // Ton frontend React

    // --- POUR FLUTTER ---
    'http://localhost',          // Requis pour Flutter Web et certains émulateurs iOS
    'http://10.0.2.2',           // Adresse spéciale pour l'émulateur Android (qui correspond au localhost de ton PC)
    'file://*',                  // Parfois requis si l'application s'exécute localement en mode natif
],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
