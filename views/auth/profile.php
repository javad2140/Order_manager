<?php
// This file is a VIEW. It expects variables ($pageTitle, $error_message, $success_message)
// to be set by the calling controller (AuthController->profile()).
require_once __DIR__ . '/../includes/header.php';
?>
<div class="d-flex align-items-center justify-content-center min-vh-100 bg-light-subtle">
    <div class="card shadow-lg p-4" style="max-width: 500px; width: 100%; border-radius: 15px;">
        <div class="card-body">
            <h3 class="card-title text-center mb-4 text-primary"><?php echo htmlspecialchars($pageTitle); ?></h3>
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

            <form action="<?php echo BASE_URL; ?>auth/change-password" method="POST">
                <div class="mb-3">
                    <label for="currentPassword" class="form-label text-muted">رمز عبور فعلی:</label>
                    <input type="password" class="form-control form-control-lg rounded-pill" id="currentPassword" name="current_password" required autocomplete="current-password" placeholder="رمز عبور فعلی خود را وارد کنید">
                </div>
                <div class="mb-3">
                    <label for="newPassword" class="form-label text-muted">رمز عبور جدید:</label>
                    <input type="password" class="form-control form-control-lg rounded-pill" id="newPassword" name="new_password" required autocomplete="new-password" placeholder="رمز عبور جدید (حداقل ۶ کاراکتر)">
                </div>
                <div class="mb-4">
                    <label for="confirmNewPassword" class="form-label text-muted">تایید رمز عبور جدید:</label>
                    <input type="password" class="form-control form-control-lg rounded-pill" id="confirmNewPassword" name="confirm_new_password" required autocomplete="new-password" placeholder="تایید رمز عبور جدید">
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm">تغییر رمز عبور</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>
