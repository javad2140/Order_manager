<?php
// views/backup/index.php
?>

<div class="container-fluid mt-4">
    <h2 class="mb-4">مدیریت پشتیبان‌گیری و بازیابی</h2>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success text-center"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Create Backup Card -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-download fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">ایجاد نسخه پشتیبان</h5>
                    <p class="card-text text-muted">
                        با کلیک بر روی این دکمه، یک نسخه کامل از پایگاه داده (شامل تمام سفارشات، مشتریان، محصولات و...) در قالب یک فایل SQL دانلود خواهد شد. این فایل را در مکانی امن نگهداری کنید.
                    </p>
                    <a href="backup/create" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-download me-2"></i> دانلود نسخه پشتیبان
                    </a>
                </div>
            </div>
        </div>

        <!-- Restore Backup Card -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100 border-danger">
                <div class="card-body text-center">
                    <i class="fas fa-upload fa-3x text-danger mb-3"></i>
                    <h5 class="card-title">بازیابی از نسخه پشتیبان</h5>
                    <p class="card-text text-muted">
                        برای بازیابی اطلاعات از یک فایل پشتیبان، فایل .sql خود را انتخاب کرده و روی دکمه "بازیابی" کلیک کنید.
                    </p>
                    <div class="alert alert-danger">
                        <strong><i class="fas fa-exclamation-triangle"></i> هشدار:</strong> این عملیات تمام داده‌های فعلی شما را حذف کرده و اطلاعات موجود در فایل پشتیبان را جایگزین آن می‌کند. این عمل غیرقابل بازگشت است.
                    </div>
                    <form action="backup/restore" method="post" enctype="multipart/form-data" class="mt-3">
                        <div class="input-group">
                            <input type="file" class="form-control" name="backup_file" id="backup_file" accept=".sql" required>
                            <button class="btn btn-danger" type="submit" onclick="return confirm('آیا مطمئن هستید؟ تمام داده‌های فعلی حذف خواهند شد.');">
                                <i class="fas fa-upload me-2"></i> بازیابی
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
