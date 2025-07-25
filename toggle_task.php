<?php
require 'auth.php';
$id = (int) $_GET['id'];
$stmt = $mysqli->prepare("UPDATE tasks SET is_done = NOT is_done WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
header("Location: index.php");
