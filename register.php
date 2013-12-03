<?php
session_start();
/* the purpose of this page is to display a form to allow a person to register
 * the form will be sticky meaning if there is a mistake the data previously 
 * entered will be displayed again. Once a form is submitted (to this same page)
 * we first sanitize our data by replacing html codes with the html character.
 * then we check to see if the data is valid. if data is valid enter the data 
 * into the table and we send and dispplay a confirmation email message. 
 * 
 * if the data is incorrect we flag the errors.
 * 
 * Written By: Robert Erickson robert.erickson@uvm.edu
 * Last updated on: October 10, 2013
 * 
 * 
  -- --------------------------------------------------------
  --
  -- Table structure for table `tblRegister`
  --

  CREATE TABLE IF NOT EXISTS `tblRegister` (
  `pkRegisterId` int(11) NOT NULL AUTO_INCREMENT,
  `fldEmail` varchar(65) DEFAULT NULL,
  `fldDateJoined` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fldConfirmed` tinyint(1) NOT NULL DEFAULT '0',
  `fldApproved` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pkPersonId`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

 * I am using a surrogate key for demonstration, 
 * email would make a good primary key as well which would prevent someone
 * from entering an email address in more than one record.
 */

//-----------------------------------------------------------------------------
// 
// Initialize variables
//  

$debug = 0;
if ($debug) print "<p>DEBUG MODE IS ON</p>";

$baseURL = "https://www.uvm.edu/~apdisant/";
$folderPath = "cs148/assignment5.1/";
// full URL of this form
$yourURL = $baseURL . $folderPath . "register.php";

require_once("connect.php");

//#############################################################################
// set all form variables to their default value on the form. for testing i set
// to my email address. you lose 10% on your grade if you forget to change it.

//$Username = "apdisant";
//$Password = "pass";
$adminemail = "apdisant@uvm.edu";

// $Username = "";
//#############################################################################
// 
// flags for errors

$UsernameERROR = false;

//#############################################################################
//  
$mailed = false;
$messageA = "";
$messageB = "";
$messageC = "";


//-----------------------------------------------------------------------------
// 
// Checking to see if the form's been submitted. if not we just skip this whole 
// section and display the form
// 
//#############################################################################
// minor security check

if(isset($_POST["btnReset"]))
{
//$Username = "apdisant";
//$Password = "pass";
}


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
    //$_SESSION['Username'] = $Username;
    if ($debug) print '<p> sess Username: '.$_SESSION['Username'].'</p>';
    $Password = htmlentities($_POST["txtPassword"], ENT_QUOTES, "UTF-8");
    $HashedPass = md5($Password);
    if ($debug) print '<p> pass: ' .$Password.'</p> <p> hashed: ' .$HashedPass. '</p>';

   
//#############################################################################
// 
// Check for mistakes using validation functions
//
// create array to hold mistakes
// 

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
        $valid = verifyAlphaNum($Username); /* test for non-valid  data */
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

//############################################################################
// 
// Processing the Data of the form
//

    if (!$errorMsg) {
        if ($debug) print "<p>Form is valid</p>";

//############################################################################
//
// the form is valid so now save the information
//    
        $primaryKey = "$Username";
        $dataEntered = false;
        
        try {
            $db->beginTransaction();
           
            $sql = 'INSERT INTO tblUser SET pkUsername="' . $Username . '", ';
            $sql .='fldPassword="' .$HashedPass .'" '; 

            //$sql .= '
            $stmt = $db->prepare($sql);
            if ($debug) print "<p>sql ". $sql;
       
            $stmt->execute();
            
            if ($debug) print "<p>pk= " . $primaryKey;

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

            $sql = "SELECT fldDateJoined FROM tblUser WHERE pkUsername=" . $primaryKey;
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $dateSubmitted = $result["fldDateJoined"];

            $key1 = sha1($dateSubmitted);
            $key2 = $primaryKey;

/*
            print "<p>key 1: " . $key1;
            print "<p>key 2: " . $key2;
*/

            //#################################################################
            //
            //Put forms information into a variable to print on the screen
            //

            $messageA = '<p>New member.</p>';

            $messageB = "<p>Click this link to see ";
            $messageB .= '<a href="' . $baseURL . $folderPath  . 'confirmation.php?q=' . $key1 . '&amp;w=' . $key2 . '">Confirm Registration</a></p>';
            $messageB .= "<p>or copy and paste this url into a web browser: ";
            $messageB .= $baseURL . $folderPath  . 'confirmation.php?q=' . $key1 . '&amp;w=' . $key2 . "</p>";

            $messageC .= "<p><b>User Name:</b><i>   " . $Username . "</i></p>";

            //##############################################################
            //
            // email the form's information
            //
            
            $subject = "You're registered! Thank you!";
            include_once('mailMessage.php');
            $mailed = sendMail($adminemail, $subject, $messageA . $messageB . $messageC);
        } //data entered   
    } // no errors 
}// ends if form was submitted. 
include ("top.php");

    $ext = pathinfo(basename($_SERVER['PHP_SELF']));
    $file_name = basename($_SERVER['PHP_SELF'], '.' . $ext['extension']);

//    print '<body id="' . $file_name . '">';

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
            print "<p>Your Request has ";

            if (!$mailed) {
                echo "not ";
            }

            echo "been processed</p>";

            print "<p>A copy of this message has ";
            if (!$mailed) {
                echo "not ";
            }
            print "been sent to: " . $adminemail . "</p>";

            echo $messageA . $messageC;
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

            <!--   Take out enctype line    -->
            <form action="<? print $_SERVER['PHP_SELF']; ?>"
                  
                  method="post"
                  id="frmRegister">
                <fieldset class="contact">
                    <legend>Contact Information</legend>

                    <label class="required" for="txtUsername">Username </label>

                    <input id ="txtUsername" name="txtUsername" class="element text medium<?php if ($UsernameERROR) echo ' mistake'; ?>" type="text" maxlength="255" value="<?php echo $Username; ?>" placeholder="enter your preferred Username" onfocus="this.select()"  tabindex=1 />
</fieldset>
<fieldset>
      <label for="txtPassword" class="required">Password </label>
      <input type="password" id="txtPassword" name="txtPassword" value="<?php echo $Password; ?>" class="element text medium" placeholder="Password"  onfocus="this.select()" tabindex=2 />
    </fieldset>



<fieldset class="buttons">
                    <input type="hidden" name="redirect" value="form.php">
                    <input type="submit" id="btnSubmit" name="btnSubmit" value="Register" tabindex="3" class="button">
                    <input type="reset" id="butReset" name="butReset" value="Reset Form" tabindex="4" class="button" onclick="reSetForm()" >
                </fieldset>
</form>



            <?php
        } // end body submit
        ?>
    </section>



<?
        include ("footer.php");
?>
