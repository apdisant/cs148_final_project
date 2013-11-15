<?php
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

$email = "apdisant@uvm.edu";
$Username = "apdisant";
$FirstName = "Alex";
$LastName = "DiSanto";
$Gender = "Male";
$CurrentGrade = "Sophomore";


// $email = "";
//#############################################################################
// 
// flags for errors

$emailERROR = false;


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
$email = "apdisant@uvm.edu";
$Username = "apdisant";
$FirstName = "Alex";
$LastName = "DiSanto";
$Gender = "Male";
$CurrentGrade = "Sophomore";
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

    $email = htmlentities($_POST["txtEmail"], ENT_QUOTES, "UTF-8");
    $Username = htmlentities($_POST["Username"], ENT_QUOTES, "UTF-8");
    $FirstName = htmlentities($_POST["FirstName"], ENT_QUOTES, "UTF-8");
    $LastName = htmlentities($_POST["LastName"], ENT_QUOTES, "UTF-8");
    $Gender = htmlentities($_POST["radGender"], ENT_QUOTES, "UTF-8");
    $CurrentGrade = htmlentities($_POST["lstGrade"], ENT_QUOTES, "UTF-8");

   
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
    if (empty($email)) {
        $errorMsg[] = "Please enter your Email Address";
        $emailERROR = true;
    } else {
        $valid = verifyEmail($email); /* test for non-valid  data */
        if (!$valid) {
            $errorMsg[] = "I'm sorry, the email address you entered is not valid.";
            $emailERROR = true;
        }
    }
        $valid = verifyAlphaNum($Username);  /*test for non-valid  data*/ 
        if (!$valid) {
            $errorMsg[] = "I'm sorry, the username you entered is not valid.";
                     }
        $valid = verifyAlphaNum($FirstName);  /*test for non-valid  data*/ 
        if (!$valid) {
            $errorMsg[] = "I'm sorry, the username you entered is not valid.";
                     }
        $valid = verifyAlphaNum($LastName);  /*test for non-valid  data*/ 
        if (!$valid) {
            $errorMsg[] = "I'm sorry, the username you entered is not valid.";
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
           
            $sql = 'INSERT INTO tblStudent SET fldEmail="' . $email . '", ';
            $sql .= 'pkUsername="' . $Username . '",';
            $sql .= 'fldFirstName="' .$FirstName . '",';
            $sql .= 'fldLastName="' .$LastName . '",';
            $sql .= 'fldGender="' .$Gender . '",';
            $sql .= 'fldCurrentYear="' .$CurrentGrade . '"';

            //$sql .= '
            $stmt = $db->prepare($sql);
            if ($debug) print "<p>sql ". $sql;
       
            $stmt->execute();
            
            $primaryKey = $db->lastInsertId();
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

            $sql = "SELECT fldDateJoined FROM tblRegister WHERE pkRegisterId=" . $primaryKey;
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

            $messageA = '<h2>Thank you for registering.</h2>';

            $messageB = "<p>Click this link to confirm your registration: ";
            $messageB .= '<a href="' . $baseURL . $folderPath  . 'confirmation.php?q=' . $key1 . '&amp;w=' . $key2 . '">Confirm Registration</a></p>';
            $messageB .= "<p>or copy and paste this url into a web browser: ";
            $messageB .= $baseURL . $folderPath  . 'confirmation.php?q=' . $key1 . '&amp;w=' . $key2 . "</p>";

            $messageC .= "<p><b>Email Address:</b><i>   " . $email . "</i></p>";
            $messageC .= "<p><b>User Name:</b><i>   " . $Username . "</i></p>";
            $messageC .= "<p><b>Name:</b><i>   " . $FirstName . ' ' . $LastName . "</i></p>";

            //##############################################################
            //
            // email the form's information
            //
            
            $subject = "You're registered! After you confirm.";
            include_once('mailMessage.php');
            $mailed = sendMail($email, $subject, $messageA . $messageB . $messageC);
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
        <h1>Register </h1>

        <?
//############################################################################
//
//  In this block  display the information that was submitted and do not 
//  display the form.
//
        if (isset($_POST["btnSubmit"]) AND empty($errorMsg)) {
            print "<h2>Your Request has ";

            if (!$mailed) {
                echo "not ";
            }

            echo "been processed</h2>";

            print "<p>A copy of this message has ";
            if (!$mailed) {
                echo "not ";
            }
            print "been sent to: " . $email . "</p>";

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

                    <label class="required" for="txtEmail">Email </label>

                    <input id ="txtEmail" name="txtEmail" class="element text medium<?php if ($emailERROR) echo ' mistake'; ?>" type="text" maxlength="255" value="<?php echo $email; ?>" placeholder="enter your preferred email address" onfocus="this.select()"  tabindex="30"/>

                </fieldset> 
<fieldset>
      <label for="Username" class="required">Username</label>
      <input type="text" maxlength="255" id="Username" name="Username" class="element text medium" value="<?php echo $Username; ?>" placeholder="Username" onfocus="this.select()" />
   </fieldset>
   <fieldset>
      <label for="FirstName" class="required">First Name </label>
      <input type="text" id="FirstName" name="FirstName" value="<?php echo $FirstName; ?>" class="element text medium" placeholder="FirstName"  onfocus="this.select()" />
    </fieldset>
   <fieldset>
      <label for="LastName" class="required">Last Name </label>
      <input type="text" id="LastName" name="LastName" value="<?php echo $LastName; ?>" placeholder="LastName"  onfocus="this.select()" />
    </fieldset>
    <fieldset class="radio">
    <legend>What is your gender?</legend>
    <label><input type="radio" id="radGenderMale" name="radGender" value="Male" tabindex="231" 
	<?php if($Gender=="Male") echo ' checked="checked" ';?>>Male</label>
            
	<label><input type="radio" id="radGenderFemale" name="radGender" value="Female" tabindex="233" 
	<?php if($Gender=="Female") echo ' checked="checked" ';?>>Female</label>
</fieldset>

<fieldset class="lists">	
	<legend>What is your Current Grade</legend>
	<select id="lstGrade" name="lstGrade" tabindex="281" size="1">
		<option value="Freshman" <?php if($CurrentGrade=="Freshman") echo ' selected="selected" ';?>>Freshman</option>
		<option value="Sophomore" <?php if($CurrentGrade=="Sophomore") echo ' selected="selected" ';?>>Sophomore</option>
		<option value="Junior" <?php if($CurrentGrade=="Junior") echo ' selected="selected" ';?>>Junior</option>
		<option value="Senior" <?php if($CurrentGrade=="Senior") echo ' selected="selected" ';?>>Senior</option>
	</select>
</fieldset>


                <fieldset class="buttons">
                    <input type="hidden" name="redirect" value="form.php">
                    <input type="submit" id="btnSubmit" name="btnSubmit" value="Register" tabindex="991" class="button"> 
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
