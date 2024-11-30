<?php
session_start();

include("db.php");
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Course data (use POST or session for real data in production)
// Check if course_id is passed in the GET request
if (isset($_GET['course_id'])) {
    $course_id = (int) $_GET['course_id']; // Cast to integer for safety

    // Query to fetch the course title from the database
    $stmt = $conn->prepare("SELECT title FROM courses WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->bind_result($course_title);
    $stmt->fetch();
    $stmt->close();

    // If no course is found, use a default title
    if (!$course_title) {
        $course_title = "Learn Programming Basics";
    }
} else {
    // Default title if no course_id is provided in the GET request
    $course_title = "Learn Programming Basics";
}

$course_description = isset($_SESSION['course_description']) ? $_SESSION['course_description'] : "This course will help you understand the basics of programming with practical examples.";

// Insert course data into the database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_course'])) {
    $stmt = $conn->prepare("INSERT INTO courses (title, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $course_title, $course_description);

    if ($stmt->execute()) {
        echo "Record inserted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch Assignments
$assignments = [];
$assignment_query = $conn->prepare("SELECT assignment_id, title, description, due_date FROM assignments WHERE course_id = ?");
$assignment_query->bind_param("i", $course_id);
$assignment_query->execute();
$assignment_query->bind_result($assignment_id, $assignment_title, $assignment_description, $assignment_due_date);
while ($assignment_query->fetch()) {
    $assignments[] = [
        'assignment_id' => $assignment_id,
        'title' => $assignment_title,
        'description' => $assignment_description,
        'due_date' => $assignment_due_date
    ];
}
$assignment_query->close();

// Fetch Quizzes
$quizzes = [];
$quiz_query = $conn->prepare("SELECT quiz_id, title FROM quizzes WHERE course_id = ?");
$quiz_query->bind_param("i", $course_id);
$quiz_query->execute();
$quiz_query->bind_result($quiz_id, $quiz_title);
while ($quiz_query->fetch()) {
    $quizzes[] = [
        'quiz_id' => $quiz_id,
        'title' => $quiz_title
    ];
}
$quiz_query->close();

// Fetch Questions for each Quiz
$questions = [];
foreach ($quizzes as $quiz) {
    $quiz_id = $quiz['quiz_id'];
    $question_query = $conn->prepare("SELECT question_id, question, options, correct_option FROM questions WHERE quiz_id = ?");
    $question_query->bind_param("i", $quiz_id);
    $question_query->execute();
    $question_query->bind_result($question_id, $question_text, $options, $correct_option);

    $quiz_questions = [];
    while ($question_query->fetch()) {
        $quiz_questions[] = [
            'question_id' => $question_id,
            'question_text' => $question_text,
            'options' => explode(',', $options), // Assuming options are stored as comma-separated values
            'correct_option' => $correct_option
        ];
    }
    $questions[$quiz_id] = $quiz_questions;
    $question_query->close();
}

// Hardcoded lessons for demonstration
$lessons = [
    [
        'title' => 'Introduction to Programming',
        'content' => 'Learn the basics of programming, including what programming is and why it matters.',
        'video_url' => 'https://www.youtube.com/embed/TXQQUQH3U7A'
    ],
    [
        'title' => 'Variables and Data Types',
        'content' => 'Understand variables, data types, and how they are used in programming.',
        'video_url' => 'https://www.youtube.com/embed/Q1dke5HtrWU'
    ],
    [
        'title' => 'Introduction to Programming',
        'content' => 'Learn the basics of programming, including what programming is and why it matters.',
        'video_url' => 'https://www.youtube.com/embed/TXQQUQH3U7A'
    ],
    [
        'title' => 'Variables and Data Types',
        'content' => 'Understand variables, data types, and how they are used in programming.',
        'video_url' => 'https://www.youtube.com/embed/Q1dke5HtrWU'
    ],
    [
        'title' => 'Introduction to Programming',
        'content' => 'Learn the basics of programming, including what programming is and why it matters.',
        'video_url' => 'https://www.youtube.com/embed/TXQQUQH3U7A'
    ],
    [
        'title' => 'Variables and Data Types',
        'content' => 'Understand variables, data types, and how they are used in programming.',
        'video_url' => 'https://www.youtube.com/embed/Q1dke5HtrWU'
    ],
    // More lessons here...
];

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start Learning: <?php echo htmlspecialchars($course_title); ?></title>
    <link rel="stylesheet" href="CSS/start-learning.css">
</head>

<body>
    <header>
        <h1>Start Learning: <?php echo htmlspecialchars($course_title); ?></h1>
    </header>

    <!-- Progress Bar -->
    <div class="progress-bar-container">
        <div class="progress-bar" id="progressBar"></div>
    </div>

    <div class="container" id="lessonContainer">
        <h2>About this Course</h2>
        <p><?php echo nl2br(htmlspecialchars($course_description)); ?></p>

        <h3>Lessons</h3>
        <?php foreach ($lessons as $lesson): ?>
        <div class="lesson">
            <h3><?php echo htmlspecialchars($lesson['title']); ?></h3>
            <p><?php echo nl2br(htmlspecialchars($lesson['content'])); ?></p>
            <iframe src="<?php echo htmlspecialchars($lesson['video_url']); ?>" allowfullscreen></iframe>
        </div>
        <?php endforeach; ?>

        <h3>Assignments</h3>
        <?php if (!empty($assignments)): ?>
        <?php foreach ($assignments as $assignment): ?>
        <div class="assignment">
            <h3><?php echo htmlspecialchars($assignment['title']); ?></h3>
            <p><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
            <p>Due Date: <?php echo htmlspecialchars($assignment['due_date']); ?></p>
            <a href="assignment-details.php?assignment_id=<?php echo $assignment['assignment_id']; ?>">View Assignment
                &raquo;</a>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <p>No assignments available for this course.</p>
        <?php endif; ?>

        <h3>Quizzes</h3>
        <?php if (!empty($quizzes)): ?>
        <?php foreach ($quizzes as $quiz): ?>
        <div class="quiz">
            <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
            <ul>
                <?php if (!empty($questions[$quiz['quiz_id']])): ?>
                <?php foreach ($questions[$quiz['quiz_id']] as $question): ?>
                <li>
                    <strong><?php echo htmlspecialchars($question['question_text']); ?></strong>
                    <ul>
                        <?php foreach ($question['options'] as $option): ?>
                        <li><?php echo htmlspecialchars($option); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <?php endforeach; ?>
                <?php else: ?>
                <li>No questions available for this quiz.</li>
                <?php endif; ?>
            </ul>
            <a href="quiz-details.php?quiz_id=<?php echo $quiz['quiz_id']; ?>">Attempt Quiz &raquo;</a>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <p>No quizzes available for this course.</p>
        <?php endif; ?>

        <a class="back-link" href="dashboard.php">&laquo; Back to Dashboard</a>
    </div>

    <!-- Modal -->
    <div class="modal" id="completionModal">
        <div class="modal-content">
            <h2>Congratulations!</h2>
            <p>You've completed the course: <strong><?php echo htmlspecialchars($course_title); ?></strong>.</p>
            <button onclick="redirectToCompletion()">Yes</button>
            <button class="no-btn" onclick="closeModal()">No</button>
        </div>
    </div>

    <script>
    // JavaScript to handle progress bar and modal
    document.addEventListener("scroll", function() {
        const container = document.getElementById("lessonContainer");
        const progressBar = document.getElementById("progressBar");
        const totalHeight = container.scrollHeight - window.innerHeight;
        const scrollPosition = window.scrollY;
        const progress = Math.min((scrollPosition / totalHeight) * 100, 100);
        progressBar.style.width = progress + "%";

        // Show modal when progress is 100%
        if (progress === 100) {
            document.getElementById("completionModal").style.display = "flex";
        }
    });

    function redirectToCompletion() {
        // Redirect to course completion page
        window.location.href = "course-completion.php?course_title=<?php echo urlencode($course_title); ?>";
    }

    function closeModal() {
        // Close the modal
        document.getElementById("completionModal").style.display = "none";
    }
    </script>
</body>

</html>