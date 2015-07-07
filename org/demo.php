<?php

require("getid/getid3.php");

#$file=realpath("2.mp3");
$file=realpath("3.MP3");

$getID3 = new getID3;

$FileInfo = $getID3->analyze($file);

echo '<pre>';
print_r($FileInfo);

# 获取播放时间的参数
#playtime_seconds
#playtime_string

