<?php
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
include 'db.php';

// Fetch logged-in learner's name
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name);
$stmt->fetch();
$stmt->close();

// Fetch completed courses for the logged-in user
$completed_stmt = $conn->prepare("SELECT cc.id, cc.course_id, cc.course_title, cc.completed_at, c.description, c.category
FROM completed_courses cc
JOIN courses c ON cc.course_id = c.course_id
WHERE cc.learner_id = ?");
$completed_stmt->bind_param("i", $user_id);
$completed_stmt->execute();
$completed_stmt->bind_result($completed_id, $completed_course_id, $completed_course_title, $completed_at, $course_description, $course_category);

$completed_courses = [];
while ($completed_stmt->fetch()) {
    $completed_courses[] = [
        'id' => $completed_id,
        'course_id' => $completed_course_id,
        'title' => $completed_course_title,
        'completed_at' => $completed_at,
        'description' => $course_description,
        'category' => $course_category
    ];
}
$completed_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Courses</title>
    <link rel="stylesheet" href="CSS/completed_courses_list.css">
</head>

<body>
    <header>
        <h1>Completed Courses</h1>
        <div class="nav">
            <p>Logged in as: <strong><?php echo htmlspecialchars($name); ?></strong></p>
            <a href="dashboard.php">Back to Dashboard</a>
        </div>
    </header>

    <!-- Completed Courses Section -->
    <div class="section">
        <h2>Your Completed Courses</h2>
        <div class="card-container">
            <?php if (!empty($completed_courses)): ?>
            <?php foreach ($completed_courses as $course): ?>
            <div class="card">
                <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                <p><strong>Category:</strong> <?php echo htmlspecialchars($course['category']); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($course['description']); ?></p>
                <p><strong>Completed At:</strong> <?php echo htmlspecialchars($course['completed_at']); ?></p>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p>You have not completed any courses yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>