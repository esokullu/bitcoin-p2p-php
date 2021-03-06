<?php

require "../vendor/autoload.php";


use BitWasp\Bitcoin\Networking\DnsSeeds\MainNetDnsSeeds;
use BitWasp\Bitcoin\Networking\Messages\Inv;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Connector;
use BitWasp\Bitcoin\Networking\Peer\Locator;
use BitWasp\Bitcoin\Networking\Peer\Manager;
use BitWasp\Bitcoin\Networking\Peer\Peer;

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
$dns = $factory->getDns();
$msgs = $factory->getMessages();

$locator = new Locator(new MainNetDnsSeeds(), $dns);
$params = new ConnectionParams();
$params->requestTxRelay();

$connector = new Connector($msgs, $params, $loop, $dns);
$manager = new Manager($connector);

$manager
    ->connectNextPeer($locator)
    ->then(function (Peer $peer) {
        $peer->on('inv', function (Peer $peer, Inv $inv) {
            $blocks = 0;
            $txs = 0;
            foreach ($inv->getItems() as $inventory) {
                if ($inventory->isBlock()) {
                    $blocks++;
                } elseif ($inventory->isTx()) {
                    $txs++;
                }
            }

            echo " Inv packet: ".$blocks." blocks and ". $txs . " txs\n";
        });
    });

$loop->run();