<?php

return [
    'access_token_expiration_minutes' => (int) env('ACCESS_TOKEN_EXPIRATION_MINUTES', 10080),
    'game_ticket_expiration_seconds' => (int) env('GAME_TICKET_EXPIRATION_SECONDS', 60),
    'game_server_token' => env('GAME_SERVER_TOKEN'),
];
