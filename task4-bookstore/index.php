<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Online Bookstore - Admin Panel</title>
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
      <i class="fas fa-book"></i> BookStore
    </div>
    <ul class="nav-links">
      <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
      <li><a href="auth.php?action=login"><i class="fas fa-sign-in-alt"></i> Login</a></li>
      <li><a href="admin.php"><i class="fas fa-shield-alt"></i> Admin</a></li>
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
          <button type="submit" class="btn btn-primary full-width"><i class="fas fa-sign-in-alt"></i> Sign In</button>
          <p class="mt-2">Don't have an account? <a href="auth.php?action=register" class="link-primary">Register</a></p>
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
          <button type="submit" class="btn btn-primary full-width"><i class="fas fa-user-plus"></i> Create Account</button>
        </form>
      </div>
    </div>
  </section>
  <?php else: ?>
  <!-- User is logged in -->
  <?php if($_SESSION['user_role'] === 'admin'): ?>
  <!-- Admin Section -->
  <section class="admin-section">
    <div class="container">
      <div class="admin-header">
        <h2><i class="fas fa-shield-alt"></i> Admin Panel</h2>
        <a href="auth.php?action=logout" class="btn btn-secondary">Logout</a>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon"><i class="fas fa-users"></i></div>
          <div class="stat-number" id="total-books">Loading...</div>
          <div class="stat-label">Total Books</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
          <div class="stat-number" id="total-orders">Loading...</div>
          <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon"><i class="fas fa-user-friends"></i></div>
          <div class="stat-number" id="total-users">Loading...</div>
          <div class="stat-label">Total Users</div>
        </div>
      </div>

      <!-- Manage Books -->
      <section class="manage-books">
        <div class="table-header">
          <h3><i class="fas fa-book"></i> Manage Books</h3>
          <a href="javascript:void(0);" class="btn" onclick="showAddBookForm()">Add New Book</a>
        </div>
        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Title</th>
                <th>Author</th>
                <th>Price</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="booksTable">
              <!-- Books loaded via AJAX -->
            </tbody>
          </table>
        </div>
      </section>

      <!-- Add/Edit Book Form -->
      <section class="book-form-section" id="bookFormSection" style="display: none;">
        <div class="book-form-card">
          <div class="card-header">
            <h3 id="bookFormTitle">Add New Book</h3>
            <button class="btn btn-sm" onclick="hideBookForm()">×</button>
          </div>
          <form class="book-form" action="api.php?action=add-book" method="post" enctype="multipart/form-data">
            <input type="hidden" name="book_id" id="editBookId">

            <div class="form-group">
              <label for="bookTitle">Title</label>
              <input type="text" name="title" id="bookTitle" required>
            </div>

            <div class="form-group">
              <label for="bookAuthor">Author</label>
              <input type="text" name="author" id="bookAuthor" required>
            </div>

            <div class="form-group">
              <label for="bookPrice">Price</label>
              <input type="number" step="0.01" name="price" id="bookPrice" required>
            </div>

            <div class="form-group">
              <label for="bookCategory">Category</label>
              <select name="category" id="bookCategory">
                <option value="fiction">Fiction</option>
                <option value="non-fiction">Non-Fiction</option>
                <option value="science">Science</option>
                <option value="fantasy">Fantasy</option>
                <option value="biography">Biography</option>
              </select>
            </div>

            <div class="form-group">
              <label for="bookStock">Stock</label>
              <input type="number" name="stock" id="bookStock" required min="0">
            </div>

            <div class="form-group">
              <label for="bookCover">Book Cover</label>
              <input type="file" name="cover_image" id="bookCover">
              <p class="form-text">Accepted: JPG, PNG. Max 2MB</p>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary"><?php echo isset($existingBook) ? 'Update Book' : 'Add Book'; ?></button>
              <button type="button" class="btn btn-secondary" onclick="hideBookForm()">Cancel</button>
            </div>
          </form>
        </div>
      </section>
    </div>
  </section>
  <?php endif; ?>
  <?php endif; ?>

  <script>
    // Check auth status
    document.addEventListener('DOMContentLoaded', function() {
      const isLoggedIn = '<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>';

      if (!isLoggedIn) {
        // Show auth section, hide admin
        document.querySelectorAll('.admin-section, .manage-books').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.auth-section').forEach(el => el.style.display = 'block');
      } else {
        // Check if admin
        const userRole = '<?php echo $_SESSION['user_role'] ?? 'user'; ?>';
        if (userRole !== 'admin') {
          document.querySelectorAll('.admin-section').forEach(el => el.style.display = 'none');
        }
        // Show stats and books via AJAX
        loadStats();
        loadBooks();
      }
    });

    // Load statistics via AJAX
    function loadStats() {
      fetch('api.php?action=get-stats')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            document.getElementById('total-books').textContent = data.stats.totalBooks || 0;
            document.getElementById('total-orders').textContent = data.stats.totalOrders || 0;
            document.getElementById('total-users').textContent = data.stats.totalUsers || 0;
          }
        })
        .catch(err => console.error('Error loading stats:', err));
    }

    // Load books via AJAX
    function loadBooks() {
      fetch('api.php?action=list-books')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const tbody = document.getElementById('booksTable');
            tbody.innerHTML = '';

            if (data.books && data.books.length > 0) {
              data.books.forEach((book, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                  <td>${index + 1}</td>
                  <td>${book.title}</td>
                  <td>${book.author}</td>
                  <td>$${parseFloat(book.price).toFixed(2)}</td>
                  <td>${book.category}</td>
                  <td>${book.stock}</td>
                  <td>
                    <button class="btn btn-sm" onclick="editBook(${book.id})">Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteBook(${book.id})">Delete</button>
                  </td>
                `;
                tbody.appendChild(tr);
              });
            } else {
              tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No books found</td></tr>';
            }
          }
        })
        .catch(err => console.error('Error loading books:', err));
    }

    // Show add book form
    window.showAddBookForm = function() {
      hideBookForm();
      document.getElementById('bookFormSection').style.display = 'block';
      document.getElementById('bookFormTitle').textContent = 'Add New Book';
      document.getElementById('editBookId').value = '';
      document.getElementById('bookTitle').value = '';
      document.getElementById('bookAuthor').value = '';
      document.getElementById('bookPrice').value = '';
      document.getElementById('bookCategory').value = 'fiction';
      document.getElementById('bookStock').value = '0';
      document.getElementById('bookCover').value = '';
    };

    // Hide add book form
    window.hideBookForm = function() {
      const section = document.getElementById('bookFormSection');
      if (section) section.style.display = 'none';
    };

    // Edit book
    window.editBook = function(bookId) {
      hideBookForm();
      document.getElementById('bookFormSection').style.display = 'block';
      document.getElementById('bookFormTitle').textContent = 'Edit Book';
      document.getElementById('editBookId').value = bookId;

      fetch(`api.php?action=get-book&id=${bookId}`)
        .then(response => response.json())
        .then(data => {
          if (data.success && data.book) {
            const b = data.book;
            document.getElementById('bookTitle').value = b.title;
            document.getElementById('bookAuthor').value = b.author;
            document.getElementById('bookPrice').value = b.price;
            document.getElementById('bookCategory').value = b.category;
            document.getElementById('bookStock').value = b.stock;
            // Set existing cover image preview
            const coverDiv = document.querySelector('.cover-preview');
            if (coverDiv && b.cover_image) {
              coverDiv.innerHTML = `<img src="books/${b.cover_image}" alt="Book cover" style="max-width: 100px; max-height: 150px;">`;
            }
          }
        })
        .catch(err => console.error('Error fetching book:', err));
    };

    // Delete book
    window.deleteBook = function(bookId) {
      if (confirm('Are you sure you want to delete this book?')) {
        fetch('api.php?action=delete-book', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({id: bookId})
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Book deleted successfully');
            loadBooks();
            loadStats();
          } else {
            alert('Failed to delete book: ' + (data.message || ''));
          }
        })
        .catch(err => console.error('Error deleting book:', err));
      }
    }

    // Form submission
    document.querySelector('.book-form').addEventListener('submit', function(e) {
      const form = this;
      const formData = new FormData(form);

      fetch('api.php?action=' + (document.getElementById('editBookId').value ? 'edit-book' : 'add-book'), {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert(data.message || 'Success');
          hideBookForm();
          loadBooks();
          loadStats();
        } else {
          alert('Error: ' + (data.message || 'Unknown error'));
        }
      })
      .catch(err => {
        console.error('Error:', err);
        alert('Error submitting form');
      });

      e.preventDefault();
    });
  </script>
</body>
</html>