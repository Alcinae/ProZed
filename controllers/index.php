 <?php
if(!$_SESSION["user"]->isInitialized())
    header("Location: login.html");

?>
