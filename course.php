<?php
session_start();
include 'db.php';

// Get the course_id from the URL
if (!isset($_GET['course_id'])) {
    header("Location: index.php");
    exit;
}

$course_id = intval($_GET['course_id']);

// Fetch course details
$stmt = $conn->prepare("SELECT title, description, category, creator_id FROM courses WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$stmt->bind_result($course_title, $course_description, $course_category, $course_creator_id);
$stmt->fetch();
$stmt->close();

// Fetch creator name
$creator_stmt = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
$creator_stmt->bind_param("i", $course_creator_id);
$creator_stmt->execute();
$creator_stmt->bind_result($creator_name);
$creator_stmt->fetch();
$creator_stmt->close();

// Check if the user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$is_enrolled = false;

if ($is_logged_in) {
    // Check if the logged-in learner is enrolled in this course
    $user_id = $_SESSION['user_id'];
    $enrollment_stmt = $conn->prepare("SELECT * FROM enrollments WHERE learner_id = ? AND course_id = ?");
    $enrollment_stmt->bind_param("ii", $user_id, $course_id);
    $enrollment_stmt->execute();
    $enrollment_stmt->store_result();
    $is_enrolled = $enrollment_stmt->num_rows > 0;
    $enrollment_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course_title); ?></title>
    <link rel="stylesheet" href="CSS/course.css">
</head>

<body>
    <header>
        <h1><?php echo htmlspecialchars($course_title); ?></h1>
    </header>

    <div class="container">
        <h2>About this Course</h2>
        <p><?php echo htmlspecialchars($course_description); ?></p>
        <p><strong>Category:</strong> <?php echo htmlspecialchars($course_category); ?></p>
        <p><strong>Created by:</strong> <?php echo htmlspecialchars($creator_name); ?></p>

        <?php if ($is_logged_in && $is_enrolled): ?>
        <!-- Show "Start Learning" for enrolled users -->
        <a class="action-button" href="start-learning.php?course_id=<?php echo $course_id; ?>">Start Learning</a>
        <?php elseif ($is_logged_in): ?>
        <!-- Show "Enroll Now" for logged-in users -->
        <a class="action-button" href="enroll.php?course_id=<?php echo $course_id; ?>">Enroll Now</a>
        <?php else: ?>
        <!-- Show "Enroll Now" for guests -->
        <a class="action-button" href="login.php">Enroll Now</a>
        <?php endif; ?>
    </div>

    <!-- Course Content Section -->
    <div class="course-content">
        <h3>What You'll Learn</h3>
        <p>Explore the topics covered in this course:</p>
        <ul>
            <li>Introduction to the subject</li>
            <li>Key concepts and practical examples</li>
            <li>Interactive quizzes and assignments</li>
            <li>Real-world applications of the material</li>
        </ul>
    </div>

    <a class="back-link" href="index.php">&laquo; Back to Courses</a>
</body>

</html>