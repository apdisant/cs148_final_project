<?php
   session_start();
   include ("top.php");
   $debug = 0;
   $baseURL = "https://www.uvm.edu/~apdisant/";
   $folderPath = "cs148/assignment5.1/";
   // full URL of this form
   $yourURL = $baseURL . $folderPath . "notes.php";
   require_once ("connect.php");
?>
<p id = "add"> Add a note:
   <a href ="add.php">Add</a>
</p>
<ol id = "MyNotes">
<?
##############################################################################
//selects all notes to me from table
   $sql = 'Select fkNoteID ';
   $sql .= 'from tblNoteToUser ';
   $sql .= 'where fkToUsername = "' .$_SESSION["Username"]. '"';
   if ($debug) print "<p>sql " .$sql;

   $stmt = $db->prepare($sql);
   
   $stmt->execute();

   $NotesToMe = $stmt->fetchAll();
   if($debug){ print "<pre>"; print_r($NotesToMe); print "</pre>";}

   foreach ($NotesToMe as $NTM) 
       {
       $sql = 'select fkFromUsername, fldToUsername, fldMessage, fldTimePosted,fldDeadline ';
       $sql .= 'from tblNote ';
       $sql .= 'where pkNoteID = "' .$NTM['fkNoteID']. '"';
       if ($debug) print "<p>sql " .$sql;

       $stmt = $db->prepare($sql);

       $stmt->execute();

       $NotesFull = $stmt->fetchAll();
       if($debug){ print "<pre>"; print_r($NotesFull); print "</pre>";}


       foreach ($NotesFull as $NF) 
       {
         print '<li class="note"><a href="#"> From: ' .$NF['fkFromUsername']. '';
         print '<p>' .$NF['fldMessage']. '</p></li>';
       }
       }
print '</ol>'
//include ("footer.php");
?>
