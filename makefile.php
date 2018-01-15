<?php
header("content-type:text/html;charset=utf-8");
require 'markdown.class.php';
$markdownObj = new \Ext\Markdown();

$ids = '';
if(isset($_REQUEST['ids'])) {
  $_REQUEST["ids"];  
}

$mysql_server_name='localhost';
$mysql_username='root';
$mysql_password='root';
$mysql_database='emlog';
$outfolder = 'out';

$conn=mysqli_connect($mysql_server_name,$mysql_username,$mysql_password,$mysql_database) or die('Unale to connect');
mysqli_query($conn,"SET NAMES UTF8");


$sql='select * from emlog_blog limit 0,10';
if($ids != '') {
  $sql="select * from emlog_blog where gid in ($ids) limit 0,10";
}
$result=mysqli_query($conn,$sql);

while($row = mysqli_fetch_array($result)){
	makefile($row['gid'],$row['alias']);
}
mysqli_close($conn);

function makefile($id,$alias){
  global $outfolder;
	//获取文件内容
	// $content=file_get_contents("makefile.php?id=$id");
  $content = getfileContent($id);
	//设置静态文件路径及文件名
	$filename=$id.".md";
  if($alias!=''){
    $filename=$alias.".md";
  }
  $filename = $outfolder.'/'.$filename;
  //检查是否存在旧文件，有则删除
	if(file_exists($filename)) unlink($filename);
	//写入文件
	$fp = fopen($filename, 'w');
	fwrite($fp, $content);
	echo $filename."生成成功！ <br />";
}
// 获取文件名
function getfileContent($id){
  global $conn;
  global $markdownObj;
  // 获取blog表
  $sql='select * from emlog_blog where gid='.$id;
  $result=mysqli_query($conn, $sql);
  $blogrow = mysqli_fetch_array($result);
  mysqli_free_result($result);
  //print_r($blogrow);

  $gid = $blogrow["gid"];
  $title = $blogrow["title"];
  $summary = $blogrow["excerpt"];
  if($summary != "") {
    $summary = $markdownObj->parseHtml($summary);
  }
  $content = $markdownObj->parseHtml($blogrow["content"]);
  $date = $blogrow["date"];
  $alias = $blogrow["alias"];
  $sortid = $blogrow["sortid"];
  $author = $blogrow["author"];

  // tags
  $str_tags = '';
  $sql = "select * from emlog_tag where gid like '%,".$gid.",%'";
  $result_tags = mysqli_query($conn,$sql);
  while($tagrow = mysqli_fetch_array($result_tags)){
    $str_tags .= $tagrow["tagname"].' ';
  }
  mysqli_free_result($result_tags);

  // 分类
  $str_cate = "";
  $sql = "select * from emlog_sort where sid=".$sortid;
  $result_sort = mysqli_query($conn,$sql);
  $sort=mysqli_fetch_array($result_sort);
  mysqli_free_result($result_sort);
  //$str_cate = $sort['alias'];
  $str_cate = $sort["sortname"];

  // username
  $str_username = "";
  $sql = "select * from emlog_user where uid=".$author;
  $result_user = mysqli_query($conn,$sql);
  $user = mysqli_fetch_array($result_user);
  mysqli_free_result($result_user);
  $str_username = $user["username"];
  
  $str = <<<str
  ?><!--
  author: <?php echo $str_username ?> 
  date: <?php echo date("Y-m-d", $date) ?> 
  title: <?php echo $title ?> 
  tags: <?php echo $str_tags ?> 
  category: <?php echo $str_cate ?> 
  status: publish
  summary: <?php echo $summary ?>
  -->
  <?php
  echo $content;
str;
  return $str;
}
?>

