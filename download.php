<?php 
   header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=".basename($_GET['path']));
   readfile($_GET['path']);
?>

// Local Variables:
// coding: utf-8
// mode: PHP
// End:
