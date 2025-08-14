<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../config/config.php';
$user_id = $_SESSION['user_id'];
$sql = "SELECT id, subject, message, status FROM complaints WHERE user_id = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$toast = '';
if (isset($_SESSION['toast'])) {
    $toast = $_SESSION['toast'];
    unset($_SESSION['toast']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include '../config/config.php';

    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    if ($subject && $message) {
        $stmt = $conn->prepare("INSERT INTO complaints (user_id, subject, message, status) VALUES (?, ?, ?, 'Pending')");
        $stmt->bind_param("iss", $_SESSION['user_id'], $subject, $message);
    

        if ($stmt->execute()) {
            $_SESSION['toast'] = 'Complaint submitted successfully!';
            header("Location: index.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        $toast = 'Please fill in all fields.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Complaints Portal</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <?php if (!empty($toast)): ?>
        <div id="toast"><?php echo htmlspecialchars($toast); ?></div>
    <?php endif; ?>
    <header>
        <div class="nav-container">
            <h1>Student Complaints Portal</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="container">
        <p class="page-description">
            Submit your concerns and track their resolution status. Our team is committed to addressing your issues promptly and efficiently.
        </p>

        <div class="main-grid">
            <div class="form-card">
                <h2>Submit a New Complaint</h2>
                <form id="complaintForm" autocomplete="off" method="POST">
                    <div class="form-group">
                        <label for="subject">Complaint Title</label>
                        <input type="text" id="subject" name="subject" placeholder="Briefly describe your concern" required>
                    </div>
                    
                    <!-- <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" required>
                            <option value="">Select a category</option>
                            <option value="facilities">Facilities & Infrastructure</option>
                            <option value="academic">Academic Issues</option>
                            <option value="administrative">Administrative</option>
                            <option value="safety">Safety Concerns</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="priority">Priority Level</label>
                        <select id="priority" required>
                            <option value="">Select priority</option>
                            <option value="high">High (Urgent attention needed)</option>
                            <option value="medium">Medium (Important but not urgent)</option>
                            <option value="low">Low (General feedback/concern)</option>
                        </select>
                    </div> -->
                    
                    <div class="form-group">
                        <label for="message">Complaint Details</label>
                        <textarea id="message" name="message" placeholder="Please provide a detailed description of your complaint..." required></textarea>
                    </div>
                    
                    <button type="submit">Submit Complaint</button>
                </form>
            </div>
            
            <div class="card">
                <h2>Your Complaints</h2>
                <div class="complaint-list" id="complaintsList">
                    <?php if ($result->num_rows === 0): ?>
                        <div class="no-complaints">You haven't submitted any complaints yet.</div>
                    <?php else: ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                                // Format date (if you have a date column, otherwise remove this)
                                // $dateObj = new DateTime($row['created_at']);
                                // $formattedDate = $dateObj->format('M d, Y');
                                // For now, we'll just use an empty string
                                $formattedDate = '';
                                // Status text and class
                                $status = strtolower($row['status']);
                                switch($status) {
                                    case 'pending':
                                        $statusText = 'Pending Review';
                                        $statusClass = 'status-pending';
                                        break;
                                    case 'in progress':
                                    case 'in-progress':
                                        $statusText = 'In Progress';
                                        $statusClass = 'status-in-progress';
                                        break;
                                    case 'resolved':
                                        $statusText = 'Resolved';
                                        $statusClass = 'status-resolved';
                                        break;
                                    default:
                                        $statusText = 'Pending';
                                        $statusClass = 'status-pending';
                                }
                                // Priority (if you have it in your DB, otherwise remove this)
                                $priorityIndicator = '';
                            ?>
                            <div class="complaint-item">
                                <div class="complaint-header">
                                    <div class="complaint-title"><?php echo htmlspecialchars($row['subject']); ?></div>
                                    <div class="complaint-date"><?php echo $formattedDate; ?></div>
                                </div>
                                <div class="complaint-content"><?php echo nl2br(htmlspecialchars($row['message'])); ?></div>
                                <div>
                                    <span class="status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    <span style="float: right; font-size: 0.9rem;"><?php echo $priorityIndicator; ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>Student Complaints Portal &copy; 2023 | All complaints are reviewed within 3 business days</p>
        </div>
    </footer>
<script>
window.addEventListener('DOMContentLoaded', function() {
  var toast = document.getElementById('toast');
  if (toast) {
    setTimeout(function() {
      toast.classList.add('hide');
    }, 3000); // Hide after 3 seconds
  }
});
</script>
</body>
</html>