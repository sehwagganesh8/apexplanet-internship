<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Management System - SehwagGanesh</title>
  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar">
    <div class="logo">
      <i class="fas fa-user-shield"></i> Admin Panel
    </div>
    <ul class="nav-links">
      <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
      <?php if(!isset($_SESSION['user_id'])): ?>
      <li><a href="auth.php?action=login"><i class="fas fa-sign-in-alt"></i> Login</a></li>
      <li><a href="auth.php?action=register"><i class="fas fa-user-plus"></i> Register</a></li>
      <?php else: ?>
      <li><a href="auth.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
      <?php endif; ?>
    </ul>
  </nav>

  <!-- Flash Messages -->
  <?php if(!empty($flash)): ?>
  <div class="flash-message <?php echo $flash['type']; ?>">
    <?php echo htmlspecialchars($flash['message']); ?>
  </div>
  <?php endif; ?>

  <!-- Main Content -->
  <main class="main-content">
    <?php if(!isset($_SESSION['user_id'])): ?>
    <!-- Auth Section -->
    <section class="auth-section">
      <div class="container">
        <div class="auth-card">
          <div class="auth-header">
            <h2><i class="fas fa-user-shield"></i> Authentication</h2>
          </div>

          <!-- Login Form -->
          <form class="auth-form" action="auth.php?action=login" method="post">
            <h3><i class="fas fa-sign-in-alt"></i> Login</h3>
            <div class="form-group">
              <input type="email" name="email" placeholder="Enter your email" required>
              <i class="fas fa-envelope form-icon"></i>
            </div>
            <div class="form-group">
              <input type="password" name="password" placeholder="Enter your password" required>
              <i class="fas fa-lock form-icon"></i>
            </div>
            <button type="submit" class="btn btn-primary full-width"><i class="fas fa-sign-in-alt"></i> Login</button>
            <p class="mt-2"><a href="auth.php?action=register" class="link-primary">Create an account</a></p>
          </form>

          <!-- Register Form -->
          <form class="auth-form" action="auth.php?action=register" method="post" style="margin-top: 2rem;">
            <h3><i class="fas fa-user-plus"></i> Register</h3>
            <div class="form-group">
              <input type="text" name="name" placeholder="Full name" required>
              <i class="fas fa-user form-icon"></i>
            </div>
            <div class="form-group">
              <input type="email" name="email" placeholder="Enter your email" required>
              <i class="fas fa-envelope form-icon"></i>
            </div>
            <div class="form-group">
              <input type="password" name="password" placeholder="Create a password" required>
              <i class="fas fa-lock form-icon"></i>
            </div>
            <button type="submit" class="btn btn-primary full-width"><i class="fas fa-user-plus"></i> Register</button>
          </form>
        </div>
      </div>
    </section>
    <?php else: ?>
    <!-- Dashboard Section -->
    <section class="dashboard">
      <div class="container">
        <div class="dashboard-header">
          <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
          <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <span><?php echo date('M d, Y'); ?></span>
          </div>
        </div>

        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-number" id="total-users">Loading...</div>
            <div class="stat-label">Total Users</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-list"></i></div>
            <div class="stat-number" id="active-users">Loading...</div>
            <div class="stat-label">Active Today</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-shield-alt"></i></div>
            <div class="stat-number" id="new-users">Loading...</div>
            <div class="stat-label">New This Month</div>
          </div>
        </div>

        <!-- Users Table -->
        <section class="users-table">
          <div class="table-header">
            <h3><i class="fas fa-table"></i> User List</h3>
            <a href="javascript:void(0);" class="btn btn-sm" onclick="showAddUserForm()">Add User</a>
          </div>
          <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Profile Picture</th>
                  <th>Joined</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="usersTable">
                <!-- Users loaded via AJAX -->
              </tbody>
            </table>
          </div>
        </section>

        <!-- Add/Edit User Form -->
        <section class="user-form-section" id="userFormSection" style="display: none;">
          <div class="user-form-card">
            <div class="card-header">
              <h3 id="formTitle">Add New User</h3>
              <button class="btn btn-sm" onclick="hideUserForm()">×</button>
            </div>
            <form class="user-form" action="api.php?action=<?php echo isset($existingUser) ? 'edit-user' : 'add-user'; ?>" method="post" enctype="multipart/form-data">
              <input type="hidden" name="user_id" id="editUserId">

              <div class="form-group">
                <label for="userName">Full Name</label>
                <input type="text" name="name" id="userName" required>
              </div>

              <div class="form-group">
                <label for="userEmail">Email</label>
                <input type="email" name="email" id="userEmail" required>
              </div>

              <div class="form-group">
                <label for="userPassword">Password</label>
                <input type="password" name="password" id="userPassword" placeholder="(leave blank to keep current)">
              </div>

              <div class="form-group">
                <label for="userRole">Role</label>
                <select name="role" id="userRole">
                  <option value="user">Regular User</option>
                  <option value="admin">Admin</option>
                </select>
              </div>

              <div class="form-group">
                <label for="userProfilePic">Profile Picture</label>
                <input type="file" name="profile_pic" id="userProfilePic">
                <p class="form-text">Accepted: JPG, PNG. Max 2MB</p>
              </div>

              <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?php echo isset($existingUser) ? 'Update User' : 'Add User'; ?></button>
                <button type="button" class="btn btn-secondary" onclick="hideUserForm()">Cancel</button>
              </div>
            </form>
          </div>
        </section>
      </div>
    </section>
    <?php endif; ?>
  </main>

  <script>
    // Check auth status on page load
    document.addEventListener('DOMContentLoaded', function() {
      const authSection = document.querySelector('.auth-section');
      const dashboardSection = document.querySelector('.dashboard');
      const flashMessage = document.querySelector('.flash-message');

      // Hide flash message after 5 seconds
      if (flashMessage) {
        setTimeout(() => {
          flashMessage.style.opacity = '0';
          flashMessage.style.transition = 'opacity 0.5s';
          setTimeout(() => flashMessage.remove(), 500);
        }, 5000);
      }

      // Toggle between auth and dashboard
      const checkAuth = function() {
        // This would typically be done via AJAX check, but for demo:
        const loggedIn = '<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>';
        if (loggedIn === 'true') {
          if (authSection) authSection.style.display = 'none';
          if (dashboardSection) dashboardSection.style.display = 'block';
        } else {
          if (authSection) authSection.style.display = 'block';
          if (dashboardSection) dashboardSection.style.display = 'none';
        }
      };

      checkAuth();

      // Add user form toggle
      window.showAddUserForm = function() {
        hideUserForm();
        document.getElementById('userFormSection').style.display = 'block';
        document.getElementById('formTitle').textContent = 'Add New User';
        document.getElementById('editUserId').value = '';
        document.getElementById('userName').value = '';
        document.getElementById('userEmail').value = '';
        document.getElementById('userPassword').value = '';
        document.getElementById('userRole').value = 'user';
        document.getElementById('userProfilePic').value = '';
      };

      window.hideUserForm = function() {
        const formSection = document.getElementById('userFormSection');
        if (formSection) formSection.style.display = 'none';
      };

      // Edit user form
      window.editUser = function(userId, name, email, role, profilePic) {
        hideUserForm();
        document.getElementById('userFormSection').style.display = 'block';
        document.getElementById('formTitle').textContent = 'Edit User';
        document.getElementById('editUserId').value = userId;
        document.getElementById('userName').value = name;
        document.getElementById('userEmail').value = email;
        document.getElementById('userRole').value = role;
        // Set profile pic preview if exists
        const picInput = document.getElementById('userProfilePic');
        if (picInput && profilePic) {
          picInput.value = profilePic;
        }
      };
    });
  </script>
</body>
</html>