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
$maxCounter = 50;

$users = $response->getUsers();
shuffle($users);

foreach ($users as $user) {
    $userID = $user->getPk();
    printf("requesting list of followers from %s ...\n", $user->getUsername());

    $followers = $ig->people->getFollowers($userID, $rankToken)->getUsers();
    shuffle($followers);
    if (count($followers) > 30) $followers = array_slice($followers, 0, 30);

    foreach ($followers as $follower) {
        sleep(rand(5, 10));
        printf("checking candidate %s: ", $follower->getUsername());
        $friendship = $ig->people->getFriendship($follower->getPk());
        if ($friendship->isOutgoingRequest()) {
            printf("outgoing requested\n");
            continue;
        }
        if ($friendship->isFollowing()) {
            printf("currently following\n");
            continue;
        }
        if ($friendship->isFollowedBy()) {
            printf("currently followed-by\n");
            continue;
        }
        if (in_array($follower->getUsername(), $requestedUsers)) {
            printf("requested before\n");
            continue;
        }

        printf("sending request\n");

        try {
            $ig->people->follow($follower->getPk());
            array_push($requestedUsers, $follower->getUsername());

            $fp = fopen('requested_users.json', 'w');
            fwrite($fp, json_encode($requestedUsers));
            fclose($fp);
        } catch (\Exception $e) {
            printf("couldn't follow %s!\n", $follower->getUsername());
        }

        sleep(rand(15, 30));
        $counter += 1;
        if ($counter > $maxCounter) exit(0);
    }
}
