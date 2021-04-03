<?php
date_default_timezone_set("America/Los_Angeles");

//youtube api key
$API_KEY = "AIzaSyCawlPUFA0p4Lu1oMHqoFGYNxm6J1el1XI";

function search($searchTerm,$url){
    $url = $url . urlencode($searchTerm);

    $result = file_get_contents($url);

    if($result !== false){
        return json_decode($result, true);
    }

    return false;
}

function get_user_channel_id($user){
    global $API_KEY;
    $url = 'https://www.googleapis.com/youtube/v3/channels?key=' . $API_KEY . '&part=id&forUsername=';
    return search($user,$url)['items'][0]['id'];
}

function push_data($searchResults){
    global $data;
    foreach($searchResults['items'] as $item){
        $data[] = $item;
    }
    return $data;
}

function get_url_for_utc_period($channelId, $utc){
    //get the API_KEY
    global $API_KEY;
    //youtube specifies the DateTime to be formatted as RFC 3339 formatted date-time value (1970-01-01T00:00:00Z)
    $publishedAfter = date("Y-m-d\TH:i:sP",strval($utc));
    //within a 60 day period
    $publishedBefore_ = $utc + (60 * 60 * 24 * 60);
    $publishedBefore = date("Y-m-d\TH:i:sP",$publishedBefore_);
    //develop the URL with the API_KEY, channelId, and the time period specified by publishedBefore & publishedAfter
    $url = 'https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&key=' . $API_KEY . '&maxResults=50&channelId=' . $channelId . '&publishedAfter=' . urlencode($publishedAfter) . '&publishedBefore=' . urlencode($publishedBefore);

    return array("url"=>$url,"utc"=>$publishedBefore_);
}
//the date that the loop will begin with, have this just before the first videos on the channel.
//this is just an example date
$start_date = "2011-01-01";
$utc = strtotime($start_date);
$username = "hikakintv";
//get the channel id for the username
$channelId = get_user_channel_id($username);

while($utc < time()){
    $url_utc = get_url_for_utc_period($channelId, $utc);
    $searchResults = search("", $url_utc['url']);
    $data = push_data($searchResults);
    $utc += 60 * 60 * 24 * 60;
}
print "<pre>";
print_r($data);
print "</pre>";

//check that all of the videos have been accounted for (cross-reference this with what it says on their youtube channel)
print count($data);

$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents("hikakin.json" , $json);
