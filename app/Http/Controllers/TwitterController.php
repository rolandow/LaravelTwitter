<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Contracts\TwitterHelperContract;

class TwitterController extends Controller
{
    /** @var TwitterHelperContract  */
    protected $twitterHelper;

    public function __construct(TwitterHelperContract $twitter) {
        $this->twitterHelper = $twitter;
    }

    public function analyseTweet(Request $request) {
        $url = $request->input("t");
        if (empty($url))
            return false;

        $stats = $this->twitterHelper->getRangeStatisticsByUrl($url);
        $pieChartData = array();
        $other = $stats['totalReach'];
        foreach($stats['topList'] as $userId => $followers) {
            $pieChartData[] = array(
                'name' => $stats['topListUsers'][$userId]->name,
                'y' => $followers,
            );
            $other -= $followers;
        }

        if ($other > 0) {
            $pieChartData[] = array(
                'name' => 'Others',
                'y' => $other,
            );
        }

        return view('analyseTweetResult')->with(array(
            'stats' => $stats,
            'pieChartData' => $pieChartData,
        ));
    }





}