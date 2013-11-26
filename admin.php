<?php
session_start();

$debug = 1;
if ($debug) print "<p>DEBUG MODE IS ON</p>";

if ($_SESSION['admin'] != 1)
{
   session_destroy();
   die("<p>You're not the admin</p>");
}

$baseURL = "https://www.uvm.edu/~apdisant/";
$folderPath = "cs148/assignment5.1/";
// full URL of this form
$yourURL = $baseURL . $folderPath . "admin.php";

require_once("connect.php");

if (isset($_POST["btnSubmit"])) {
    $fromPage = getenv("http_referer");

    if ($debug){
        print "<p>From: " . $fromPage . " should match ";
        print "<p>Your: " . $yourURL;
}

    if ($fromPage != $yourURL) {
        die("<p>Sorry you cannot access this page. Security breach detected and reported.</p>");
    }

    $Username = htmlentities($_POST["txtUsername"], ENT_QUOTES, "UTF-8");


    $sql  = 'SELECT pkUsername, fldDateJoined, fldColorScheme, fldNotesMade, fldSpaceUsed ';
    $sql .= 'FROM tblUser ';
    $sql .= 'WHERE  pkUsername = "' .$Username. '"';

if ($debug) print "<p>sql ". $sql;

$stmt = $db->prepare($sql);

$stmt->execute();

$user = $stmt->fetchAll();
if($debug){ print "<pre>"; print_r($user); print "</pre>";}
if($user)
{
    $_SESSION['Username'] = $user['pkUser'];
}else{
    $UsernameERROR = true;
    $errorMsg[] = "incorrect user or password";
}
}//end if submitted
######################################################################

        if (isset($_POST["btnSubmit"]) AND empty($errorMsg))
        {
            print "<p>logged in as" . $SESSION_["Username"]. "</p>";
            print "<p>logged in as" . $Username. "</p>";
        }else{

##############################################################################
            
            print '<p>not a valid user</p>';
            ?>

<form action="<? print $_SERVER['PHP_SELF']; ?>"
                  
                  method="post"
                  id="frmLogin">
                <fieldset class="contact">
                    <legend>Login</legend>

                    <label class="required" for="txt">Username </label>

                    <input id ="txtUsername" name="txtUsername" class="element text medium<?php if ($UsernameERROR) echo ' mistake'; ?>" type="text" maxlength="255" value="<?php echo $Username; ?>" placeholder="Username" onfocus="this.select()"  tabindex="30"/>

                     </fieldset>

<fieldset class="buttons">
   <input type="submit" id="btnSubmit" name="btnSubmit" value="Register" tabindex="991" class="button">
</fieldset>
</form>

<?
}//end submission
?>
