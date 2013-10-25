<?php 
$dir="jsdata/";
if (isset($_GET['path'])) {
  $month = basename(dirname($_GET['path'])) ;
  $file = $dir . $month . '/' . basename($_GET['path']) ;
  header("Content-Type: application/octet-stream");
  header("Content-Disposition: attachment; filename=$file");
  readfile($file);
 }

// Local Variables:
// coding: utf-8
// mode: PHP
// End:
?>
