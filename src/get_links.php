<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';

$ig = new \InstagramAPI\Instagram();
try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: ' . $e->getMessage() . "\n";
    exit(0);
}

while (true) {
    $max_id = null;
    $r = $ig->media->getSavedFeed($max_id);
    foreach ($r->getItems() as $item) {
        $feed = $ig->media->getInfo($item->getMedia()->getId());
        printf("https://instagram.com/p/%s\n", $feed->getItems()[0]->getCode());
    }
    if (!$r->getMoreAvailable())
        break;
    $max_id = $r->getNextMaxId();
    sleep(rand(2, 5));
}
