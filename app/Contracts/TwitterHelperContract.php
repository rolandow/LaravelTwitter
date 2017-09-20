<?php
namespace App\Contracts;

Interface TwitterHelperContract
{
    public function getTweetIdByUrl(string $url);

    public function getRetweetersByTweetId(int $id);

    public function getFollowerIdsByUserId(int $userId);

    public function getNrOfFollowerByUserList(array $userList);

    public function getRangeStatisticsByUrl($url);



}