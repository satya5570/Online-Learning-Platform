<?php
session_start();
include 'db.php';


if (!isset($_GET['course_id'])) {
    header("Location: index.php");
    exit;
}

$course_id = intval($_GET['course_id']);

$course_id = intval($course_id);


$sql_course = "SELECT title, description, category, creator_id FROM courses WHERE course_id = $course_id";
$result_course = $conn->query($sql_course);

if ($result_course && $row_course = $result_course->fetch_assoc()) {
    $course_title = $row_course['title'];
    $course_description = $row_course['description'];
    $course_category = $row_course['category'];
    $course_creator_id = $row_course['creator_id'];
} else {
    die("Course not found or error: " . $conn->error);
}

$course_creator_id = intval($course_creator_id); 
$sql_creator = "SELECT name FROM users WHERE user_id = $course_creator_id";
$result_creator = $conn->query($sql_creator);

if ($result_creator && $row_creator = $result_creator->fetch_assoc()) {
    $creator_name = $row_creator['name'];
} else {
    die("Creator not found or error: " . $conn->error);
}


$is_logged_in = isset($_SESSION['user_id']);
$is_enrolled = false;

if ($is_logged_in) {
    $user_id = intval($_SESSION['user_id']); 
    $sql_enrollment = "SELECT * FROM enrollments WHERE learner_id = $user_id AND course_id = $course_id";
    $result_enrollment = $conn->query($sql_enrollment);

    if ($result_enrollment && $result_enrollment->num_rows > 0) {
        $is_enrolled = true;
    }
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

        <a class="action-button" href="start-learning.php?course_id=<?php echo $course_id; ?>">Start Learning</a>
        <?php elseif ($is_logged_in): ?>

        <a class="action-button" href="enroll.php?course_id=<?php echo $course_id; ?>">Enroll Now</a>
        <?php else: ?>

        <a class="action-button" href="login.php">Enroll Now</a>
        <?php endif; ?>
    </div>


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