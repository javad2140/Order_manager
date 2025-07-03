<?php
// views/dashboard/index.php
// این یک فایل نمای ساده برای داشبورد است.

// متغیرهایی مانند $pageTitle از کنترلر (DashboardController) ارسال می‌شوند.
// این فایل شامل header و footer نمی‌شود، زیرا توسط کنترلر مدیریت می‌شوند.
?>

<div class="container-fluid mt-4">
    
    <!-- بخش ناوبری ماهانه و نمایش ماه جاری -->
    <div class="card shadow-sm mb-4 p-3">
        <div class="d-flex justify-content-between align-items-center">
            <a href="?year=<?php echo $display_shamsi_year; ?>&month=<?php echo $display_shamsi_month - 1; ?>" class="btn btn-outline-primary rounded-pill">
                <i class="fas fa-chevron-right"></i> ماه قبل
            </a>
            <h4 class="mb-0">آمار ماه: <?php echo htmlspecialchars($display_month_name); ?> <?php echo htmlspecialchars($display_shamsi_year); ?></h4>
            <a href="?year=<?php echo $display_shamsi_year; ?>&month=<?php echo $display_shamsi_month + 1; ?>" class="btn btn-outline-primary rounded-pill">
                ماه بعد <i class="fas fa-chevron-left"></i>
            </a>
        </div>
    </div>

    <!-- [جدید] بخش آمارهای ماهانه -->
    <div class="row justify-content-center">
        <div class="col-12">
            <h5 class="mb-3 text-muted text-center">آمار ماه جاری</h5>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="card-title text-primary"><i class="fas fa-shopping-cart"></i> کل سفارشات این ماه</h6>
                    <p class="card-text fs-3"><?php echo htmlspecialchars($total_orders_this_month ?? '0'); ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="card-title text-info"><i class="fas fa-user-plus"></i> مشتریان جدید این ماه</h6>
                    <p class="card-text fs-3"><?php echo htmlspecialchars($total_customers_this_month ?? '0'); ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="card-title text-warning"><i class="fas fa-money-bill-wave"></i> مبلغ کل سفارشات این ماه</h6>
                    <p class="card-text fs-3"><?php echo number_format($total_amount_this_month ?? 0); ?> تومان</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <?php $shipped_orders_url = BASE_URL . 'orders?search=&customer_filter=&status_filter=shipped&shipping_method_filter=&sort_by=created_at&sort_order=DESC'; ?>
            <a href="<?php echo $shipped_orders_url; ?>" class="card shadow-sm h-100 border-secondary text-decoration-none text-reset clickable-card">
                <div class="card-body text-center">
                    <!-- [اصلاح شده] عنوان کارت به‌روز شد -->
                    <h6 class="card-title text-secondary"><i class="fas fa-truck"></i> سفارشات ارسال شده</h6>
                    <!-- [اصلاح شده] متغیر جدید جایگزین شد -->
                    <p class="card-text fs-3"><?php echo htmlspecialchars($total_shipped_orders_this_month ?? '0'); ?></p>
                </div>
            </a>
        </div>
    </div>

    <!-- [جدید] بخش آمارهای کلی -->
    <hr class="my-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <h5 class="mb-3 text-muted text-center">آمار کلی</h5>
        </div>
        <div class="col-md-4 mb-4">
            <a href="<?php echo BASE_URL; ?>orders" class="card shadow-sm h-100 text-decoration-none text-reset clickable-card">
                <div class="card-body text-center">
                    <h5 class="card-title"><i class="fas fa-globe-americas"></i> کل سفارشات تا کنون</h5>
                    <p class="card-text fs-3"><?php echo htmlspecialchars($total_orders_all_time ?? '0'); ?></p>
                </div>
            </a>
        </div>
        <div class="col-md-4 mb-4">
            <a href="<?php echo BASE_URL; ?>customers" class="card shadow-sm h-100 text-decoration-none text-reset clickable-card">
                <div class="card-body text-center">
                    <h5 class="card-title"><i class="fas fa-users"></i> کل مشتریان تا کنون</h5>
                    <p class="card-text fs-3"><?php echo htmlspecialchars($total_customers_all_time ?? '0'); ?></p>
                </div>
            </a>
        </div>
        <div class="col-md-4 mb-4">
            <a href="<?php echo BASE_URL; ?>products" class="card shadow-sm h-100 text-decoration-none text-reset clickable-card">
                <div class="card-body text-center">
                    <h5 class="card-title"><i class="fas fa-box"></i> کل محصولات</h6>
                    <p class="card-text fs-3"><?php echo htmlspecialchars($total_products_all_time ?? '0'); ?></p>
                </div>
            </a>
        </div>
    </div>

    <!-- فعالیت‌های اخیر -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">فعالیت‌های اخیر</h5>
                    <p>در این بخش، خلاصه‌ای از آخرین فعالیت‌ها و آمارهای مهم نمایش داده می‌شود.</p>
                    <ul>
                        <li>آخرین سفارش: 
                            <?php if ($latest_order): ?>
                                #<?php echo htmlspecialchars($latest_order['id']); ?> (مشتری: <?php echo htmlspecialchars($latest_order['first_name'] . ' ' . $latest_order['last_name']); ?>)
                            <?php else: ?>
                                یافت نشد.
                            <?php endif; ?>
                        </li>
                        <li>آخرین محصول اضافه شده: 
                            <?php if ($latest_product): ?>
                                <?php echo htmlspecialchars($latest_product['name']); ?> (شناسه: <?php echo htmlspecialchars($latest_product['id']); ?>)
                            <?php else: ?>
                                یافت نشد.
                            <?php endif; ?>
                        </li>
                        <li>آخرین کاربر ثبت نام کرده: 
                            <?php if ($latest_customer): ?>
                                <?php echo htmlspecialchars($latest_customer['first_name'] . ' ' . $latest_customer['last_name']); ?> (شناسه: <?php echo htmlspecialchars($latest_customer['id']); ?>)
                            <?php else: ?>
                                یافت نشد.
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
