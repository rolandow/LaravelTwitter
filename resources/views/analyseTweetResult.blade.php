@extends('layout')

@section('content')
    <form action="/analyseTweet" method="get">
        <div class="input-group">
            <input type="text" name="t" class="form-control" placeholder="URL to Tweet..." value="{{ app('request')->input('t') }}" aria-label="URL to Tweet...">
            <span class="input-group-btn">
                <button class="btn btn-secondary" type="submit">Go!</button>
            </span>
        </div>
        <small>e.g. https://twitter.com/NUnl/status/910330576388988928</small>
    </form>

    <br/><br/>
    This tweet was retweeted by {{ $stats['nrOfRetweets'] }} users. They have a total number of {{ $stats['totalReach'] }} followers.
    <br/><br/>


    <div id="analyseTweetResult" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>



@endsection


@section('javascripts')
    @parent

    <script>
        jQuery(document).ready(function( $ ) {
            Highcharts.chart('analyseTweetResult', {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: 'Top 10 retweeters'
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.y:.0f}</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                            style: {
                                color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                            }
                        }
                    }
                },
                series: [{
                    name: 'Followers',
                    colorByPoint: true,
                    data: <?php echo json_encode($pieChartData); ?>
                }]
            });
        });
    </script>
@endsection
