<?php
require 'auth.php';

$title = trim($_POST['title']);
$desc = trim($_POST['description']);
$deadline = $_POST['deadline'] ?: null;
$priority = $_POST['priority'] ?? 'Medium';

$stmt = $mysqli->prepare("INSERT INTO tasks (user_id, title, description, deadline, priority) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $_SESSION['user_id'], $title, $desc, $deadline, $priority);
$stmt->execute();

header("Location: index.php?added=1");
exit;
