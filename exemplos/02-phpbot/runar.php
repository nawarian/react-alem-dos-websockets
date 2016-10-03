<?php

require_once dirname(__FILE__).'/vendor/autoload.php';

use PHPBot\Keyboard\Keys;
use PHPBot\Pointer\MouseButtons;

$runa = $argv[1];
$vezes = $argv[2];

$loop = React\EventLoop\Factory::create();
$dm = PHPBot\DesktopManager\Factory::create($loop);

$runar = $dm->createCommandPipeline(
    $dm->wait(.5),
    $dm->keyboard()->sendKey(Keys::ENTER()),
    $dm->wait(.5),
    $dm->keyboard()->type($runa),
    $dm->wait(.5),
    $dm->keyboard()->sendKey(Keys::ENTER()),
    $dm->wait(2)
);

$runs = 0;
$loop->addPeriodicTimer(3, function ($timer) use ($runar, $vezes, &$runs) {
    if ($runs == $vezes) {
        return $timer->cancel();
    }

    $runar->start();
    $runs++;
});

$loop->run();