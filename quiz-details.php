<?php
session_start();

include("db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['quiz_id'])) {
    $quiz_id = (int)$_GET['quiz_id']; 

    $sql = "SELECT title FROM quizzes WHERE quiz_id = $quiz_id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $quiz_title = $row['title'];
    } else {
        echo "Quiz not found.";
        exit;
    }

    $questions = [];
    $question_sql = "SELECT question_id, question, options FROM questions WHERE quiz_id = $quiz_id";
    $result = $conn->query($question_sql);
    
    while ($row = $result->fetch_assoc()) {
        $questions[] = [
            'question_id' => $row['question_id'],
            'question_text' => $row['question'],
            'options' => explode(',', $row['options'])
        ];
    }
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