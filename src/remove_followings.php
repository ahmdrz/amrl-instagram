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
    if (!$r->getMoreAvailable())
        break;
    foreach($r->getItems() as $item) {
        print($item->getMedia()->getLink());
    }
    $max_id = $r->getNextMaxId();
}

$rankToken = \InstagramAPI\Signatures::generateUUID();
$response = $ig->people->getSelfFollowing($rankToken);

$excludeList = array(
    "mrl_atwork",
    "mrl_humanoid",
    "mrl_uav",
    "mrl_middle",
    "mrl_middlesize",
    "mrlspl",
    "mrlathomelab",
    "qiau.ac",
    "src.syntech",
    "wevolverapp",
    "neuroboticslab",
    "spqrteam",
    "robotics_weekends",
);

foreach ($response->getUsers() as $user) {
    $username = $user->getUsername();
    if (!in_array($username, $excludeList)) {
        printf("unfollowing %s ...\n", $username);
        try {
            $ig->people->unfollow($user->getPk());
            sleep(rand(20, 50));
        } catch (\Exception $e) {
            printf("couldn't unfollow %s!\n", $username);
        }
    }
}
