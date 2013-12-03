<?php
session_start();

$debug = 0;
if ($debug) print "<p>DEBUG MODE IS ON</p>";

$baseURL = "https://www.uvm.edu/~apdisant/";
$folderPath = "cs148/assignment5.1/";
// full URL of this form
$yourURL = $baseURL . $folderPath . "login.php";

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


//#############################################################################
// replace any html or javascript code with html entities
//

    $Username = htmlentities($_POST["txtUsername"], ENT_QUOTES, "UTF-8");
    $_SESSION['Username'] = $Username;
    if ($debug) print '<p> sess Username: '.$_SESSION['Username'].'</p> <p> Username: ' .$Username. '</p>';
    $Password = htmlentities($_POST["txtPassword"], ENT_QUOTES, "UTF-8");
    $HashedPass = md5($Password);
    if ($debug) print '<p> pass: ' .$Password.'</p> <p> hashed: ' .$HashedPass. '</p>';



include ("validation_functions.php");

    $errorMsg = array();


//############################################################################
// 
// Check each of the fields for errors then adding any mistakes to the array.
//
    //^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^       Check email address
    if (empty($Username)) {
        $errorMsg[] = "Please enter your Username";
        $UsernameERROR = true;
    } else {
        $valid = verifyUsername($Username); /* test for non-valid  data */
        if (!$valid) {
            $errorMsg[] = "I'm sorry, the username you entered is not valid.";
            $UsernameERROR = true;
        }
    }
    if (empty($Password)) {
        $errorMsg[] = "Please enter your Password";
        $PasswordERROR = true;
    } else {
        $valid = verifyPass($Password); /* test for non-valid  data */
        if (!$valid) {
            $errorMsg[] = "I'm sorry, the username you entered is not valid.";
            $PasswordERROR = true;
        }
    }

###########################################################################
// 
// test to see if the username exists in the table and if the hashed password matches
//

   $sql  = 'SELECT pkUsername, fldDateJoined, fldColorScheme, fldNotesMade, fldSpaceUsed ';
$sql .= 'FROM tblUser ';
$sql .= 'WHERE  pkUsername = "' .$Username. '"';
$sql .= ' and  fldPassword = "' .$HashedPass. '"';
if ($debug) print "<p>sql ". $sql;

$stmt = $db->prepare($sql);
            
$stmt->execute(); 

$user = $stmt->fetchAll(); 
if($debug){ print "<pre>"; print_r($user); print "</pre>";}
if($user)
{
    $SESSION_['Username'] = $user['pkUser'];
}else{
    $UsernameERROR = true;
    $errorMsg[] = "incorrect user or password";
}
if($user['pkUser'] = admin)
{
   $_SESSION["admin"] = 1;
   if ($debug) print $_SESSION['admin'];
}

}//end if submitted
   
include ("top.php");

    $ext = pathinfo(basename($_SERVER['PHP_SELF']));
    $file_name = basename($_SERVER['PHP_SELF'], '.' . $ext['extension']);

//    print '<body id="' . $file_name . '">';

include ("header.php");
    ?>

    <section id="main">

        <?

######################################################################

        if (isset($_POST["btnSubmit"]) AND empty($errorMsg)) 
        {
            print "<p>logged in as " . $Username. "</p>";
        }else{

##############################################################################

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
                  id="frmLogin">
                <fieldset class="contact">
                    <legend>Login</legend>

                    <label class="required" for="txt">Username </label>

                    <input id ="txtUsername" name="txtUsername" class="element text medium<?php if ($UsernameERROR) echo ' mistake'; ?>" type="text" maxlength="255" value="<?php echo $Username; ?>" placeholder="Username" onfocus="this.select()"  tabindex="1"/>

                </fieldset>


<fieldset>
      <label for="Password" class="required">Password </label>
      <input type="password" id="txtPassword" name="txtPassword" value="<?php echo $Password; ?>" class="element text medium" placeholder="Password"  onfocus="this.select()" tabindex=2/>
    </fieldset>



<fieldset class="buttons">
                    <input type="hidden" name="redirect" value="form.php">
                    <input type="submit" id="btnSubmit" name="btnSubmit" value="Register" tabindex=3 class="button">
                    <input type="reset" id="butReset" name="butReset" value="Reset Form" tabindex=4 class="button" onclick="reSetForm()" >
                </fieldset>
</form>



 <?php
        } // end body submit
        ?>
    </section>



<?
        include ("footer.php");
?>

