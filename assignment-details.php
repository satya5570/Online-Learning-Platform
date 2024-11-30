<?php
session_start();

include("db.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch the assignment details based on the assignment_id from the URL
if (isset($_GET['assignment_id'])) {
    $assignment_id = (int) $_GET['assignment_id']; // Cast to integer for safety

    // Query to fetch the assignment details from the database
    $stmt = $conn->prepare("SELECT title, description, due_date FROM assignments WHERE assignment_id = ?");
    $stmt->bind_param("i", $assignment_id);
    $stmt->execute();
    $stmt->bind_result($assignment_title, $assignment_description, $assignment_due_date);
    $stmt->fetch();
    $stmt->close();

    // If no assignment is found, display an error
    if (!$assignment_title) {
        echo "Assignment not found.";
        exit;
    }
} else {
    echo "Assignment ID is missing.";
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment: <?php echo htmlspecialchars($assignment_title); ?></title>
    <link rel="stylesheet" href="CSS/start-learning.css">
</head>

<body>
    <header>
        <h1>Assignment: <?php echo htmlspecialchars($assignment_title); ?></h1>
    </header>

    <div class="container">
        <h2>Assignment Details</h2>
        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($assignment_description)); ?></p>
        <p><strong>Due Date:</strong> <?php echo htmlspecialchars($assignment_due_date); ?></p>

        <!-- Example of an option to upload the assignment -->
        <form action="submit-assignment.php" method="post" enctype="multipart/form-data">
            <label for="assignment_file">Upload Assignment File:</label>
            <input type="file" name="assignment_file" id="assignment_file" required>
            <input type="hidden" name="assignment_id" value="<?php echo $assignment_id; ?>">
            <button type="submit">Submit Assignment</button>
        </form>

        <a class="back-link"
            href="start-learning.php?course_id=<?php echo isset($_GET['course_id']) ? $_GET['course_id'] : ''; ?>">&laquo;
            Back to Course</a>

    </div>
</body>

</html>