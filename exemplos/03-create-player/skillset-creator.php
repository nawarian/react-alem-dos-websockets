<?php

require __DIR__ . '/vendor/autoload.php';

use React\EventLoop\Factory;

$db = new \PDO('sqlite:player.db');

$loop = Factory::create();
\Ratchet\Client\connect('ws://127.0.0.1:8181', [], [], $loop)
->then(
    function($conn) use ($loop, $db) {
        // Ao conectar-se, inscreva-se para receber mensagens de criação de jogadores
        $conn->send(json_encode([
            'type' => 'subscribe',
            'event' => 'player-created'
        ]));

        $conn->on('message', function($msg, $ws) use ($conn, $loop, $db) {
            $mensagem = json_decode((string) $msg);

            $sql =  sprintf(
                        "INSERT INTO skillset (player_id) VALUES ('%s')",
                        $mensagem->id
                    );

            $db->exec($sql);

            $loop->addTimer(.5, function () use ($conn, $db, $mensagem) {
                $conn->send(json_encode([
                    'type' => 'publish',
                    'event' => 'skillset-created'
                ]));
            });
        });

    },
    function ($e) {
        echo "Could not connect: {$e->getMessage()}\n";
    }
);