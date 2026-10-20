<?php

return [
    'access_token_expiration_minutes' => (int) env('ACCESS_TOKEN_EXPIRATION_MINUTES', 10080),

    // Static universe identity and connection details. Runtime availability and
    // player counts are populated by the scheduled universes:refresh command.
    'universes' => [
        ['name' => 'Universe Alpha', 'address' => env('UNIVERSE_ALPHA_URL', 'http://localhost:8090')],
        //['name' => 'Universe Beta', 'address' => env('UNIVERSE_BETA_URL', 'http://localhost:8091')],
        //['name' => 'Universe Gamma', 'address' => env('UNIVERSE_GAMMA_URL', 'http://localhost:8092')],
    ],
    'universe_status_connect_timeout_seconds' => 1,
    'universe_status_timeout_seconds' => 2,
    'universe_status_cache_seconds' => 300,

    // Game tickets are intentionally much shorter-lived than login access tokens.
    // The Phaser client requests one only when the player is ready to connect.
    'game_ticket_expiration_seconds' => (int) env('GAME_TICKET_EXPIRATION_SECONDS', 60),

    // This server-to-server secret protects the internal consume endpoint. It
    // must be configured identically in Oculus and each trusted game server, and
    // must never be shipped to the Phaser client.
    'game_server_token' => env('GAME_SERVER_TOKEN'),
    'admin' => [
        'username' => env('ADMIN_USERNAME'),
        'password' => env('ADMIN_PASSWORD'),
    ],
];
