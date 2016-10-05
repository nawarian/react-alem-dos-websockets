<?php

require __DIR__ . '/vendor/autoload.php';

use React\EventLoop\Factory;

$loop = Factory::create();
\Ratchet\Client\connect('ws://127.0.0.1:8181', [], [], $loop)
->then(
    function($conn) use ($loop) {
        // Ao conectar-se, inscreva-se para receber mensagens de criação de jogadores
        $conn->send(json_encode([
            'type' => 'subscribe',
            'event' => 'create-player'
        ]));

        $conn->on('message', function($msg, $ws) use ($conn, $loop) {
            $mensagem = json_decode((string) $msg);

            $loop->addTimer(.5, function () use ($conn) {
                $conn->send(json_encode([
                    'type' => 'publish',
                    'event' => 'player-created',
                    'id' => rand(1, 1000)
                ]));
            });
        });

    },
    function ($e) {
        echo "Could not connect: {$e->getMessage()}\n";
    }
);