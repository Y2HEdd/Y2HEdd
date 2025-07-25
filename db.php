<?php
$mysqli = new mysqli("localhost", "root", "", "todo_app");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
session_start();
?>
