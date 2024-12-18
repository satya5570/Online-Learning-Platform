<?php
session_start();

include("db.php");
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['course_id'])) {
    $course_id = (int)$_GET['course_id'];

    $result = $conn->query("SELECT title FROM courses WHERE course_id = $course_id");
    $course_title = $result->fetch_assoc()['title'] ?? "Learn Programming Basics";
} else {
    $course_title = "Learn Programming Basics";
}

$course_description = $_SESSION['course_description'] ?? "This course will help you understand the basics of programming with practical examples.";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_course'])) {
    $course_title = $conn->real_escape_string($course_title);
    $course_description = $conn->real_escape_string($course_description);
    $conn->query("INSERT INTO courses (title, description) VALUES ('$course_title', '$course_description')");

    if ($conn->affected_rows > 0) {
        echo "Record inserted successfully.";
    } else {
        echo "Error: " . $conn->error;
    }
}

$assignments = [];
$result = $conn->query("SELECT assignment_id, title, description, due_date FROM assignments WHERE course_id = $course_id");
while ($row = $result->fetch_assoc()) {
    $assignments[] = [
        'assignment_id' => $row['assignment_id'],
        'title' => $row['title'],
        'description' => $row['description'],
        'due_date' => $row['due_date']
    ];
}

$quizzes = [];
$result = $conn->query("SELECT quiz_id, title FROM quizzes WHERE course_id = $course_id");
while ($row = $result->fetch_assoc()) {
    $quizzes[] = [
        'quiz_id' => $row['quiz_id'],
        'title' => $row['title']
    ];
}

$questions = [];
foreach ($quizzes as $quiz) {
    $quiz_id = $quiz['quiz_id'];
    $result = $conn->query("SELECT question_id, question, options, correct_option FROM questions WHERE quiz_id = $quiz_id");
    while ($row = $result->fetch_assoc()) {
        $questions[$quiz_id][] = [
            'question_id' => $row['question_id'],
            'question_text' => $row['question'],
            'options' => explode(',', $row['options']),
            'correct_option' => $row['correct_option']
        ];
    }
}

$apiKey = $apik;
$searchQuery = urlencode($course_title . " programming");
$apiUrl = "https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&q=$searchQuery&maxResults=5&key=$apiKey";

$response = file_get_contents($apiUrl);
if ($response === FALSE) {
    die("Error limit crossed! Fetching data from YouTube API.");
}

$searchResults = json_decode($response, true);

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


    <div class="progress-bar-container">
        <div class="progress-bar" id="progressBar"></div>
    </div>

    <div class="container" id="lessonContainer">
        <h2>About this Course</h2>
        <p><?php echo nl2br(htmlspecialchars($course_description)); ?></p>

        <h3>Lessons</h3>
        <?php
       
      

if (!empty($searchResults['items'])) {
    foreach ($searchResults['items'] as $item) {
        $videoId = $item['id']['videoId'];
        $title = $item['snippet']['title'];
        $description = $item['snippet']['description'];
        $thumbnail = $item['snippet']['thumbnails']['high']['url'];


           ?>
        <div class="lesson">
            <h3><?php echo htmlspecialchars($title); ?></h3>
            <p><?php echo nl2br(htmlspecialchars($description)); ?></p>
            <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($videoId); ?>"
                allowfullscreen></iframe>
        </div>
        <hr>
        <?php
           }
        } else {
            echo "No videos found for the query '{$searchQuery}'.";
        }?>



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


    <div class="modal" id="completionModal">
        <div class="modal-content">
            <h2>Congratulations!</h2>
            <p>You've completed the course: <strong><?php echo htmlspecialchars($course_title); ?></strong>.</p>
            <button onclick="redirectToCompletion()">Yes</button>
            <button class="no-btn" onclick="closeModal()">No</button>
        </div>
    </div>

    <script>
    document.addEventListener("scroll", function() {
        const container = document.getElementById("lessonContainer");
        const progressBar = document.getElementById("progressBar");
        const totalHeight = container.scrollHeight - window.innerHeight;
        const scrollPosition = window.scrollY;
        const progress = Math.min((scrollPosition / totalHeight) * 100, 100);
        progressBar.style.width = progress + "%";


        if (progress === 100) {
            document.getElementById("completionModal").style.display = "flex";
        }
    });

    function redirectToCompletion() {

        window.location.href = "course-completion.php?course_title=<?php echo urlencode($course_title); ?>";
    }

    function closeModal() {

        document.getElementById("completionModal").style.display = "none";
    }
    </script>
</body>

</html>