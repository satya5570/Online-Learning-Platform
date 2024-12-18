<?php
session_start();

include("db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quiz_id'])) {
    $quiz_id = (int)$_POST['quiz_id'];

    foreach ($_POST as $key => $answer) {
        if (strpos($key, 'question_') === 0) {
            $question_id = (int)substr($key, 9);
            $query = "INSERT INTO quiz_answers (user_id, quiz_id, question_id, answer) VALUES (" . $_SESSION['user_id'] . ", $quiz_id, $question_id, '$answer')";
            $conn->query($query);
        }
    }

    echo "Quiz submitted successfully.";
} else {
    echo "Quiz ID or answers missing.";
}

$conn->close();

?>