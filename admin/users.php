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
$total_users = 0;
$user_count_sql = "SELECT COUNT(*) AS total FROM users";
$user_count_result = $conn->query($user_count_sql);
if ($user_count_result) {
    $row = $user_count_result->fetch_assoc();
    $total_users = $row['total'];
}

// Get initials from session
$first = $_SESSION['first_name'] ?? '';
$last = $_SESSION['last_name'] ?? '';
$initials = strtoupper(substr($first, 0, 1) . substr($last, 0, 1));

$student_count = 0;
$student_sql = "SELECT COUNT(*) AS total FROM users WHERE role = 'student'";
$student_result = $conn->query($student_sql);
if ($student_result) {
    $row = $student_result->fetch_assoc();
    $student_count = $row['total'];
}

$admin_count = 0;
$admin_sql = "SELECT COUNT(*) AS total FROM users WHERE role = 'admin'";
$admin_result = $conn->query($admin_sql);
if ($admin_result) {
    $row = $admin_result->fetch_assoc();
    $admin_count = $row['total'];
}

// FETCH ALL USERS
$users_sql = "SELECT id, first_name, last_name, email, role, matric_number, created_at FROM users ORDER BY created_at DESC";
$users_result = $conn->query($users_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
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
            <li><a href="admin-dash.php"><i class="fas fa-home"></i> <span class="link-text">Dashboard</span></a></li>
            <li><a href=""  class="active"><i class="fas fa-users"></i> <span class="link-text">Students</span></a></li>
            <li><a href="../users/logout.php"><i class="fas fa-question-circle"></i> <span class="link-text">Logout</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="container">
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
            <!-- Stats Section -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(156, 123, 172, 0.15); color: var(--african-violet);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_users; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(156, 123, 172, 0.15); color: var(--african-violet);">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $admin_count; ?></h3>
                        <p>Administrators</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(76, 177, 255, 0.15); color: var(--argentinian-blue);">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $student_count; ?></h3>
                        <p>Students</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(124, 176, 120, 0.15); color: var(--asparagus);">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_users; ?></h3>
                        <p>Active Users</p>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-group">
                    <label for="status">User Status</label>
                    <select id="status">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="role">User Role</label>
                    <select id="role">
                        <option value="">All Roles</option>
                        <option value="admin">Administrator</option>
                        <option value="student">Student</option>
                    </select>
                </div>
                <div class="search-group">
                    <label for="search">Search Users</label>
                    <i class="fas fa-search" style="margin-top: 10px;"></i>
                    <input type="text" id="search" placeholder="Search by name, email, or ID...">
                </div>
            </div>

            <!-- Users Grid -->
            <div class="users-container">
                <?php if ($users_result && $users_result->num_rows > 0): ?>
                    <?php while ($user = $users_result->fetch_assoc()): ?>
                        <div class="user-card">
                            <div class="card-header">
                                <div class="user-avatar-lg">
                                    <?php echo strtoupper(substr($user['first_name'],0,1) . substr($user['last_name'],0,1)); ?>
                                </div>
                                <div class="user-name-lg"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                            <div class="card-body">
                                <ul class="user-details-list">
                                    <li>
                                        <div class="detail-label">Matric No:</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($user['matric_number'] ?? $user['id']); ?></div>
                                    </li>
                                    <li>
                                        <div class="detail-label">Status:</div>
                                        <div class="detail-value">
                                            <span class="status-badge status-active">
                                                Active
                                            </span>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="detail-label">Role:</div>
                                        <div class="detail-value">
                                            <span class="role-badge role-<?php echo strtolower($user['role']); ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="detail-label">Joined:</div>
                                        <div class="detail-value">
                                            <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-footer">
                                <button class="btn btn-admin make-admin-btn" data-user="<?php echo htmlspecialchars($user['id']); ?>">
                                    <i class="fas fa-user-shield"></i>
                                    <?php echo $user['role'] === 'admin' ? 'Remove Administrator' : 'Make Administrator'; ?>
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-complaints">No users found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

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


        // Make Admin functionality
        const makeAdminButtons = document.querySelectorAll('.make-admin-btn');
        
        makeAdminButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user');
            const userCard = this.closest('.user-card');
            const roleBadge = userCard.querySelector('.role-badge');
            const userName = userCard.querySelector('.user-name-lg').textContent;
            const isAdmin = roleBadge.classList.contains('role-admin');
            const newRole = isAdmin ? 'student' : 'admin';
            const confirmMsg = isAdmin
                ? `Are you sure you want to remove administrator rights from ${userName}?`
                : `Are you sure you want to make ${userName} an administrator?`;

            if (confirm(confirmMsg)) {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                this.disabled = true;

                fetch('update-role.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `user_id=${userId}&role=${newRole}`
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        // Update role badge
                        roleBadge.textContent = newRole === 'admin' ? 'Administrator' : 'Student';
                        roleBadge.className = 'role-badge role-' + newRole;

                        // Update button
                        if (newRole === 'admin') {
                            this.className = 'btn btn-admin make-admin-btn';
                            this.innerHTML = '<i class="fas fa-user-shield"></i> Remove Administrator';
                            this.disabled = false;
                        } else {
                            this.className = 'btn btn-admin make-admin-btn';
                            this.innerHTML = '<i class="fas fa-user-shield"></i> Make Administrator';
                            this.disabled = false;
                        }

                        showToast('User role updated!');
                    } else {
                        showToast('Failed to update role.');
                        this.disabled = false;
                    }
                });
            }
        });
    });
        
        // Search functionality
        const searchInput = document.getElementById('search');
        const userCards = document.querySelectorAll('.user-card');
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            userCards.forEach(card => {
                const name = card.querySelector('.user-name-lg').textContent.toLowerCase();
                const email = card.querySelector('.user-email').textContent.toLowerCase();
                const userId = card.querySelector('.detail-value').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || email.includes(searchTerm) || userId.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>