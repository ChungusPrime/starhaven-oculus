<?php

return [
    'access_token_expiration_minutes' => (int) env('ACCESS_TOKEN_EXPIRATION_MINUTES', 10080),

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
