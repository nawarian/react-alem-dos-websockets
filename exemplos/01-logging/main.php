<?php

require_once dirname(__FILE__).'/vendor/autoload.php';

use React\ChildProcess\Process;

$loop = React\EventLoop\Factory::create();
$stdIn = new React\Stream\Stream(STDIN, $loop);

$db = new \PDO('sqlite:data.db');

$stdIn->on('data', function($input) use ($loop, $db) {
    if (preg_match('/SELECT /', strtoupper($input))) {
        $stmt = $db->query(trim($input));
        $res = $stmt->fetchAll(PDO::FETCH_CLASS);

        if (count($res)) {
            foreach ($res as $item) {
                $props = get_object_vars($item);
                echo '------------------'.PHP_EOL;
                foreach ($props as $prop => $val) {
                    echo sprintf('--- [%s] => %s'.PHP_EOL, $prop, $val);
                }
                echo '------------------'.PHP_EOL;
            }
        }
    } else if ($stmt = $db->prepare($input)) {
        $stmt->execute();
    }
    $comando = "php log.php queries.log '{$input}'";
    (new Process($comando))->start($loop);
});

$loop->run();