<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get user and course information
$learner_id = $_SESSION['user_id'];
$course_title = isset($_GET['course_title']) ? $_GET['course_title'] : null;

// Debugging: Check if the course title is provided
if (!$course_title) {
    die("Error: Course title is missing.");
}

// Fetch the course_id based on course_title
$stmt = $conn->prepare("SELECT course_id FROM courses WHERE title = ?");
$stmt->bind_param("s", $course_title);
$stmt->execute();
$stmt->bind_result($course_id);
$stmt->fetch();
$stmt->close();

// Debugging: Check if the course_id was retrieved
if (!$course_id) {
    die("Error: Course not found in the database.");
}

// Check if the course is already marked as completed
$check_stmt = $conn->prepare("SELECT id FROM completed_courses WHERE learner_id = ? AND course_id = ?");
$check_stmt->bind_param("ii", $learner_id, $course_id);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows === 0) {
    // Insert record into completed_courses
    $insert_stmt = $conn->prepare("INSERT INTO completed_courses (learner_id, course_id, course_title) VALUES (?, ?,?)");
    if ($insert_stmt) {
        $insert_stmt->bind_param("iis", $learner_id, $course_id, $course_title);
        if ($insert_stmt->execute()) {
            // Debugging: Confirmation of insertion
            error_log("Course completion successfully recorded for learner_id: $learner_id and course_id: $course_id");
        } else {
            // Debugging: Log any SQL error
            error_log("Error inserting completed course: " . $insert_stmt->error);
        }
        $insert_stmt->close();
    } else {
        // Debugging: Log any error in the prepare statement
        error_log("Error preparing statement for course completion.");
    }
} else {
    // Debugging: Log if the course is already completed
    error_log("Course already marked as completed for learner_id: $learner_id and course_id: $course_id");
}
$check_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Completion</title>
    <link rel="stylesheet" href="CSS/course-completion.css">
</head>

<body>
    <div class="container">
        <h1>Congratulations!</h1>
        <p>You have successfully completed <strong><?php echo htmlspecialchars($course_title); ?></strong>.</p>
        <a class="btn" href="completed-courses.php">View Completed Courses</a>
        <a class="btn" href="dashboard.php">Go to Dashboard</a>
    </div>
</body>

</html>