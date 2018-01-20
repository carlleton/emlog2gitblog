<?php
/**
 * 读取md文件中的图片地址，copy图片到某个目录
 */
$scanfolder="E:/www/markdown/";//读取md文件的目录
$srcfolder='e:/www/blogold';//源目录
$outfolder="./images";//目标目录
$host = "http://www.vcandou.com";


function match($str){// 识别图片
  $pattern='/!\[(.*)\]\((.*)\)/i';
  $result = array(); 
  preg_match_all($pattern, $str, $result);
  return $result[2];
}
function copyfile(){
  global $scanfolder;
  global $srcfolder;
  global $outfolder;
  global $host;
  // 读取目录
  $filelist = scandir($scanfolder);
  for($i=0,$num=count($filelist);$i<$num;++$i){
    if($filelist[$i]=='.'||$filelist[$i]=='..')continue;
    // 读取文件
    $file_path = $scanfolder.$filelist[$i];
    $str = file_get_contents($file_path);
    $content = $str;
    $pics = match($str);
    $picnum=count($pics);
    if($picnum==0)continue;
    
    $isreplaceurl = false;
    // 图片处理
    for($j=0;$j<$picnum;$j++){
      // 复制图片
      $pic_path = $pics[$j];
      $httpnum = strpos($pic_path,"http://");
      if(is_numeric($httpnum) && $httpnum == 0){ //网络图片下载下来
        $arr1 = explode('/',$pic_path);
        $newfile = $arr1[count($arr1)-1];
        $newfilenum = stripos($newfile,"!");
        if(is_numeric($newfilenum)){
          $newfile = substr($newfile,0,$newfilenum);
        }
        $newfilenum = stripos($newfile,"?");
        if(is_numeric($newfilenum)){
          $newfile = substr($newfile,0,$newfilenum);
        }
        if(!stripos($newfile,".")){
          $newfile = date('Ymdhis').rand(0,10000).".jpg";
        }
        $newfile = "/201801/".$newfile;
        $newfile = $outfolder.$newfile;
        $result = httpcopy($pic_path, $newfile);
        echo '重写'.$result."<br>";
        $content = str_replace($pic_path, $newfile, $content);
        $isreplaceurl = true;
        //echo $pic_path.'<br>';
      } else {
        $sourcefile = $srcfolder.$pic_path;
        $destfile = $outfolder.$pic_path;
        $destfile = str_replace($host,"",$destfile);
        $destfile = str_replace("/UserFiles/image","",$destfile);
        $destfile = str_replace("!600","",$destfile);
        
        $destfolder = substr($destfile, 0, strrpos($destfile, '/'));
        if(file_exists($sourcefile)){
          if(!file_exists($destfolder)){
            mkdir(iconv("UTF-8", "GBK", $destfolder),0777,true);
          }
          copy($sourcefile,$destfile);
        }else{
          echo "不存在文件：".$sourcefile."<br>";
        }
      }
    }
    // 如果有替换，自动重写
    if($isreplaceurl){
      $fp = fopen($file_path, "w");
      fwrite($fp,$content);
      fclose($fp);
    }
  }
}
copyfile();

// 下载url到本地
function httpcopy($url, $file="", $timeout=60) {
  $file = empty($file) ? pathinfo($url,PATHINFO_BASENAME) : $file;
  $dir = pathinfo($file,PATHINFO_DIRNAME);
  !is_dir($dir) && @mkdir($dir,0755,true);
  $url = str_replace(" ","%20",$url);
  
  if(function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    try{
      $temp = curl_exec($ch);
    }catch(Exception $e){
      echo "问题：".$url."<br>";
    }
    
    if(@file_put_contents($file, $temp) && !curl_error($ch)) {
      return $file;
    } else {
      return false;
    }
  } else {
    $opts = array(
      "http"=>array(
      "method"=>"GET",
      "header"=>"",
      "timeout"=>$timeout)
    );
    $context = stream_context_create($opts);
    if(@copy($url, $file, $context)) {
      //$http_response_header
      return $file;
    } else {
      return false;
    }
  }
}
?>