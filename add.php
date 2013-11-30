<?
session_start();
$debug = 0;
$uploadsDir = 'files';
if ($debug) print "<p>DEBUG MODE IS ON</p>";
if ($debug) print "<p> session username: " . $_SESSION["Username"]. "</p>";

$baseURL = "https://www.uvm.edu/~apdisant/";
$folderPath = "cs148/assignment5.1/";
// full URL of this form
$yourURL = $baseURL . $folderPath . "add.php";

require_once("connect.php");

$Note = "Enter your note here";
$Recipient = "";
$Deadline = "";

###################################################################################


if (isset($_POST["btnSubmit"])) {
    $fromPage = getenv("http_referer");

    if ($debug){
        print "<p>From: " . $fromPage . " should match ";
        print "<p>Your: " . $yourURL;
}

    if ($fromPage != $yourURL) {
        die("<p>Sorry you cannot access this page. Security breach detected and reported.</p>");
    }

    $Note = htmlentities($_POST["txtNote"], ENT_QUOTES, "UTF-8");
    if($debug) print "<p> note: " .$Note. "</p>";
    $Recipients= htmlentities($_POST["txtRecipients"], ENT_QUOTES, "UTF-8");
    if (!$Recipients)
    {
       $Recipients = $_SESSION['Username'];
    } 
    $Deadline = htmlentities($_POST["txtDeadline"], ENT_QUOTES, "UTF-8");
    $date = date('Y-m-d H:i:s');

if ($debug) print "<p>date: " . $date . "</p>";


###################################################################################

   include ("validation_functions.php");
   $errorMsg = array();

 $valid = verifyText($Note); /* test for non-valid  data */
        if (!$valid) {
            $errorMsg[] = "I'm sorry, the Note you entered is not valid. Letters, numbers and punctuation only";
                     }
 $valid = verifyRecipients($Recipients); /* test for non-valid  data */
        if (!$valid) {
            $errorMsg[] = "I'm sorry, the list of recipients you entered is not valid. Letters and numbers only separated by commas";
                     }
##################################################################################
//image submission
if ($_FILES['userfile']['size'] > 0)
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
}

##############################################################################
//recipient list scraping

if ($debug) print "<p>original recipients: " .$Recipients."</p>";
$NSRecipients = str_replace(' ','',$Recipients);
if ($debug) print "<p>spaceless recipients: " .$NSRecipients."</p>";
$ArrayRecipients = explode(',',$Recipients);
if ($debug) print "<p>csv recipients: "; print_r($ArrayRecipients); print "</p>";

##############################################################################
   if (!$errorMsg){
      if ($debug) print "<p>Form is valid</p>";

##################################################################################

   $primaryKey = "";
   $dataEntered = false;
   
    try {
            $db->beginTransaction();

            $sql = 'INSERT INTO tblNote SET fldMessage="' . $Note . '", ';
            $sql .= 'fkFromUsername="' .$_SESSION["Username"]. '",';
            $sql .= 'fldToUsername="' .$Recipient . '",';
            $sql .= 'fldTimePosted="' .$date . '",';
            $sql .= 'fldDeadline="' .$Deadline . '"';

            //$sql .= '
            $stmt = $db->prepare($sql);
            if ($debug) print "<p>sql ". $sql;

            $stmt->execute();

            $primaryKey = $db->lastInsertId();
            if ($debug) print "<p>pk = " .$primaryKey;

          
          foreach($ArrayRecipients as $Recipient)
          {
             $RecipNum = array_search($Recipient,$ArrayRecipients);
             $NSRecipient = str_replace(' ','',$Recipient);
             $sql2 = 'insert into tblNoteToUser Set fkNoteID="' .$primaryKey . '", ';
             $sql2 .= 'fldRecipNum="' .$RecipNum. '",';
             $sql2 .= 'fkFromUsername="' .$_SESSION["Username"]. '",';
             $sql2 .= 'fkToUsername="' .$NSRecipient. '"';

             $stmt2 = $db->prepare($sql2);
             if ($debug) print "<p>sql2 " .$sql2;

             $stmt2->execute();
          }

          $sql3 = 'insert into tblFile Set fkNoteID="' .$primaryKey . '", ';
          $sql3 .= 'fldName="' .$fileName. '",';
          $sql3 .= 'fldSize="' .$fileSize. '"';

          $stmt3 = $db->prepare($sql3);
          if ($debug) print "<p>sql3 " .$sql3;

          $stmt3->execute();

$dataEntered = $db->commit();
            if ($debug) print "<p>transaction complete";
        } catch (PDOExecption $e) {
            $db->rollback();
            if ($debug) print "Error!: " . $e->getMessage() . "</br>";
            $errorMsg[] = "There was a problem with accpeting your data please contact us directly.";
        }


        // If the transaction was successful, give success message
        if ($dataEntered) 
        {
            if ($debug) print "<p>data entered now prepare keys ";
            //#################################################################
            // create a key value for confirmation

            $sql = "SELECT fldTimePosted FROM tblNote WHERE pkNoteId=" . $primaryKey;
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $dateSubmitted = $result["fldTimePosted"];

            $key1 = sha1($dateSubmitted);
            $key2 = $primaryKey;
         } //data entered
     } // no errors
} //ends if form submitted

#######################################################################################
// Begin creating actual form
#######################################################################################

include ("top.php");

   $ext = pathinfo(basename($_SERVER['PHP_SELF']));
   $file_name = basename($_SERVER['PHP_SELF'], '.' . $ext['extension']);
     if ($debug) print '<body id="' . $file_name . '">';

include ("header.php");
?> 
   
   <section id="main">

        <?
//############################################################################
//
//  In this block  display the information that was submitted and do not 
//  display the form.
//
        if (isset($_POST["btnSubmit"]) AND empty($errorMsg)) {
            print "<p>Note Submitted ";

        } else {
//#############################################################################
//
// Here we display any errors that were on the form
//

            print '<div id="errors">';

            if ($errorMsg) {
                echo "<ol>\n";
                foreach ($errorMsg as $err) {
                    echo "<li>" . $err . "</li>\n";
                }
                echo "</ol>\n";
            }

            print '</div>';
            ?>


<form action="<? print $_SERVER['PHP_SELF']; ?>"
                  
                  method="post"
                  enctype="multipart/form-data"
                  id="frmAdd">
                <fieldset class="add">
                    <legend>add a note</legend>

<p>Enter all recipients of this note separated by commas.</p>
<p>(if empty it will be sent only to you)</p>
<p><textarea id="txtRecipients" name="txtRecipients" class="element text medium<? if($noteERROR) echo ' mistake'; ?>" type="textarea" rows = "1" cols="85" wrap-"none" maxlength="400" value="<?php echo $Recipients;?>" placeholder="Recipients" onfocus="this.select()" tabindex="30"/>
</textarea></p>

                    <textarea id ="txtNote" name="txtNote" class="element text medium<?php if ($NoteERROR) echo ' mistake'; ?>" type="textarea" rows="20" cols="85" wrap="soft" maxlength="1200" value="<?php echo $Note; ?>" placeholder="Message" onfocus="this.select()"  tabindex="30"/>
</textarea>

                </fieldset>
<fieldset>
   <table width="350" border="0" cellpadding="1" cellspacing="1" class="box">
   <tr>
   <td width="246">
   <input type="hidden" name="MAX_FILE_SIZE" value="20000000">
   <input name="userfile" type="file" id="userfile">
   </td>
   </tr>
   </table>
</fieldset>

<fieldset class="buttons">
                    <input type="hidden" name="redirect" value="form.php">
                    <input type="submit" id="btnSubmit" name="btnSubmit" value="submit" tabindex="991" class="button">
                    <input type="reset" id="butReset" name="butReset" value="Reset Form" tabindex="993" class="button" onclick="reSetForm()" >
                </fieldset>
</form>


 <?php
        } // end body submit
        ?>
    </section>



<?
        include ("footer.php");
?>

