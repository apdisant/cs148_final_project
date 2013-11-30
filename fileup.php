<?
$debug = 0;
$uploadsDir = 'files';

$yourURL = "https://www.uvm.edu/~apdisant/cs148/assignment5.1/fileup.php";

$fromPage = getenv("http_referer");

if ($debug) {
    print "<p>From: " . $fromPage . " should match ";
    print "<p>Your: " . $yourURL;
}

if(isset($_POST['upload']) && $_FILES['userfile']['size'] > 0)
{
$fileName = $_FILES['userfile']['name'];
$tmpName  = $_FILES['userfile']['tmp_name'];
$fileSize = $_FILES['userfile']['size'];
$fileType = $_FILES['userfile']['type'];

$fp      = fopen($tmpName, 'r');
$content = fread($fp, filesize($tmpName));
$content = addslashes($content);
fclose($fp);

if(!get_magic_quotes_gpc())
{
    $fileName = addslashes($fileName);
}


if ($debug)
{
   echo "<br>Filename $fileName <br>";
   print "<p>tempname " .$tmpName."</p>";
   print "<p> filesize" .$fileSize."</p>";
   print "<p> filetype" .$fileType."</p>";
   print $uploadsDir."/".$fileName;
}
if (file_exists($uploadsDir."/".$fileName))
{
print "<p>" . $fileName . " already exists. ";
}else{
move_uploaded_file($tmpName, "$uploadsDir/$fileName");
   print "<p>".$fileName. " uploaded</p>";
}
}else{
print "upload file (size limit 18mb)";
}

?>

<form method="post" enctype="multipart/form-data">
<table width="350" border="0" cellpadding="1" cellspacing="1" class="box">
<tr>
<td width="246">
<input type="hidden" name="MAX_FILE_SIZE" value="20000000">
<input name="userfile" type="file" id="userfile">
</td>
<td width="80"><input name="upload" type="submit" class="box" id="upload" value=" Upload "></td>
</tr>
</table>
</form>


