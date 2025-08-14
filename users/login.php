<?php
session_start();
include '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        // Registration logic
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $matric_number = $_POST['matric_number'];
        $faculty = $_POST['faculty'];
        $department = $_POST['department'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, matric_number, faculty, department, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $first_name, $last_name, $email, $matric_number, $faculty, $department, $password);

        if ($stmt->execute()) {
            header("Location: login.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } elseif ($action === 'login') {
        // Login logic
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, role FROM users WHERE email = ? OR matric_number = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['first_name'] = $row['first_name'];
                $_SESSION['last_name'] = $row['last_name'];
                $_SESSION['role'] = $row['role']; // Save role in session
                $_SESSION['toast'] = "Welcome, " . $row['first_name'] . "!";
                if ($row['role'] === 'admin') {
                    header("Location: ../admin/admin-dash.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "User not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login/Register</title>
    <style>
      body {
        font-family: "Segoe UI", sans-serif;
        background-color: #e3f2fd;
        margin: 0;
        padding: 0;
        display: flex;
        height: 100vh;
        justify-content: center;
        align-items: center;
      }

      .container {
        background-color: #ffffff;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
        overflow-y: auto;
        max-height: 90vh;
      }

      h2 {
        text-align: center;
        color: #1565c0;
        margin-bottom: 20px;
      }

      label {
        display: block;
        margin-top: 10px;
        font-weight: 500;
      }

      input[type="text"],
      input[type="password"],
      input[type="email"] {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
      }

      button {
        width: 100%;
        padding: 10px;
        background: linear-gradient(to right, #42a5f5, #1e88e5);
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        transition: background 0.3s;
      }

      button:hover {
        background: linear-gradient(to right, #1e88e5, #1565c0);
      }

      .toggle-link {
        margin-top: 15px;
        text-align: center;
        font-size: 14px;
      }

      .toggle-link a {
        color: #1565c0;
        text-decoration: none;
        font-weight: 500;
        cursor: pointer;
      }

      .hidden {
        display: none;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <!-- Login Form -->
      <form id="loginForm" method="POST">
        <input type="hidden" name="action" value="login" />
        <h2>Login</h2>
        <label for="loginUsername">Email or Matric Number:</label>
        <input type="text" id="loginUsername" name="username" required />

        <label for="loginPassword">Password:</label>
        <input type="password" id="loginPassword" name="password" required />

        <button type="submit">Login</button>

        <div class="toggle-link">
          Don't have an account? <a onclick="showRegister()">Sign up</a>
        </div>
      </form>

      <!-- Register Form -->
      <form id="registerForm" class="hidden" method="POST">
        <input type="hidden" name="action" value="register" />
        <h2>Register</h2>
        <label for="registerFirstName">First Name</label>
        <input type="text" id="registerFirstName" name="first_name" placeholder="Enter your first name ..." required />

        <label for="registerLastName">Last Name</label>
        <input type="text" id="registerLastName" name="last_name" placeholder="Enter your last name ..." required />

        <label for="registerMatricNumber">Matric Number</label>
        <input type="text" id="registerMatricNumber" name="matric_number" placeholder="e.g  ABC/12/1234" required />

        <label for="registerEmail">Email</label>
        <input type="email" id="registerEmail" name="email" placeholder="Enter a valid email address" required />

        <label for="registerFaculty">Faculty</label>
        <input type="text" id="registerFaculty" name="faculty" placeholder="Enter your faculty" required />

        <label for="registerDepartment">Department</label>
        <input type="text" id="registerDepartment" name="department" placeholder="Enter your department" required />

        <label for="registerPassword">Password</label>
        <input type="password" id="registerPassword" name="password" required />

        <label for="registerPassword">Password (Again)</label>
        <input type="password" id="registerPassword" name="password_confirm" required />

        <button type="submit">Register</button>

        <div class="toggle-link">
          Already have an account? <a onclick="showLogin()">Login</a>
        </div>
      </form>
    </div>

    <script>
      const loginForm = document.getElementById("loginForm");
      const registerForm = document.getElementById("registerForm");

      function showRegister() {
        loginForm.classList.add("hidden");
        registerForm.classList.remove("hidden");
      }

      function showLogin() {
        registerForm.classList.add("hidden");
        loginForm.classList.remove("hidden");
      }
    </script>
  </body>
</html>
