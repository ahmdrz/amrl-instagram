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

$rankToken = \InstagramAPI\Signatures::generateUUID();
$tags = $ig->hashtag->search('robotics');
foreach ($tags->getResults() as $tag) {
    $items = $ig->hashtag->getFeed($tag->getName(), $rankToken);
    foreach ($items->getItems() as $item) {
        if (!$item->isHasLiked()) {
            printf("sending like request to %s ...\n", $item->getItemUrl());
            $ig->media->like($item->getPk(), 0);
            sleep(rand(10, 50));
        }
    }
}
