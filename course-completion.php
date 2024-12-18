<?php
session_start();
include 'db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}


$learner_id = $_SESSION['user_id'];
$course_title = isset($_GET['course_title']) ? $_GET['course_title'] : null;


if (!$course_title) {
    die("Error: Course title is missing.");
}


$course_title = $conn->real_escape_string($course_title); 
$sql = "SELECT course_id FROM courses WHERE title = '$course_title'";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $course_id = $row['course_id'];
} else {
    echo "Error or no results found: " . $conn->error;
}


if (!$course_id) {
    die("Error: Course not found in the database.");
}


$learner_id = intval($learner_id);
$course_id = intval($course_id);
$course_title = $conn->real_escape_string($course_title);


$sql_check = "SELECT id FROM completed_courses WHERE learner_id = $learner_id AND course_id = $course_id";
$result_check = $conn->query($sql_check);

if ($result_check && $result_check->num_rows === 0) {
   
    $sql_insert = "
        INSERT INTO completed_courses (learner_id, course_id, course_title) 
        VALUES ($learner_id, $course_id, '$course_title')
    ";
    if ($conn->query($sql_insert) === TRUE) {
        error_log("Course completion successfully recorded for learner_id: $learner_id and course_id: $course_id");
    } else {
        error_log("Error inserting completed course: " . $conn->error);
    }
} else {
    error_log("Course already marked as completed for learner_id: $learner_id and course_id: $course_id");
}

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