<?
$debug = 1;
if ($debug) print "<p>DEBUG MODE IS ON</p>";

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
    $Recipient= htmlentities($_POST["txtRecipient"], ENT_QUOTES, "UTF-8");
    $Deadline = htmlentities($_POST["txtDeadline"], ENT_QUOTES, "UTF-8");
    $date = date('Y-m-d H:i:s');

if ($debug) print "<p>date: " . $date . "</p>";
###################################################################################

   include ("validation_functions.php");
   $errorMsg = array();

 $valid = verifyText($Note); /* test for non-valid  data */
        if (!$valid) {
            $errorMsg[] = "I'm sorry, the Note you entered is not valid. Letters, numbers and punctuation only";
            $emailERROR = true;
 $valid = verifyAlphaNum($Recipient);  /*test for non-valid  data*/
        if (!$valid) {
            $errorMsg[] = "I'm sorry, the username you entered is not valid.";
                     }
###################################################################################

   if (!$errorMsg){
      if ($debug) print "<p>Form is valid</p>";

###################################################################################

   $primaryKey = "";
   $dataEntered = false;
   
    try {
            $db->beginTransaction();

            $sql = 'INSERT INTO tblNote SET fldMessage="' . $Note . '", ';
            $sql .= 'fldFromName="' . $Username . '",';
            $sql .= 'fldToName="' .$Recipient . '",';
            $sql .= 'fldTimePosted="' .$Date . '",';
            $sql .= 'fldDeadline="' .$Deadline . '",';

            //$sql .= '
            $stmt = $db->prepare($sql);
            if ($debug) print "<p>sql ". $sql;

            $stmt->execute();

            $primaryKey = $db->lastInsertId();
            if ($debug) print "<p>pk = " .$primaryKey;

            // all sql statements are done so lets commit to our changes
            $dataEntered = $db->commit();
            if ($debug) print "<p>transaction complete ";
        } catch (PDOExecption $e) {
            $db->rollback();
            if ($debug) print "Error!: " . $e->getMessage() . "</br>";
            $errorMsg[] = "There was a problem with accpeting your data please contact us directly.";
        }


        // If the transaction was successful, give success message
        if ($dataEntered) {
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

