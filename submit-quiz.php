<?php
session_start();

include("db.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if quiz_id and answers are provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quiz_id'])) {
    $quiz_id = (int) $_POST['quiz_id'];

    // Loop through the questions and save the answers
    foreach ($_POST as $key => $answer) {
        if (strpos($key, 'question_') === 0) {
            $question_id = (int) substr($key, 9); // Extract question_id from the key
            $stmt = $conn->prepare("INSERT INTO quiz_answers (user_id, quiz_id, question_id, answer) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $_SESSION['user_id'], $quiz_id, $question_id, $answer);
            $stmt->execute();
            $stmt->close();
        }
    }

    echo "Quiz submitted successfully.";
} else {
    echo "Quiz ID or answers missing.";
}

$conn->close();
?>