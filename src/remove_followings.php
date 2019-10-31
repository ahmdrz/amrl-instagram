<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ .'/../config.php';

$ig = new \InstagramAPI\Instagram();
try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: ' . $e->getMessage() . "\n";
    exit(0);
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
            sleep(rand(10, 50));
        } catch (\Exception $e) {
            printf("couldn't unfollow %s!\n", $username);
        }
    }
}
