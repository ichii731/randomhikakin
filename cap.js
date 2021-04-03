// ターミナルパラメータのチェック
if (process.argv.length < 3) {
    console.log('missing argument.');
    return;
}
// パラメータの内容を受け取る
var argValue = process.argv[2];

var path = require( 'path' );
const ffmpeg = require('fluent-ffmpeg');
const command = ffmpeg(argValue);
command.screenshots();
console.log('tn.png');
