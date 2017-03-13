<?php
   include_once 'header.php';
 ?>
<form method="POST" enctype="multipart/form-data" action="/vocabulary/add">
    <input type="text" name="type">
    <input type="file" name="file">
    <input type="submit">
</form>

<form method="POST" enctype="multipart/form-data" action="/vocabulary/check">
    <input type="text" name="type">
    <textarea name="text"></textarea>
    <input type="submit">
</form>
