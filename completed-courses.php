<?php
session_start();
include 'db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$learner_id = $_SESSION['user_id'];

$sql = "
    SELECT course_title, completed_at 
    FROM completed_courses 
    WHERE learner_id = $learner_id 
    ORDER BY completed_at DESC
";

$result = $conn->query($sql);

$completed_courses = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $completed_courses[] = [
            'title' => $row['course_title'],
            'completed_at' => $row['completed_at']
        ];
    }
} else {
    echo "Error: " . $conn->error;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Courses</title>
    <link rel="stylesheet" href="CSS/completed-courses.css">
</head>

<body>
    <div class="container">
        <h1>Completed Courses</h1>
        <?php if (count($completed_courses) > 0): ?>
        <?php foreach ($completed_courses as $course): ?>
        <div class="course">
            <h2><?php echo htmlspecialchars($course['title']); ?></h2>
            <span>Completed on: <?php echo htmlspecialchars($course['completed_at']); ?></span>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <p>No courses completed yet.</p>
        <?php endif; ?>
        <a class="back-link" href="dashboard.php">Back to Dashboard</a>
    </div>
</body>

</html>