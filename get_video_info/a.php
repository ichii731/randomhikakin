<?php
/*
日時関連でエラーが出た場合は↓みたいにデフォルトタームゾーンを指定してあげてください。
date_default_timezone_set("America/Los_Angeles");
*/

//YouTube API v3
$API_KEY = "***************************************";

// ユーザー名からチャンネルIDを取得(ID分かってるならスキップOK)
function get_user_channel_id($user){
    global $API_KEY;
    $url = 'https://www.googleapis.com/youtube/v3/channels?key=' . $API_KEY . '&part=id&forUsername=';
    return search($user,$url)['items'][0]['id'];
}

// 
function search($searchTerm,$url){
    $url = $url . urlencode($searchTerm);

    $result = file_get_contents($url);

    if($result !== false){
        return json_decode($result, true);
    }

    return false;
}

function push_data($searchResults){
    global $data;
    foreach($searchResults['items'] as $item){
        $data[] = $item;
    }
    return $data;
}

function get_url_for_time_period($channelId, $time){
    global $API_KEY;
    // 日時型指定
    $publishedAfter = date("Y-m-d\TH:i:sP",strval($time));
    //　期間を60日指定
    $publishedBefore_ = $time + (60 * 60 * 24 * 60);
    $publishedBefore = date("Y-m-d\TH:i:sP",$publishedBefore_);
    // リクエスト用URL作成
    $url = 'https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&key=' . $API_KEY . '&maxResults=50&channelId=' . $channelId . '&publishedAfter=' . urlencode($publishedAfter) . '&publishedBefore=' . urlencode($publishedBefore);

    return array("url"=>$url,"utc"=>$publishedBefore_);
}


$start_date = "YYYY-MM-DD"; // 日時指定
$time = strtotime($start_date);
$username = "*****";

$channelId = get_user_channel_id($username);

while($time < time()){
    $url = get_url_for_time_period($channelId, $time);
    $searchResults = search("", $url['url']);
    $data = push_data($searchResults);
    $time += 60 * 60 * 24 * 60; // 60日追加
}

// 帰ってきたデータを表示するなら…
echo "<pre>";
var_dump($data);
echo "</pre>";

// 配列をJSON形式にして保存するなら…
$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents($username . ".json" , $json);

// 動画数を出力するなら…
print count($data);
