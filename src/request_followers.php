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

$requestedUsers = array();
if (file_exists('requested_users.json')) {
    $string = file_get_contents('requested_users.json');
    $requestedUsers = json_decode($string);
}

print("fetching self following from Instagram ...\n");
$rankToken = \InstagramAPI\Signatures::generateUUID();
$response = $ig->people->getSelfFollowing($rankToken);

$counter = 0;
$maxCounter = 100;

$users = $response->getUsers();
shuffle($users);

foreach ($users as $user) {
    $userID = $user->getPk();
    printf("requesting list of followers from %s ...\n", $user->getUsername());

    $followers = $ig->people->getFollowers($userID, $rankToken);
    shuffle($followers);

    foreach ($followers->getUsers() as $follower) {
        printf("checking candidate %s ...\n", $follower->getUsername());
        $friendship = $ig->people->getFriendship($follower->getPk());
        if ($friendship->isOutgoingRequest()) continue;
        if ($friendship->isFollowing()) continue;
        if ($friendship->isFollowedBy()) continue;

        printf("requesting follow to %s ...\n", $follower->getUsername());

        try {
            $ig->people->follow($follower->getPk());
            array_push($requestedUsers, $follower->getPk());
        } catch (\Exception $e) {
            printf("couldn't follow %s!\n", $follower->getUsername());
        }

        sleep(rand(15, 30));
        $counter += 1;
        if ($counter > $maxCounter) {
            $fp = fopen('requested_users.json', 'w');
            fwrite($fp, json_encode($requestedUsers));
            fclose($fp);
            break;
        }
    }
}
