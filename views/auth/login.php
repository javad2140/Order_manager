<?php
// This file is a VIEW. It expects variables ($pageTitle, $error_message, $success_message)
// to be set by the calling controller (AuthController->login()).
// No direct database calls or session management here.

// Include header/footer (relative to views/auth/)
// Go up one level from views/auth/ to views/, then into includes/
require_once __DIR__ . '/../includes/header_login.php'; 

?>
<div class="d-flex align-items-center justify-content-center min-vh-100 bg-light-subtle">
    <div class="card shadow-lg p-4" style="max-width: 400px; width: 100%; border-radius: 15px;">
        <div class="card-body">
            <h3 class="card-title text-center mb-4 text-primary">ورود به سیستم</h3>
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success text-center alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger text-center alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <form action="login" method="POST"> <!-- Action points to clean URL route for login -->
                <div class="mb-3">
                    <label for="username" class="form-label text-muted">نام کاربری:</label>
                    <input type="text" class="form-control form-control-lg rounded-pill" id="username" name="username" required autocomplete="username" placeholder="نام کاربری خود را وارد کنید">
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label text-muted">رمز عبور:</label>
                    <input type="password" class="form-control form-control-lg rounded-pill" id="password" name="password" required autocomplete="current-password" placeholder="رمز عبور خود را وارد کنید">
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm">ورود</button>
                </div>
            </form>
            <p class="text-center mt-3 text-muted"><small>فقط برای دسترسی مدیران</small></p>
            <p class="text-center mt-2">
                <small><a href="register" class="text-decoration-none">ثبت نام مدیر جدید</a></small>
            </p>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php'; 
?>
