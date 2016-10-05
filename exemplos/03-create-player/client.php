<?php

require __DIR__ . '/vendor/autoload.php';

\Ratchet\Client\connect('ws://127.0.0.1:8181')->then(function($conn) {
    $conn->on('message', function($msg) use ($conn) {
        echo "Received: {$msg}\n";
        $conn->close();
    });

    $conn->send(json_encode([
        'type' => 'publish',
        'event' => 'create-player'
    ]));
}, function ($e) {
    echo "Could not connect: {$e->getMessage()}\n";
});