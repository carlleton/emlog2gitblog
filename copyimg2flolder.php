<?php
$srcfolder='e:/www/emlog';//目标目录
$outfolder="F:/out/";//源目录


function match($str){// 识别图片
  $pattern='/!\[(.*)\]\(.*\)/i';
  $result = array(); 
  preg_match_all($pattern, $str, $result);
  return $result[0];
}
function takefile(){
  // 读取目录
  
  // 读取文件
  $file_path = '1006.md';
  $str = file_get_contents($file_path);
  // 图片处理
  var_dump(match($str));
  // 复制图片
  
}
takefile();
?>