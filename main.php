<?php
/*
randomhikakin_system
Unauthorized use is prohibited. Please contact the author when using it.
Author:@tomox0115 | https://0115765.com
*/
// オートローディング
require('vendor/autoload.php');
use ParagonIE\ConstantTime\Encoding;
use Abraham\TwitterOAuth\TwitterOAuth;

// Twitter認証情報設定
$CK = '************************';
$CS = '**********************************************';
$AT = '**********************************************';
$AS = '*****************************************';

// 動画データベースから情報取得
$file = "database/hikakin.json";
$json = file_get_contents($file);
$json = json_decode($json, true);
$val = array_rand($json, 1);
$title = $json[$val]['snippet']['title'];
$videoid = $json[$val]['id']['videoId'];
$url = "https://youtube.com/watch?v=" . $videoid;


use YoutubeDl\Options;
use YoutubeDl\YoutubeDl;

$yt = new YoutubeDl();

$collection = $yt->download(
    Options::create()
        ->downloadPath('/hikakin')
        ->url($url)
);

// YT動画ダウンロード
foreach ($collection->getVideos() as $video) {
    if ($video->getError() !== null) {
        echo "Error downloading video: {$video->getError()}.";
    } else {
        $oldname = $video->getFile();
        rename($oldname, $videoid);
    }
}

// FFmpeg+Nodeキャプチャ
exec("node cap.js " . $videoid);


// Tweet部分
$connection = new TwitterOAuth($CK, $CS, $AT, $AS);

$imageId = $connection->upload('media/upload', ['media' => 'tn.png']);
// パラメータ編集
$tweet = [
  'status' => $title . ' ' . $url,
  'media_ids' => implode(',', [
    $imageId->media_id_string
   ])
  ];

$res = $connection->post("statuses/update", $tweet);

unlink($videoid);
unlink("tn.png");

