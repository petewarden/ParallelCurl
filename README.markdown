# ParallelCurl

## Simple usage

    require 'parallelcurl.php';

    $parallel_curl = new ParallelCurl($max_parallel_requests, $curl_options);

    $urls = array("http://github.com/", "http://news.ycombinator.com/");
    foreach ($urls as $url) {
        $parallel_curl->startRequest($url, function($content, $url, $ch) {
            print $content;
        });
    }

    $parallel_curl->finishAllRequests();


## Advanced usage

    require 'parallelcurl.php';

    function on_request_done($content, $url, $ch, $param) {
    {
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);    
        if ($httpcode !== 200) {
            print "Fetch error $httpcode for '$url'\n";
            return;
        }
        print $content;
    }

    $curl_options = array(
        CURLOPT_SSL_VERIFYPEER => FALSE,
        CURLOPT_SSL_VERIFYHOST => FALSE,
        CURLOPT_USERAGENT, 'Parallel Curl test script',
    );

    $max_parallel_requests = 10;
    $parallel_curl = new ParallelCurl($max_parallel_requests, $curl_options);

    $urls = array("http://github.com/", "http://news.ycombinator.com/");

    foreach ($urls as $url) {
        $param = 'Arbitrary parameter to callback: ' . $url;
        $parallel_curl->startRequest($url, 'on_request_done', $param);
    }

    $parallel_curl->finishAllRequests();

## In detail

This module provides an easy-to-use interface to allow you to run multiple CURL url fetches in parallel in PHP. 

To test it, go to the command line, cd to this folder and run

    ./test.php

This should run 100 searches through Google's API, printing the results. To see what sort of
performance difference running parallel requests gets you, try altering the default of 10 requests
running in parallel using the optional script argument, and timing how long each takes:

    time ./test.php 1
    time ./test.php 20

The first only allows one request to run at once, serializing the calls. I see this taking around
100 seconds. The second run has 20 in flight at a time, and takes 11 seconds! Be warned though,
it's possible to overwhelm your target if you fire too many requests at once. You may end up
with your IP banned from accessing that server, or hit other API limits.

The class is designed to make it easy to run multiple curl requests in parallel, rather than
waiting for each one to finish before starting the next. Under the hood it uses curl_multi_exec
but since I find that interface painfully confusing, I wanted one that corresponded to the tasks
that I wanted to run.

To use it, first copy `parallelcurl.php` and include it, then create the `ParallelCurl` object:

    $parallelcurl = new ParallelCurl(10);

The first argument to the constructor is the maximum number of outstanding fetches to allow
before blocking to wait for one to finish. You can change this later using `setMaxRequests()`
The second optional argument is an array of curl options in the format used by `curl_setopt_array()`

Next, start a URL fetch:

    $parallelcurl->startRequest('http://example.com', 'on_request_done', array('something'));

The first argument is the address that should be fetched
The second is the callback function that will be run once the request is done
The third is a 'cookie', that can contain arbitrary data to be passed to the callback

This `startRequest` call will return immediately, as long as less than the maximum number of
requests are outstanding. Once the request is done, the callback function will be called, eg:

    on_request_done($content, 'http://example.com', $ch, array('something));

The callback should take four arguments. The first is a string containing the content found at
the URL. The second is the original URL requested, the third is the curl handle of the request that
can be queried to get the results, and the fourth is the arbitrary 'cookie' value that you 
associated with this object. This cookie contains user-defined data.

There's an optional fourth parameter to startRequest. If you pass in an array at that position in
the arguments, the POST method will be used instead, with the contents of the array controlling the
contents of the POST parameters.

Since you may have requests outstanding at the end of your script, you *MUST* call

    $parallelcurl->finishAllRequests();

before you exit. If you don't, the final requests may be left unprocessed!

By Pete Warden <pete@petewarden.com>, freely reusable, see [http://petewarden.typepad.com](http://petewarden.typepad.com) for more
