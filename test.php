#!/usr/bin/php
<?php
// 
// A test script for the ParallelCurl class
// 
// This example fetches a 100 different results from Google's search API, with no more
// than 10 outstanding at any time.
//
// By Pete Warden <pete@petewarden.com>, freely reusable, see http://petewarden.typepad.com for more

require_once('parallelcurl.php');

define ('SEARCH_URL_PREFIX', 'http://ajax.googleapis.com/ajax/services/search/web?v=1.0&rsz=large&filter=0');

// This function gets called back for each request that completes
function on_request_done($content, $url, $ch, $search) {
    
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);    
    if ($httpcode !== 200) {
        print "Fetch error $httpcode for '$url'\n";
        return;
    }

    $responseobject = json_decode($content, true);
    if (empty($responseobject['responseData']['results'])) {
        print "No results found for '$search'\n";
        return;
    }

    print "********\n";
    print "$search:\n";
    print "********\n";

    $allresponseresults = $responseobject['responseData']['results'];
    foreach ($allresponseresults as $responseresult) {
        $title = $responseresult['title'];
        print "$title\n";
    }
}

// The terms to search for on Google
$terms_list = array(
    "John", "Mary",
    "William", "Anna",
    "James", "Emma",
    "George", "Elizabeth",
    "Charles", "Margaret",
    "Frank", "Minnie",
    "Joseph", "Ida",
    "Henry", "Bertha",
    "Robert", "Clara",
    "Thomas", "Alice",
    "Edward", "Annie",
    "Harry", "Florence",
    "Walter", "Bessie",
    "Arthur", "Grace",
    "Fred", "Ethel",
    "Albert", "Sarah",
    "Samuel", "Ella",
    "Clarence", "Martha",
    "Louis", "Nellie",
    "David", "Mabel",
    "Joe", "Laura",
    "Charlie", "Carrie",
    "Richard", "Cora",
    "Ernest", "Helen",
    "Roy", "Maude",
    "Will", "Lillian",
    "Andrew", "Gertrude",
    "Jesse", "Rose",
    "Oscar", "Edna",
    "Willie", "Pearl",
    "Daniel", "Edith",
    "Benjamin", "Jennie",
    "Carl", "Hattie",
    "Sam", "Mattie",
    "Alfred", "Eva",
    "Earl", "Julia",
    "Peter", "Myrtle",
    "Elmer", "Louise",
    "Frederick", "Lillie",
    "Howard", "Jessie",
    "Lewis", "Frances",
    "Ralph", "Catherine",
    "Herbert", "Lula",
    "Paul", "Lena",
    "Lee", "Marie",
    "Tom", "Ada",
    "Herman", "Josephine",
    "Martin", "Fanny",
    "Jacob", "Lucy",
    "Michael", "Dora",
);

if (isset($argv[1])) {
    $max_requests = $argv[1];
} else {
    $max_requests = 10;
}

$curl_options = array(
    CURLOPT_SSL_VERIFYPEER => FALSE,
    CURLOPT_SSL_VERIFYHOST => FALSE,
    CURLOPT_USERAGENT, 'Parallel Curl test script',
);

$parallel_curl = new ParallelCurl($max_requests, $curl_options);

foreach ($terms_list as $terms) {
    $search = '"'.$terms.' is a"';
    $search_url = SEARCH_URL_PREFIX.'&q='.urlencode($terms);
    $parallel_curl->startRequest($search_url, 'on_request_done', $search);
}

// This should be called when you need to wait for the requests to finish.
// This will automatically run on destruct of the ParallelCurl object, so the next line is optional.
$parallel_curl->finishAllRequests();

?>