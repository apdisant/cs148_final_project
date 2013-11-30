<?php
$mo=$_REQUEST[mo];
$yr=$_REQUEST[yr];
if (!isset($mo) || $mo<1 || $mo>12 || !is_numeric($mo)) $mo = date("m",time() - date('Z') + ($gmt * 3600));
if (!isset($yr) || $yr<1970 || $yr>2036 || !is_numeric($yr)) $yr = date("Y",time() - date('Z') + ($gmt * 3600));
    ?>
     <form name="form3" method="post" action="<?php echo $PHP_SELF ?>">
        <select name="mo" id="mo">
         <?php
for ($N=1; $N<=12; $N++) {
    echo "<option value='$N'";
    if ($N==$mo) echo " SELECTED";
    echo ">$mth[$N]</option>";
    }
    ?>
        </select>
        <select name="yr" id="yr">
         <?php
for ($N=2000; $N<=2036; $N++) {
    echo "<option";
    if ($N==$yr) echo " SELECTED";
    echo ">$N</option>";
    }
    ?>
        </select>
        <input type="submit" name="Submit" value="Go">
     </form>
