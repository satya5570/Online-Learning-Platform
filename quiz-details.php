<?php
session_start();

include("db.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch the quiz details based on the quiz_id from the URL
if (isset($_GET['quiz_id'])) {
    $quiz_id = (int) $_GET['quiz_id']; // Cast to integer for safety

    // Query to fetch the quiz title from the database
    $stmt = $conn->prepare("SELECT title FROM quizzes WHERE quiz_id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $stmt->bind_result($quiz_title);
    $stmt->fetch();
    $stmt->close();

    // If no quiz is found, display an error
    if (!$quiz_title) {
        echo "Quiz not found.";
        exit;
    }

    // Fetch the questions for this quiz
    $questions = [];
    $question_query = $conn->prepare("SELECT question_id, question, options FROM questions WHERE quiz_id = ?");
    $question_query->bind_param("i", $quiz_id);
    $question_query->execute();
    $question_query->bind_result($question_id, $question_text, $options);
    while ($question_query->fetch()) {
        $questions[] = [
            'question_id' => $question_id,
            'question_text' => $question_text,
            'options' => explode(',', $options) // Assuming options are stored as comma-separated values
        ];
    }
    $question_query->close();
} else {
    echo "Quiz ID is missing.";
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz: <?php echo htmlspecialchars($quiz_title); ?></title>
    <link rel="stylesheet" href="CSS/start-learning.css">
</head>

<body>
    <header>
        <h1>Quiz: <?php echo htmlspecialchars($quiz_title); ?></h1>
    </header>

    <div class="container">
        <h2>Quiz Questions</h2>
        <form action="submit-quiz.php" method="post">
            <?php foreach ($questions as $question): ?>
            <div class="question">
                <p><strong><?php echo htmlspecialchars($question['question_text']); ?></strong></p>
                <ul>
                    <?php foreach ($question['options'] as $option): ?>
                    <li>
                        <label>
                            <input type="radio" name="question_<?php echo $question['question_id']; ?>"
                                value="<?php echo htmlspecialchars($option); ?>">
                            <?php echo htmlspecialchars($option); ?>
                        </label>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>

            <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
            <button type="submit">Submit Quiz</button>
        </form>

        <a class="back-link"
            href="start-learning.php?course_id=<?php echo isset($_GET['course_id']) ? $_GET['course_id'] : ''; ?>">&laquo;
            Back to Course</a>

    </div>
</body>

</html>