<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}


include 'db.php';


$user_id = intval($_SESSION['user_id']); 
$sql = "SELECT name FROM users WHERE user_id = $user_id";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $name = $row['name'];
} else {
    echo "Error or no results found: " . $conn->error;
}


$user_id = $_SESSION['user_id']; 

$completed_courses_query = "SELECT cc.id, cc.course_id, cc.course_title, cc.completed_at, c.description, c.category 
                            FROM completed_courses cc 
                            JOIN courses c ON cc.course_id = c.course_id 
                            WHERE cc.learner_id = $user_id";

$result = $conn->query($completed_courses_query);

$completed_courses = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $completed_courses[] = [
            'id' => $row['id'],
            'course_id' => $row['course_id'],
            'title' => $row['course_title'],
            'completed_at' => $row['completed_at'],
            'description' => $row['description'],
            'category' => $row['category']
        ];
    }
}

$conn->close();

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