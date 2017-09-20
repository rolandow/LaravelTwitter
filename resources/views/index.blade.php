@extends('layout')

@section('content')
    <form action="/analyseTweet" method="get">
        <div class="input-group">
            <input type="text" name="t" class="form-control" placeholder="URL to Tweet..." aria-label="URL to Tweet...">
            <span class="input-group-btn">
                <button class="btn btn-secondary" type="submit">Go!</button>
            </span>
        </div>
        <small>e.g. https://twitter.com/nhdagblad/status/674114712766824448</small>
    </form>
@endsection