<?php
if(!headers_sent()) {
  header("Content-Type: application/json; charset=utf-8");
}
?>
<?php echo json_encode($json); ?>
