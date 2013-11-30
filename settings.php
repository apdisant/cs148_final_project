<?
session_start();
include ("top.php");
include ("header.php");
$debug = 1;
require_once ("connect.php");
   if (!$_SESSION["Username"]){
      print "<p><a href='login.php'>Please login first</a></p>";
      die;
   }

if (isset($_POST["cmdDelete"]))
   {
      print "<p>testing</p>";
      $delID = htmlentities($_POST["deleteID"], ENT_QUOTES);

      $sql = "Delete ";
      $sql .= "FROM tblUser ";
      $sql .= "where pkUsername=" .$delID;

      if ($debug) print "<p>sql " . $sql;
      $stmt = $db->prepare($sql);
      //$DeleteData = $stmt->execute();
}
?>

<form action="<? print $_SERVER['PHP_SELF']; ?>"
         method="post"
         class="accountDelete">
            <fieldset class="accountDelete">
               <input type = "password" id = "txtPassword" name="txtPassword" value="<?php echo $Password; ?>" class="element text medium" placeholder = "enter password to delete account">
               <input type="submit" name="cmdDelete" class="accountDeleteButton" value="x"/>
               <?php print '<input name= "deleteID" type="hidden" id="deleteID" value="' .$_SESSION["Username"]. '"/>';?>
            </fieldset>
         </form>

