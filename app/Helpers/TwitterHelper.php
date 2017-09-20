<?php
/**
 * Binds Twitter API and cache together. It uses the Twitter Contract to define what
 * methods are needed from the Twitter API.
 */
namespace App\Helpers;

use App\Contracts\TwitterHelperContract;
use Illuminate\Contracts\Cache\Repository as Cache;
use Thujohn\Twitter\Twitter;

class TwitterHelper implements TwitterHelperContract
{
    /** @var Cache Cache driver */
    protected $cache;

    /** @var Twitter Twitter API */
    protected $twitter;

    /** @var int Default TTL of two hours */
    protected $ttl = 7200;

    /**
     * TwitterHelper constructor.
     *
     * @param Twitter $twitter
     * @param Cache $cache
     */
    public function __construct(Twitter $twitter, Cache $cache) {
        $this->twitter = $twitter;
        $this->cache = $cache;
    }

    /**
     * Returns a tweet ID by URl. Returns 0 if it fails.
     *
     * @param string $url
     * @return int
     */
    public function getTweetIdByUrl(string $url) {
        $data = parse_url($url, PHP_URL_PATH);
        if ($data !== false) {
            $parts = explode("/", $data);
            if (is_array($parts) && count($parts)) {
                foreach($parts as $key => $val) {
                    if (strtolower($val) == "status")
                        $id = (int)$parts[$key+1];
                }

                if (isset($id))
                    return $id;
            }
        }

        return 0;
    }

    /**
     * Get list of user id's who retweedet the tweet id
     * @param int $id   Tweet ID
     * @return array
     */
    public function getRetweetersByTweetId(int $id) {
        $cacheKey = "twitter:retweeters:".$id;
        if ($this->cache->has($cacheKey))
            return $this->cache->get($cacheKey);

        $cursor = -1;
        $list = array();

        do {
            $obj = $this->twitter->getRters(array(
                'id' => $id,
                'cursor' => $cursor,
            ));

            $list = array_merge($list, $obj->ids);
            $cursor = $obj->next_cursor;
        } while ($cursor > 0);

        $this->cache->put($cacheKey, $list, $this->ttl);

        return $list;
    }

    /**
     * Returns a collection of user IDs for every user following the specified user.
     * @param int $userId
     * @return array
     */
    public function getFollowerIdsByUserId(int $userId) {
        $cacheKey = "twitter:flw_by_uid:".$userId;
        if ($this->cache->has($cacheKey))
            return $this->cache->get($cacheKey);

        $list = array();
        $cursor = -1;
        do {
            $obj = $this->twitter->getFollowersIds(array(
                'user_id' => $userId,
                'cursor' => $cursor
            ));

            $list = array_merge($list, $obj->ids);
            $cursor = $obj->next_cursor;
        } while ($cursor > 0);

        $this->cache->put($cacheKey, $list, $this->ttl);

        return $list;
    }

    /**
     * Get list of userId's and it's corresponding followers
     *
     * @param array $userList
     * @return array
     */
    public function getNrOfFollowerByUserList(array $userList) {
        $followerList = array();

        // Get followers for each user
        foreach($userList as $userId) {
            $followerList[$userId] = count($this->getFollowerIdsByUserId((int)$userId));
        }
        return $followerList;
    }

    /**
     * Find out what the range of a tweet is by URL. This function is a wrapper
     * to collect all the statistics for a specific tweet.
     *
     * @param $url
     * @return array
     */
    public function getRangeStatisticsByUrl($url) {
        $tweetId = $this->getTweetIdByUrl($url);
        if (!$tweetId)
            return false;

        $cacheKey = "twitter:stats:".$tweetId;
        if ($this->cache->has($cacheKey)) {
            $data = $this->cache->get($cacheKey);
            return $data;
        }

        // Retrieve user list from twitter
        $userList = $this->getRetweetersByTweetId($tweetId);
        if (!is_array($userList) || !count($userList))
            return false;

        $stats = $this->getNrOfFollowerByUserList($userList);

        $result['nrOfRetweets'] = count($userList);
        $result['totalReach'] = array_sum($stats);

        // Calculate top 10 for cool charts
        arsort($stats);
        $topList = array_slice($stats, 0, 9, true);
        $result['topList'] = $topList;
        $result['topListUsers'] = $this->getUsersLookup(array_keys($topList));


        $this->cache->put($cacheKey, $result, $this->ttl);

        return $result;
    }

    /**
     * Get a list of user data by an array of users
     *
     * @param array $userList
     * @return array
     */
    public function getUsersLookup(array $userList) {
        sort($userList);
        $hash = md5(serialize($userList));
        $cacheKey = "twitter:userlist:".$hash;
        if ($this->cache->has($cacheKey))
            return $this->cache->get($cacheKey);

        $list = $this->twitter->getUsersLookup(array(
            'user_id' => implode(",", $userList),
        ));

        $keyedList = array();
        foreach($list as $user) {
            $keyedList[(int)$user->id] = $user;
        }

        $this->cache->put($cacheKey, $keyedList, $this->ttl);
        return $keyedList;
    }
}
