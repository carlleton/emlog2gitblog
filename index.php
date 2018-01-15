<?php
header("content-type:text/html;charset=utf-8");
$mysql_server_name='localhost';
$mysql_username='root';
$mysql_password='root';
$mysql_database='emlog';
$conn=mysqli_connect($mysql_server_name,$mysql_username,$mysql_password,$mysql_database);
mysqli_query($conn,"SET NAMES UTF8");
$sql='select * from emlog_blog';
$result=mysqli_query($conn,$sql);
$ids = '';
while($row = mysqli_fetch_array($result)){
	$ids .= ','.$row['gid'];
}
if(strlen($ids)>0){
  $ids = substr($ids,1);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>demo</title>
</head>
<body>
  <div id="result"></div>
  <script src="jquery-1.7.1.js"></script>
  <script>
  var idstr = '<?php echo $ids ?>';
  var ids = idstr.split(',');
  var allcount = ids.length;
  var count = 0;
  var fileurl = 'makefile.php?ids=';
  makefile();
  function makefile(){
    var strids = ''
    for(var i = count,n=count+10;i<n && count<allcount;i++){
      strids += ',' + ids[i];
      count++;
    }
    if(!strids)return;
    strids = strids.substr(1);
    var url = fileurl + strids;
    console.log(url);
    $.get(url, function (result){
      $('#result').append('<p>'+result+'</p>');
      makefile();
    });
  }
  
  </script>
</body>
</html>