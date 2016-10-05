<?php

require_once 'vendor/autoload.php';

use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class PubSub implements MessageComponentInterface
{
    private $clients;

    private $subscribers;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
        $this->subscribers = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
    }

    public function onClose(ConnectionInterface $conn)
    {
        if ($this->clients->contains($conn)) {
            $this->clients->detach($conn);
        }
    }

    public function onMessage(ConnectionInterface $from, $message)
    {
        if ($msg = json_decode($message)) {
            switch ($msg->type) {
                case 'publish':
                    $this->handlePush($from, $msg);
                    break;
                case 'subscribe':
                    $this->handleSubscribe($from, $msg);
                    break;
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        if ($this->clients->contains($conn)) {
            $this->clients->detach($conn);
        }
    }

    public function handlePush(ConnectionInterface $client, $msg)
    {
        $event = $msg->event;
        $enviados = new \SplObjectStorage();

        foreach ($this->subscribers as $subEvent => $subscribers) {
            if ($event == $subEvent) {
                foreach ($subscribers as $sub) {
                    if ($enviados->contains($sub)) {
                        continue;
                    }

                    $enviados->attach($sub);
                    $sub->send(json_encode($msg));
                }
            }
        }
    }

    public function handleSubscribe(ConnectionInterface $client, $msg)
    {
        $event = $msg->event;
        if (!isset($this->subscribers[$event])) {
            $this->subscribers[$event] = new \SplObjectStorage();
        }

        if (!$this->subscribers[$event]->contains($client)) {
            $this->subscribers[$event]->attach($client);
        }
    }
}

$ws = new WsServer(new PubSub());
$ws->disableVersion(0);

$server = IoServer::factory(new HttpServer($ws), 8181);
$server->run();