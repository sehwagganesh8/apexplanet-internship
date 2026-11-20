<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-Learning Portal - Capstone Project</title>
  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar">
    <div class="logo">
      <i class="fas fa-graduation-cap"></i> E-Learn
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

  <?php session_start(); ?>

  <?php if(!isset($_SESSION['user_id'])): ?>
  <!-- Auth Section -->
  <section class="auth-section">
    <div class="container">
      <div class="auth-card">
        <div class="auth-header">
          <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
        </div>

        <form class="auth-form" action="auth.php?action=login" method="post">
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
            <i class="fas fa-envelope form-icon"></i>
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
            <i class="fas fa-lock form-icon"></i>
          </div>
          <button type="submit" class="btn btn-primary full-width"><i class="fas fa-sign-in-alt"></i> Login</button>
          <p class="mt-2"><a href="auth.php?action=register" class="link-primary">Create an account</a></p>
        </form>

        <form class="auth-form" action="auth.php?action=register" method="post" style="margin-top: 2rem;">
          <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" required>
            <i class="fas fa-user form-icon"></i>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
            <i class="fas fa-envelope form-icon"></i>
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
            <i class="fas fa-lock form-icon"></i>
          </div>
          <button type="submit" class="btn btn-primary full-width"><i class="fas fa-user-plus"></i> Register</button>
        </form>
      </div>
    </div>
  </section>
  <?php else: ?>
  <!-- User is logged in -->
  <section class="dashboard">
    <div class="container">
      <div class="dashboard-header">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
        <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
      </div>

      <!-- Stats & OTP Status -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon"><i class="fas fa-chalkboard-user"></i></div>
          <div class="stat-number" id="total-courses">Loading...</div>
          <div class="stat-label">Total Courses</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
          <div class="stat-number" id="total-students">Loading...</div>
          <div class="stat-label">Total Students</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon"><i class="fas fa-envelope"></i></div>
          <div class="stat-number" id="otp-status">Loading...</div>
          <div class="stat-label">OTP Verification</div>
        </div>
      </div>

      <!-- Course Management -->
      <section class="course-management">
        <div class="table-header">
          <h3><i class="fas fa-folder"></i> My Courses</h3>
          <a href="javascript:void(0);" class="btn" onclick="showAddCourseForm()">Add New Course</a>
        </div>
        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Title</th>
                <th>Instructor</th>
                <th>Students</th>
                <th>Price</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="coursesTable">
              <!-- Courses loaded via AJAX -->
            </tbody>
          </table>
        </div>
      </section>

      <!-- Add/Edit Course Form -->
      <section class="course-form-section" id="courseFormSection" style="display: none;">
        <div class="course-form-card">
          <div class="card-header">
            <h3 id="courseFormTitle">Add New Course</h3>
            <button class="btn btn-sm" onclick="hideCourseForm()">×</button>
          </div>
          <form class="course-form" action="api.php?action=add-course" method="post" enctype="multipart/form-data">
            <input type="hidden" name="course_id" id="editCourseId">

            <div class="form-group">
              <label for="courseTitle">Title</label>
              <input type="text" name="title" id="courseTitle" required>
            </div>

            <div class="form-group">
              <label for="courseInstructor">Instructor</label>
              <input type="text" name="instructor" id="courseInstructor" required>
            </div>

            <div class="form-group">
              <label for="coursePrice">Price</label>
              <input type="number" step="0.01" name="price" id="coursePrice" required>
            </div>

            <div class="form-group">
              <label for="courseDuration">Duration (hours)</label>
              <input type="number" step="0.5" name="duration" id="courseDuration" required min="0.5">
            </div>

            <div class="form-group">
              <label for="courseDescription">Description</label>
              <textarea name="description" id="courseDescription" rows="3" required></textarea>
            </div>

            <div class="form-group">
              <label for="courseCover">Course Cover</label>
              <input type="file" name="cover_image" id="courseCover">
              <p class="form-text">Accepted: JPG, PNG. Max 2MB</p>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary"><?php echo isset($existingCourse) ? 'Update Course' : 'Add Course'; ?></button>
              <button type="button" class="btn btn-secondary" onclick="hideCourseForm()">Cancel</button>
            </div>
          </form>
        </div>
      </section>

      <!-- OTP Verification Section -->
      <section class="otp-section" id="otpSection" style="margin-top: 3rem; display: none;">
        <div class="otp-card">
          <div class="card-header">
            <h3><i class="fas fa-envelope"></i> Email Verification</h3>
          </div>
          <p>A verification code has been sent to your email address.</p>
          <div class="otp-inputs">
            <input type="text" id="otp1" maxlength="1" class="otp-input" oninput="checkOTP()">
            <input type="text" id="otp2" maxlength="1" class="otp-input" oninput="checkOTP()">
            <input type="text" id="otp3" maxlength="1" class="otp-input" oninput="checkOTP()">
            <input type="text" id="otp4" maxlength="1" class="otp-input" oninput="checkOTP()">
            <input type="text" id="otp5" maxlength="1" class="otp-input" oninput="checkOTP()">
            <input type="text" id="otp6" maxlength="1" class="otp-input" oninput="checkOTP()">
          </div>
          <button class="btn btn-secondary" onclick="resendOTP()">Resend Code</button>
          <p id="otp-timer" style="margin-top: 1rem; font-size: 0.8rem; color: #666;"></p>
        </div>
      </section>
    </div>
  </section>
  <?php endif; ?>

  <script>
    // Check auth status on page load
    document.addEventListener('DOMContentLoaded', function() {
      const isLoggedIn = '<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>';

      if (!isLoggedIn) {
        document.querySelectorAll('.dashboard').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.auth-section').forEach(el => el.style.display = 'block');
      } else {
        document.querySelectorAll('.auth-section').forEach(el => el.style.display = 'none');
        // Load data
        loadStats();
        loadCourses();

        // Start OTP timer
        startOTPTimer();
      }
    });

    // Load statistics via AJAX
    function loadStats() {
      fetch('api.php?action=get-stats')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            document.getElementById('total-courses').textContent = data.stats.totalCourses || 0;
            document.getElementById('total-students').textContent = data.stats.totalStudents || 0;
            const otp = data.stats.otpStatus;
            document.getElementById('otp-status').innerHTML = otp ? `<span style="color: var(--success);">✓ Verified</span>` : `<span style="color: var(--danger);">✗ Not Verified</span>`;
          }
        })
        .catch(err => console.error('Error loading stats:', err));
    }

    // Load courses via AJAX
    function loadCourses() {
      fetch('api.php?action=list-courses')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const tbody = document.getElementById('coursesTable');
            tbody.innerHTML = '';

            if (data.courses && data.courses.length > 0) {
              data.courses.forEach((course, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                  <td>${index + 1}</td>
                  <td>${course.title}</td>
                  <td>${course.instructor}</td>
                  <td>${course.students || 0}</td>
                  <td>$${parseFloat(course.price).toFixed(2)}</td>
                  <td>
                    <button class="btn btn-sm" onclick="editCourse(${course.id})">Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteCourse(${course.id})">Delete</button>
                  </td>
                `;
                tbody.appendChild(tr);
              });
            } else {
              tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No courses found</td></tr>';
            }
          }
        })
        .catch(err => console.error('Error loading courses:', err));
    }

    // Course form actions
    window.showAddCourseForm = function() {
      hideCourseForm();
      document.getElementById('courseFormSection').style.display = 'block';
      document.getElementById('courseFormTitle').textContent = 'Add New Course';
      document.getElementById('editCourseId').value = '';
      document.getElementById('courseTitle').value = '';
      document.getElementById('courseInstructor').value = '';
      document.getElementById('coursePrice').value = '';
      document.getElementById('courseDuration').value = '10';
      document.getElementById('courseDescription').value = '';
      document.getElementById('courseCover').value = '';
    };

    window.hideCourseForm = function() {
      const section = document.getElementById('courseFormSection');
      if (section) section.style.display = 'none';
    };

    window.editCourse = function(courseId) {
      hideCourseForm();
      document.getElementById('courseFormSection').style.display = 'block';
      document.getElementById('courseFormTitle').textContent = 'Edit Course';
      document.getElementById('editCourseId').value = courseId;

      fetch(`api.php?action=get-course&id=${courseId}`)
        .then(response => response.json())
        .then(data => {
          if (data.success && data.course) {
            const c = data.course;
            document.getElementById('courseTitle').value = c.title;
            document.getElementById('courseInstructor').value = c.instructor;
            document.getElementById('coursePrice').value = c.price;
            document.getElementById('courseDuration').value = c.duration;
            document.getElementById('courseDescription').value = c.description;
            // Set cover image preview
            const coverDiv = document.querySelector('.cover-preview');
            if (coverDiv && c.cover_image) {
              coverDiv.innerHTML = `<img src="courses/${c.cover_image}" alt="Course cover" style="max-width: 150px; max-height: 200px;">`;
            }
          }
        })
        .catch(err => console.error('Error fetching course:', err));
    };

    window.deleteCourse = function(courseId) {
      if (confirm('Are you sure you want to delete this course?')) {
        fetch('api.php?action=delete-course', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({id: courseId})
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Course deleted successfully');
            loadCourses();
            loadStats();
          } else {
            alert('Failed to delete course: ' + (data.message || ''));
          }
        })
        .catch(err => console.error('Error deleting course:', err));
      }
    }

    // OTP Timer
    let otpTimer = 5 * 60; // 5 minutes in seconds
    let otpInterval = null;

    function startOTPTimer() {
      const timerEl = document.getElementById('otp-timer');
      if (!timerEl) return;

      otpInterval = setInterval(function() {
        const minutes = Math.floor(otpTimer / 60);
        const seconds = otpTimer % 60;
        timerEl.textContent = `Resend in: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

        if (otpTimer <= 0) {
          clearInterval(otpInterval);
          timerEl.textContent = 'Resend Code';
          document.querySelector('.btn-secondary').disabled = false;
        }

        otpTimer--;
      }, 1000);
    }

    function resendOTP() {
      clearInterval(otpInterval);
      otpTimer = 5 * 60;
      // In a real app, would call API to send new OTP
      document.querySelector('.btn-secondary').disabled = true;
      startOTPTimer();

      // Generate random 6-digit OTP for demo
      const otp = Math.floor(100000 + Math.random() * 900000);
      // Store OTP in session for verification
      <?php $_SESSION['otp'] = $otp; ?>

      // Show OTP inputs
      document.getElementById('otpSection').style.display = 'block';

      // Focus first input
      setTimeout(() => document.getElementById('otp1').focus(), 100);
    }

    // OTP verification
    function checkOTP() {
      const otp = document.getElementById('otp1').value +
                    document.getElementById('otp2').value +
                    document.getElementById('otp3').value +
                    document.getElementById('otp4').value +
                    document.getElementById('otp5').value +
                    document.getElementById('otp6').value;

      // In a real app, would verify via AJAX
      if (otp.length === 6) {
        // For demo: if OTP is 123456, verify
        if (otp === '123456') {
          alert('Email verified successfully!');
          document.getElementById('otpSection').style.display = 'none';
          // Mark OTP as verified in UI
          document.getElementById('otp-status').innerHTML = '<span style="color: var(--success);">✓ Verified</span>';
        } else {
          alert('Invalid verification code. Please try again.');
          // Reset inputs
          document.getElementById('otp1').value = '';
          document.getElementById('otp2').value = '';
          document.getElementById('otp3').value = '';
          document.getElementById('otp4').value = '';
          document.getElementById('otp5').value = '';
          document.getElementById('otp6').value = '';
          document.getElementById('otp1').focus();
        }
      }
    }
  </script>
</body>
</html>