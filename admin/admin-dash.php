<?php
session_start();
include '../config/config.php';

// Allowing only admin users to access this page, Students can not access this page

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../users/login.php");
    exit();
}


// GET LOGGED IN USER
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT first_name, last_name FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_row = $user_result->fetch_assoc();
$full_name = $user_row ? $user_row['first_name'] . ' ' . $user_row['last_name'] : '';

// Get initials from session
$first = $_SESSION['first_name'] ?? '';
$last = $_SESSION['last_name'] ?? '';
$initials = strtoupper(substr($first, 0, 1) . substr($last, 0, 1));

// GET THE TOTAL NUMBER OF STUDENTS
$student_count = 0;
$student_sql = "SELECT COUNT(*) AS total FROM users WHERE role = 'student'";
$student_result = $conn->query($student_sql);
if ($student_result) {
    $row = $student_result->fetch_assoc();
    $student_count = $row['total'];
}

// GET THE TOTAL NUMBER OF PENDING COMPLAINTS
$pending_count = 0;
$pending_sql = "SELECT COUNT(*) AS total FROM complaints WHERE status = 'Pending'";
$pending_result = $conn->query($pending_sql);
if ($pending_result) {
    $row = $pending_result->fetch_assoc();
    $pending_count = $row['total'];
}
// GET THE TOTAL NUMBER OF IN PROGRESS COMPLAINTS
$in_progress_count = 0;
$in_progress_sql = "SELECT COUNT(*) AS total FROM complaints WHERE status = 'In Progress'";
$in_progress_result = $conn->query($in_progress_sql);
if ($in_progress_result) {
    $row = $in_progress_result->fetch_assoc();
    $in_progress_count = $row['total'];
}

// GET THE TOTAL NUMBER OF RESOLVED COMPLAINTS
$resolved_count = 0;
$resolved_sql = "SELECT COUNT(*) AS total FROM complaints WHERE status = 'Resolved'";
$resolved_result = $conn->query($resolved_sql);
if ($resolved_result) {
    $row = $resolved_result->fetch_assoc();
    $resolved_count = $row['total'];
}

// GET ALL COMPLAINTS
$complaints_sql = "
    SELECT c.id, c.subject, c.message, c.status, c.created_at,
           u.first_name, u.last_name, u.matric_number
    FROM complaints c
    JOIN users u ON c.user_id = u.id
    ORDER BY c.id DESC
";
$complaints_result = $conn->query($complaints_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Student Complaints</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/dashboard.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-graduation-cap"></i> <span class="logo-text">UniAdmin</span></h2>
        </div>
        <ul class="nav-links">
            <li><a href="" class="active"><i class="fas fa-home"></i> <span class="link-text">Dashboard</span></a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> <span class="link-text">Students</span></a></li>
            <li><a href="../users/logout.php"><i class="fas fa-question-circle"></i> <span class="link-text">Logout</span></a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Complaints Dashboard</h1>
            <div class="user-info">
                <div class="user-avatar"><?php echo $initials; ?></div>
                <div>
                    <div><?php echo htmlspecialchars($full_name); ?></div>
                    <div style="font-size: 0.85rem; color: #777;">Administrator</div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(255, 127, 45, 0.15); color: var(--pumpkin);">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $pending_count; ?></h3>
                    <p>Pending Complaints</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(76, 177, 255, 0.15); color: var(--argentinian-blue);">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $in_progress_count; ?></h3>
                    <p>In Progress</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(124, 176, 120, 0.15); color: var(--asparagus);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $resolved_count; ?></h3>
                    <p>Resolved</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(156, 123, 172, 0.15); color: var(--african-violet);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $student_count; ?></h3>
                    <p>Students Active</p>
                </div>
            </div>
        </div>

        <!-- Complaints Table -->
        <div class="complaints-container">
            <div class="section-header">
                <h2>Recent Complaints</h2>
                <div class="filters">
                    <button class="filter-btn active">All</button>
                    <button class="filter-btn">Pending</button>
                    <button class="filter-btn">In Progress</button>
                    <button class="filter-btn">Resolved</button>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Matric No</th>
                        <th>Student</th>
                        <th>Complaint</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($complaints_result && $complaints_result->num_rows > 0): ?>
                    <?php while ($row = $complaints_result->fetch_assoc()): ?>
                        <tr data-status="<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>">
                            <td><?php echo htmlspecialchars($row['matric_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <span class="status status-<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="action-btn view-btn" data-id="<?php echo $row['id']; ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn delete-btn" data-id="<?php echo $row['id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8">No complaints found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Complaint Detail Modal -->
    <form id="replyForm" method="POST" action="reply-complaint.php">
        <input type="hidden" name="complaint_id" id="complaint_id">
        <div class="modal-overlay" id="complaintModal">
            <div class="modal">
                <div class="modal-header">
                    <h3>Complaint Details</h3>
                    <button class="close-btn">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="complaint-detail">
                        <div class="detail-row">
                            <div class="detail-label">Complaint Matric Number:</div>
                            <div class="detail-value" id="modal-matricno"></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Student:</div>
                            <div class="detail-value" id="modal-student"></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Email:</div>
                            <div class="detail-value" id="modal-email"></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Date Submitted:</div>
                            <div class="detail-value" id="modal-date"></div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">Status:</div>
                            <div class="detail-value">
                                <select class="status-select" name="status">
                                    <option>Pending</option>
                                    <option selected>In Progress</option>
                                    <option>Resolved</option>
                                </select>
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Description:</div>
                        </div>
                        <div class="complaint-description">
                            <p id="modal-description"></p>
                        </div>
                    </div>

                    <div class="reply-section">
                        <h4>Reply to Student</h4>
                        <textarea name="reply" placeholder="Type your response here..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline" id="cancelBtn">Cancel</button>
                    <button class="btn btn-primary">Save & Send Response</button>
                </div>
            </div>
        </div>
    </form>
    <script>
        function showToast(message) {
            let oldToast = document.getElementById('toast');
            if (oldToast) oldToast.remove();
            let toast = document.createElement('div');
            toast.id = 'toast';
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 500);
            }, 3000);
        }

        const modal = document.getElementById('complaintModal');
        const viewButtons = document.querySelectorAll('.view-btn');
        const closeButton = document.querySelector('.close-btn');
        const deleteButtons = document.querySelectorAll('.delete-btn');


        viewButtons.forEach(button => {
            button.addEventListener('click', () => {
                const complaintId = button.getAttribute('data-id');
                fetch('get-complaint.php?id=' + complaintId)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('modal-matricno').textContent = data.matric_number;
                        document.getElementById('modal-student').textContent = `${data.first_name} ${data.last_name} (ID: ${data.matric_number})`;
                        document.getElementById('modal-email').textContent = data.email;
                        document.getElementById('modal-date').textContent = new Date(data.created_at).toLocaleString();
                        document.querySelector('.status-select').value = data.status;
                        document.getElementById('modal-description').textContent = data.message;
                        document.getElementById('complaint_id').value = data.id;      
                        modal.classList.add('active');
                    });
            });
        });

        document.getElementById('cancelBtn').addEventListener('click', () => {
            modal.classList.remove('active');
        });

        closeButton.addEventListener('click', () => {
            modal.classList.remove('active');
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const complaintId = button.getAttribute('data-id');
                if (confirm('Are you sure you want to delete this complaint?')) {
                    fetch('delete-complaint.php?id=' + complaintId, { method: 'GET' })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                // Optionally remove the row from the table
                                button.closest('tr').remove();
                                showToast('Complaint deleted successfully!');
                            } else {
                                showToast('Failed to delete complaint.');
                            }
                        });
                }
            });
        });

        document.getElementById('replyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showToast('Operation successful!');
                    modal.classList.remove('active');
                    form.reset();
                } else {
                    showToast('Failed to send reply.');
                }
            })
            .catch(() => {
                showToast('An error occurred.');
            });
        });

        // Filter button functionality
        const filterButtons = document.querySelectorAll('.filter-btn');
        const tableRows = document.querySelectorAll('tbody tr');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                button.classList.add('active');

                const filter = button.textContent.trim().toLowerCase().replace(' ', '-');
                tableRows.forEach(row => {
                    if (filter === 'all') {
                        row.style.display = '';
                    } else {
                        // Compare with data-status attribute
                        if (row.getAttribute('data-status') === filter) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>