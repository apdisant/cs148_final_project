<?
session_start();
$debug = 1;
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

/*
if (isset($_POST["btnUpload"])) {
    if ($fromPage != $yourURL) {
        die("<p>Sorry you cannot access this page. Security breach detected and reported.</p>");
    }
    
    $allowedExts = array("gif", "jpeg", "jpg", "png");
    $temp = explode(".", $_FILES["imgFile"]["name"]);
    $extension = end($temp);
    
    if ((($_FILES["imgFile"]["type"] == "image/gif")
    || ($_FILES["imgFile"]["type"] == "image/jpeg")
    || ($_FILES["imgFile"]["type"] == "image/jpg")
    || ($_FILES["imgFile"]["type"] == "image/pjpeg")
    || ($_FILES["imgFile"]["type"] == "image/x-png")
    || ($_FILES["imgFile"]["type"] == "image/png"))
    && ($_FILES["imgFile"]["size"] < 20000)
    && in_array($extension, $allowedExts))
      {
      if ($_FILES["file"]["error"] > 0) {
        if ($debug) { echo "Return Code: " . $_FILES["imgFile"]["error"] . "<br>";}
        $output="<p>There was a problem submitting your file</p>";
      } else {
        if ($debug) {
            echo "<p>Upload: " . $_FILES["imgFile"]["name"] . "<br>";
            echo "Type: " . $_FILES["imgFile"]["type"] . "<br>";
            echo "Size: " . ($_FILES["imgFile"]["size"] / 1024) . " kB<br>";
            echo "Temp file: " . $_FILES["imgFile"]["tmp_name"] . "<br>";
        }
        
        if (file_exists("images/" . $_FILES["imgFile"]["name"])){
          $output= $_FILES["imgFile"]["name"] . " already exists. ";
        }else{
          move_uploaded_file($_FILES["imgFile"]["tmp_name"],"images/" . $_FILES["imgFile"]["name"]);
          $output="<p>File Stored in: " . "images/" . $_FILES["imgFile"]["name"];
          }
        }
      }
    else
      {
      $output="<p>Invalid file";
      }
   

} // ends form was submitted
*/

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
    $Recipient= htmlentities($_POST["txtRecipient"], ENT_QUOTES, "UTF-8");
    if (!$Recipient)
    {
       $Recipient = $_SESSION['Username'];
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
            $emailERROR = true;
/*
 $valid = verifyAlphaNum($Recipient);
        if (!$valid) {
            $errorMsg[] = "I'm sorry, the username you entered is not valid.";
                     }
*/
###################################################################################

   if (!$errorMsg){
      if ($debug) print "<p>Form is valid</p>";

###################################################################################

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

          $sql2 = 'insert into tblNoteToUser Set fkNoteID="' .$primaryKey . '", ';
          $sql2 .= 'fkFromUsername="' .$_SESSION["Username"]. '",';
          $sql2 .= 'fkToUsername="' .$Recipient. '"';

          $stmt2 = $db->prepare($sql2);
          if ($debug) print "<p>sql2 " .$sql2;

          $stmt2->execute();
$dataEntered = $db->commit();
            if ($debug) print "<p>transaction complete ";
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
                  id="frmAdd">
                <fieldset class="add">
                    <legend>add a note</legend>


                    <textarea id ="txtNote" name="txtNote" class="element text medium<?php if ($NoteERROR) echo ' mistake'; ?>" type="textarea" rows="20" cols="85" wrap="soft" maxlength="12000" value="<?php echo $Note; ?>" placeholder="Enter your note here" onfocus="this.select()"  tabindex="30"/>
                    </textarea>

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

