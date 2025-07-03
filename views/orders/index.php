<?php
// views/orders/index.php
// این یک فایل نمای ساده برای مدیریت سفارشات است.

// متغیرهایی مانند $pageTitle, $orders_array, $all_customers, $all_products
// و همچنین متغیرهای فیلتر و صفحه‌بندی ($search_term, $customer_filter_id, $status_filter, $shipping_method_filter, $sort_by, $sort_order, $page, $total_pages, $order_statuses, $shipping_methods)
// از کنترلر (OrderController) ارسال می‌شوند.
// این فایل شامل header و footer نمی‌شود، زیرا توسط کنترلر مدیریت می‌شوند.

// [اصلاح شده] تعریف معادل فارسی برای وضعیت‌های جدید
$status_translations = [
    'processing' => 'در حال انجام',
    'shipped' => 'ارسال شده',
    'cancelled' => 'لغو شده'
];

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>مدیریت سفارشات</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOrderModal">
        <i class="fas fa-plus"></i> افزودن سفارش جدید
    </button>
</div>

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

<div class="card shadow-sm mb-4 p-3">
    <h5 class="card-title">فیلتر و جستجو</h5>
    <form action="orders" method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label for="search_term" class="form-label">جستجو (مشتری، محصول، یادداشت، کد رهگیری):</label>
            <input type="text" class="form-control rounded-pill" id="search_term" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="جستجو...">
        </div>
        <div class="col-md-4">
            <label for="customer_filter" class="form-label">مشتری:</label>
            <select class="form-select rounded-pill" id="customer_filter" name="customer_filter">
                <option value="">همه مشتریان</option>
                <?php
                foreach ($all_customers as $customer) {
                    $selected = ($customer_filter_id == $customer['id']) ? 'selected' : '';
                    // استفاده از full_name_display برای نمایش نام کامل مشتری به همراه ID
                    echo '<option value="' . htmlspecialchars($customer['id']) . '" ' . $selected . '>' . htmlspecialchars($customer['full_name_display']) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="status_filter" class="form-label">وضعیت سفارش:</label>
            <select class="form-select rounded-pill" id="status_filter" name="status_filter">
                <option value="">همه وضعیت‌ها</option>
                <?php
                // [اصلاح شده] استفاده از معادل فارسی برای نمایش در دراپ‌داون فیلتر
                foreach ($order_statuses as $status_key) {
                    $selected = ($status_filter === $status_key) ? 'selected' : '';
                    $persian_status = $status_translations[$status_key] ?? htmlspecialchars($status_key);
                    echo '<option value="' . htmlspecialchars($status_key) . '" ' . $selected . '>' . $persian_status . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="shipping_method_filter" class="form-label">روش ارسال:</label>
            <select class="form-select rounded-pill" id="shipping_method_filter" name="shipping_method_filter">
                <option value="">همه روش‌ها</option>
                <?php
                foreach ($shipping_methods as $method) {
                    $selected = ($shipping_method_filter === $method) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($method) . '" ' . $selected . '>' . htmlspecialchars($method) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="sort_by" class="form-label">مرتب‌سازی بر اساس:</label>
            <select class="form-select rounded-pill" id="sort_by" name="sort_by">
                <option value="created_at" <?php echo ($sort_by === 'created_at') ? 'selected' : ''; ?>>تاریخ ایجاد</option>
                <option value="order_date_shamsi" <?php echo ($sort_by === 'order_date_shamsi') ? 'selected' : ''; ?>>تاریخ سفارش</option>
                <option value="total_amount" <?php echo ($sort_by === 'total_amount') ? 'selected' : ''; ?>>مبلغ کل</option>
                <option value="id" <?php echo ($sort_by === 'id') ? 'selected' : ''; ?>>شناسه</option>
            </select>
        </div>
        <div class="col-md-4">
            <label for="sort_order" class="form-label">ترتیب:</label>
            <select class="form-select rounded-pill" id="sort_order" name="sort_order">
                <option value="DESC" <?php echo ($sort_order === 'DESC') ? 'selected' : ''; ?>>نزولی</option>
                <option value="ASC" <?php echo ($sort_order === 'ASC') ? 'selected' : ''; ?>>صعودی</option>
            </select>
        </div>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-secondary rounded-pill">اعمال فیلتر</button>
            <a href="orders" class="btn btn-outline-secondary rounded-pill">پاک کردن فیلترها</a>
        </div>
    </form>
</div>

<div class="card shadow-lg p-3">
    <h4>لیست سفارشات</h4>
    <hr>
    <?php if (!empty($orders_array) && is_array($orders_array)): ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped text-center align-middle">
                <thead>
                    <tr>
                        <th scope="col">شناسه سفارش</th>
                        <th scope="col">مشتری</th>
                        <th scope="col">محصولات</th>
                        <th scope="col">تاریخ سفارش</th>
                        <th scope="col">وضعیت</th>
                        <th scope="col">مبلغ کل</th>
                        <th scope="col">مبلغ پرداخت شده</th>
                        <th scope="col">مانده</th>
                        <th scope="col" style="min-width: 150px;">عملیات</th> 
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders_array as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name'] ?? 'نامشخص'); ?></td>
                            <td><?php echo htmlspecialchars($order['product_names'] ?? 'بدون محصول'); ?></td> 
                            <td><?php echo htmlspecialchars($order['order_date_shamsi']); ?></td>
                            <td>
                                <?php
                                    $status_key = $order['status'];
                                    // [اصلاح شده] استفاده از ترجمه‌های جدید
                                    $persian_status = $status_translations[$status_key] ?? htmlspecialchars($status_key);
                                    echo $persian_status;
                                ?>
                            </td>
                            <td><?php echo number_format($order['total_amount']); ?> تومان</td>
                            <td><?php echo number_format($order['deposit_amount']); ?> تومان</td>
                            <?php $remaining = $order['total_amount'] - $order['deposit_amount']; ?>
                            <td class="fw-bold <?php echo ($remaining > 0) ? 'text-danger' : 'text-success'; ?>">
                                <?php echo number_format($remaining); ?> تومان
                            </td>
                            <td style="vertical-align: middle;">
                                <div class="d-flex gap-1 justify-content-center"> 
                                    <button type="button" class="btn btn-sm btn-warning text-white edit-order-btn"
                                            data-bs-toggle="modal" data-bs-target="#editOrderModal"
                                            data-order-id="<?php echo htmlspecialchars($order['id']); ?>">
                                        ویرایش
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-order-btn"
                                            data-bs-toggle="modal" data-bs-target="#deleteOrderModal"
                                            data-order-id="<?php echo htmlspecialchars($order['id']); ?>"
                                            data-order-name="<?php echo htmlspecialchars('سفارش #' . $order['id']); ?>">
                                        حذف
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <nav aria-label="Order page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                    <?php
                    // Build query parameters for pagination links
                    $pagination_query_params = [];
                    if (!empty($search_term)) $pagination_query_params['search'] = urlencode($search_term);
                    if ($customer_filter_id !== null) $pagination_query_params['customer_filter'] = $customer_filter_id;
                    if ($status_filter !== null) $pagination_query_params['status_filter'] = urlencode($status_filter);
                    if ($shipping_method_filter !== null) $pagination_query_params['shipping_method_filter'] = urlencode($shipping_method_filter);
                    if (!empty($sort_by)) $pagination_query_params['sort_by'] = urlencode($sort_by);
                    if (!empty($sort_order)) $pagination_query_params['sort_order'] = urlencode($sort_order);

                    $prev_page_query_string = http_build_query(array_merge($pagination_query_params, ['page' => $page - 1]));
                    ?>
                    <a class="page-link"
                       href="orders?<?php echo $prev_page_query_string; ?>"
                       aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php for ($x = 1; $x <= $total_pages; $x++):
                    $current_page_query_string = http_build_query(array_merge($pagination_query_params, ['page' => $x]));
                ?>
                    <li class="page-item <?php echo ($x == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="orders?<?php echo $current_page_query_string; ?>"><?php echo $x; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                    <?php
                    $next_page_query_string = http_build_query(array_merge($pagination_query_params, ['page' => $page + 1]));
                    ?>
                    <a class="page-link"
                       href="orders?<?php echo $next_page_query_string; ?>"
                       aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>

    <?php else: ?>
        <div class="alert alert-info text-center" role="alert">
            هیچ سفارشی هنوز ثبت نشده است. از دکمه "افزودن سفارش جدید" برای شروع استفاده کنید.
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="addOrderModal" tabindex="-1" aria-labelledby="addOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl"> <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addOrderModalLabel">افزودن سفارش جدید</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="orders" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_order">
                    <input type="hidden" name="current_page" value="<?php echo htmlspecialchars($page); ?>">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                    <input type="hidden" name="customer_filter" value="<?php echo htmlspecialchars($customer_filter_id ?? ''); ?>">
                    <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($status_filter ?? ''); ?>">
                    <input type="hidden" name="shipping_method_filter" value="<?php echo htmlspecialchars($shipping_method_filter ?? ''); ?>">
                    <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($sort_by); ?>">
                    <input type="hidden" name="sort_order" value="<?php echo htmlspecialchars($sort_order); ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customerSelect" class="form-label">نام و نام خانوادگی مشتری:</label>
                            <select class="form-select rounded-pill" id="customerSelect" name="customer_id" required>
                                <option value="">انتخاب مشتری...</option>
                                <?php
                                foreach ($all_customers as $customer) {
                                    echo '<option value="' . htmlspecialchars($customer['id']) . '">' .
                                        htmlspecialchars($customer['full_name_display']) .
                                        '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="orderDateDisplay" class="form-label">تاریخ ثبت سفارش (شمسی):</label>
                            <input type="text" class="form-control rounded-pill" id="orderDateDisplay" name="order_date" data-jdp required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="shippingMethod" class="form-label">نوع ارسال:</label>
                            <select class="form-select rounded-pill" id="shippingMethod" name="shipping_method">
                                <option value="">انتخاب روش ارسال...</option>
                                <option value="post">پست</option>
                                <option value="chapart">چاپار</option>
                                <option value="tipax">تیپاکس</option>
                                <option value="delivery_company">شرکت پستی دیگر</option>
                                <option value="pickup">تحویل حضوری</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="trackingCode" class="form-label">کد رهگیری (اختیاری):</label>
                            <input type="text" class="form-control rounded-pill" id="trackingCode" name="tracking_code">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="orderStatus" class="form-label">وضعیت سفارش:</label>
                            <!-- [اصلاح شده] گزینه‌های وضعیت سفارش به‌روز شد -->
                            <select class="form-select rounded-pill" id="orderStatus" name="order_status" required>
                                <option value="processing">در حال انجام</option>
                                <option value="shipped">ارسال شده</option>
                                <option value="cancelled">لغو شده</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="estimatedDeliveryDateDisplay" class="form-label">تاریخ حدودی ارسال (شمسی):</label>
                            <input type="text" class="form-control rounded-pill" id="estimatedDeliveryDateDisplay" name="estimated_delivery_date" data-jdp>
                        </div>
                    </div>

                    <hr>
                    <h5 class="mb-3">افزودن محصولات:</h5>
                    <div id="product-items-container">
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm mt-3" id="addProductItemBtn">
                        <i class="fas fa-plus"></i> افزودن محصول دیگر
                    </button>

                    <hr>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="totalAmount" class="form-label">مبلغ کل سفارش (تومان):</label>
                            <input type="text" class="form-control rounded-pill" id="totalAmount" name="total_amount_display" readonly>
                            <input type="hidden" id="totalAmountRaw" name="total_amount_raw">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="downPayment" class="form-label">پیش پرداخت (تومان):</label>
                            <input type="text" class="form-control rounded-pill" id="downPayment" name="down_payment_display" value="0">
                            <input type="hidden" id="downPaymentRaw" name="down_payment_raw" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="remainingAmount" class="form-label">باقیمانده مبلغ (تومان):</label>
                            <input type="text" class="form-control rounded-pill" id="remainingAmount" name="remaining_amount_display" readonly>
                            <input type="hidden" id="remainingAmountRaw" name="remaining_amount_raw">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="adminNotes" class="form-label">یادداشت‌های ادمین (اختیاری):</label>
                        <textarea class="form-control rounded-lg" id="adminNotes" name="admin_notes" rows="3"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary rounded-pill">ثبت سفارش</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="editOrderModal" tabindex="-1" aria-labelledby="editOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl"> <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="editOrderModalLabel">ویرایش سفارش #<span id="editOrderIdDisplay"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="orders" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_order">
                    <input type="hidden" name="order_id" id="editOrderId"> <input type="hidden" name="current_page" value="<?php echo htmlspecialchars($page); ?>">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                    <input type="hidden" name="customer_filter" value="<?php echo htmlspecialchars($customer_filter_id ?? ''); ?>">
                    <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($status_filter ?? ''); ?>">
                    <input type="hidden" name="shipping_method_filter" value="<?php echo htmlspecialchars($shipping_method_filter ?? ''); ?>">
                    <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($sort_by); ?>">
                    <input type="hidden" name="sort_order" value="<?php echo htmlspecialchars($sort_order); ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editCustomerSelect" class="form-label">نام و نام خانوادگی مشتری:</label>
                            <select class="form-select rounded-pill" id="editCustomerSelect" name="customer_id" required>
                                <option value="">انتخاب مشتری...</option>
                                <?php
                                foreach ($all_customers as $customer) {
                                    echo '<option value="' . htmlspecialchars($customer['id']) . '">' .
                                        htmlspecialchars($customer['full_name_display']) .
                                        '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editOrderDateDisplay" class="form-label">تاریخ ثبت سفارش (شمسی):</label>
                            <input type="text" class="form-control rounded-pill" id="editOrderDateDisplay" name="order_date" data-jdp required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editShippingMethod" class="form-label">نوع ارسال:</label>
                            <select class="form-select rounded-pill" id="editShippingMethod" name="shipping_method">
                                <option value="">انتخاب روش ارسال...</option>
                                <option value="post">پست</option>
                                <option value="chapart">چاپار</option>
                                <option value="tipax">تیپاکس</option>
                                <option value="delivery_company">شرکت پستی دیگر</option>
                                <option value="pickup">تحویل حضوری</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editTrackingCode" class="form-label">کد رهگیری (اختیاری):</label>
                            <input type="text" class="form-control rounded-pill" id="editTrackingCode" name="tracking_code">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editOrderStatus" class="form-label">وضعیت سفارش:</label>
                            <!-- [اصلاح شده] گزینه‌های وضعیت سفارش به‌روز شد -->
                            <select class="form-select rounded-pill" id="editOrderStatus" name="order_status" required>
                                <option value="processing">در حال انجام</option>
                                <option value="shipped">ارسال شده</option>
                                <option value="cancelled">لغو شده</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="editEstimatedDeliveryDateDisplay" class="form-label">تاریخ حدودی ارسال (شمسی):</label>
                            <input type="text" class="form-control rounded-pill" id="editEstimatedDeliveryDateDisplay" name="estimated_delivery_date" data-jdp>
                        </div>
                    </div>

                    <hr>
                    <h5 class="mb-3">محصولات سفارش:</h5>
                    <div id="edit-product-items-container">
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm mt-3" id="editAddProductItemBtn">
                        <i class="fas fa-plus"></i> افزودن محصول دیگر
                    </button>

                    <hr>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="editTotalAmount" class="form-label">مبلغ کل سفارش (تومان):</label>
                            <input type="text" class="form-control rounded-pill" id="editTotalAmount" name="total_amount_display" readonly>
                            <input type="hidden" id="editTotalAmountRaw" name="total_amount_raw">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="editDownPayment" class="form-label">پیش پرداخت (تومان):</label>
                            <input type="text" class="form-control rounded-pill" id="editDownPayment" name="down_payment_display" value="0">
                            <input type="hidden" id="editDownPaymentRaw" name="down_payment_raw" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="editRemainingAmount" class="form-label">باقیمانده مبلغ (تومان):</label>
                            <input type="text" class="form-control rounded-pill" id="editRemainingAmount" name="remaining_amount_display" readonly>
                            <input type="hidden" id="editRemainingAmountRaw" name="remaining_amount_raw">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editAdminNotes" class="form-label">یادداشت‌های ادمین (اختیاری):</label>
                        <textarea class="form-control rounded-lg" id="editAdminNotes" name="admin_notes" rows="3"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-info text-white rounded-pill">ذخیره تغییرات</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="viewOrderModal" tabindex="-1" aria-labelledby="viewOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl"> <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewOrderModalLabel">جزئیات سفارش #<span id="viewOrderId"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>مشتری:</strong> <span id="viewOrderCustomerName"></span></p>
                        <p><strong>تاریخ ثبت سفارش:</strong> <span id="viewOrderDate"></span></p>
                        <p><strong>وضعیت:</strong> <span id="viewOrderStatus"></span></p>
                        <p><strong>نوع ارسال:</strong> <span id="viewOrderShippingMethod"></span></p>
                        <p><strong>تاریخ حدودی ارسال:</strong> <span id="viewOrderEstimatedDeliveryDate"></span></p>
                        <p><strong>کد رهگیری:</strong> <span id="viewOrderTrackingCode"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>مبلغ کل:</strong> <span id="viewOrderTotalAmount"></span> تومان</p>
                        <p><strong>پیش پرداخت:</strong> <span id="viewOrderDownPayment"></span> تومان</p>
                        <p><strong>باقیمانده:</strong> <span id="viewOrderRemainingAmount"></span> تومان</p>
                    </div>
                </div>
                <h6 class="mt-4">یادداشت ادمین:</h6>
                <p id="viewOrderAdminNotes"></p>
                <hr>
                <h6 class="mt-4">محصولات سفارش:</h6>
                <div id="viewOrderItemsContainer" class="list-group">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">بستن</button>
                <button type="button" class="btn btn-info text-white rounded-pill" id="openEditOrderModalBtn">ویرایش سفارش</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteOrderModal" tabindex="-1" aria-labelledby="deleteOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteOrderModalLabel">تایید حذف سفارش</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="orders" method="POST" class="d-inline">
                <div class="modal-body">
                    <p>آیا از حذف سفارش "<strong id="deleteOrderNamePlaceholder"></strong>" اطمینان دارید؟</p>
                    <p class="text-danger">توجه: این عملیات غیرقابل بازگشت است.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">انصراف</button>
                    <input type="hidden" name="action" value="delete_order">
                    <input type="hidden" name="delete_order_id" id="deleteOrderIdConfirm">
                    <button type="submit" class="btn btn-danger rounded-pill">حذف کن</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="module">
// Import the formatter function
import { initializeAmountFormatter } from '<?php echo BASE_URL; ?>assets/js/input-formatter.js';

document.addEventListener('DOMContentLoaded', function() {
    // General DOM elements
    const addOrderModal = document.getElementById('addOrderModal');
    const customerSelect = document.getElementById('customerSelect');
    const productItemsContainer = document.getElementById('product-items-container');
    const addProductItemBtn = document.getElementById('addProductItemBtn');
    const totalAmountInput = document.getElementById('totalAmount');
    const totalAmountRawInput = document.getElementById('totalAmountRaw');
    const downPaymentInput = document.getElementById('downPayment');
    const downPaymentRawInput = document.getElementById('downPaymentRaw');
    const remainingAmountInput = document.getElementById('remainingAmount');
    const remainingAmountRawInput = document.getElementById('remainingAmountRaw');
    const orderStatusSelect = document.getElementById('orderStatus');
    const orderDateDisplayInput = document.getElementById('orderDateDisplay');
    const shippingMethodSelect = document.getElementById('shippingMethod');
    const trackingCodeInput = document.getElementById('trackingCode'); // Keep track of it for initial state/reset
    const estimatedDeliveryDateDisplayInput = document.getElementById('estimatedDeliveryDateDisplay');
    const adminNotesTextarea = document.getElementById('adminNotes');
    const deleteOrderModal = document.getElementById('deleteOrderModal');
    const viewOrderModal = document.getElementById('viewOrderModal');

    // Edit Modal Elements
    const editOrderModal = document.getElementById('editOrderModal');
    const editOrderIdDisplay = document.getElementById('editOrderIdDisplay');
    const editOrderIdInput = document.getElementById('editOrderId');
    const editCustomerSelect = document.getElementById('editCustomerSelect');
    const editOrderDateDisplayInput = document.getElementById('editOrderDateDisplay');
    const editShippingMethodSelect = document.getElementById('editShippingMethod');
    const editTrackingCodeInput = document.getElementById('editTrackingCode');
    const editOrderStatusSelect = document.getElementById('editOrderStatus');
    const editEstimatedDeliveryDateDisplayInput = document.getElementById('editEstimatedDeliveryDateDisplay');
    const editAdminNotesTextarea = document.getElementById('editAdminNotes');
    const editProductItemsContainer = document.getElementById('edit-product-items-container');
    const editAddProductItemBtn = document.getElementById('editAddProductItemBtn');
    const editTotalAmountInput = document.getElementById('editTotalAmount');
    const editTotalAmountRawInput = document.getElementById('editTotalAmountRaw');
    const editDownPaymentInput = document.getElementById('editDownPayment');
    const editDownPaymentRawInput = document.getElementById('editDownPaymentRaw');
    const editRemainingAmountInput = document.getElementById('editRemainingAmount');
    const editRemainingAmountRawInput = document.getElementById('editRemainingAmountRaw');


    let itemCounter = 0;
    let editItemCounter = 0;

    // All products data from PHP
    const allProducts = <?php echo json_encode($all_products ?? []); ?>;
    // Pass all_customers with full_name_display to JavaScript
    const allCustomers = <?php echo json_encode($all_customers ?? []); ?>;

    // Helper function to create options for Select2 dropdowns
    function formatCustomerOption(customer) {
        // Select2 passes an object with 'text' and 'id' properties.
        // If it's an actual option element, 'element' property will exist.
        if (customer.id) { // Check if customer object has an ID (i.e., it's a real customer, not just a placeholder)
            const foundCustomer = allCustomers.find(c => c.id == customer.id);
            if (foundCustomer) {
                // Extract only the name part, removing " (ID: X)"
                const fullNameWithId = foundCustomer.full_name_display;
                const nameOnly = fullNameWithId.replace(/\s+\(ID: \d+\)$/, '');
                return nameOnly;
            }
        }
        // If customer.text already contains the ID (e.g., from initial load of the select options before Select2 takes over),
        // we should also clean it.
        const textOnly = (customer.text || '').replace(/\s+\(ID: \d+\)$/, '');
        return textOnly;
    }

    // Helper to populate customer select dropdown (can be reused for add/edit)
    function populateCustomerSelect(targetSelectElement, selectedCustomerId = '') {
        targetSelectElement.innerHTML = '<option value="">انتخاب مشتری...</option>';
        allCustomers.forEach(customer => {
            const option = document.createElement('option');
            option.value = customer.id;
            // Extract only the name part, removing " (ID: X)"
            const fullNameWithId = customer.full_name_display;
            const nameOnly = fullNameWithId.replace(/\s+\(ID: \d+\)$/, '');
            option.textContent = nameOnly; // Use name only here
            targetSelectElement.appendChild(option);
        });
        if (selectedCustomerId) {
            targetSelectElement.value = selectedCustomerId;
        }
    }

    // تابع برای افزودن یک ردیف محصول جدید به فرم (برای افزودن/ویرایش سفارش)
    function addProductItem(productId = '', quantity = 1, customDescription = '', priceAtOrder = '', targetContainer, isEdit = false) {
        const currentId = isEdit ? editItemCounter : itemCounter;
        const productsOptions = allProducts.map(product => {
            const selected = (product.id == productId) ? 'selected' : '';
            // Only display product name
            return `<option value="${product.id}" data-price="${product.price}" ${selected}>${product.name}</option>`;
        }).join('');

        const prefix = isEdit ? 'edit' : '';
        const itemHtml = `
            <div class="card p-3 mb-2 product-item" data-item-id="${currentId}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">محصول ${currentId + 1}</h6>
                    <button type="button" class="btn btn-danger btn-sm remove-product-item-btn"><i class="fas fa-times"></i> حذف</button>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="${prefix}productSelect-${currentId}" class="form-label">انتخاب محصول:</label>
                        <select class="form-select rounded-pill product-select" id="${prefix}productSelect-${currentId}" name="product_ids[]" required>
                            <option value="">انتخاب محصول...</option>
                            ${productsOptions}
                        </select>
                        <input type="hidden" name="prices_at_order[]" id="${prefix}priceAtOrder-${currentId}" value="${priceAtOrder}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="${prefix}productQuantity-${currentId}" class="form-label">تعداد:</label>
                        <input type="number" class="form-control rounded-pill product-quantity" id="${prefix}productQuantity-${currentId}" name="quantities[]" value="${quantity}" min="1" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="${prefix}itemTotalPrice-${currentId}" class="form-label">مبلغ جزئی (تومان):</label>
                        <input type="text" class="form-control rounded-pill item-total-price" id="${prefix}itemTotalPrice-${currentId}" value="${(priceAtOrder * quantity).toLocaleString('en-US')}" readonly>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="${prefix}customDescription-${currentId}" class="form-label">توضیحات سفارشی محصول (اختیاری):</label>
                    <textarea class="form-control rounded-lg" id="${prefix}customDescription-${currentId}" name="custom_descriptions[]" rows="2">${customDescription}</textarea>
                </div>
            </div>
        `;
        targetContainer.insertAdjacentHTML('beforeend', itemHtml);

        const currentProductItemElement = targetContainer.lastElementChild;
        const currentProductSelectElement = currentProductItemElement.querySelector(`.product-select`);
        const currentProductQuantityInput = currentProductItemElement.querySelector(`.product-quantity`);

        // Initialize Select2 for the newly added product select
        if ($(currentProductSelectElement).data('select2')) {
            $(currentProductSelectElement).select2('destroy');
        }
        $(currentProductSelectElement).select2({
            width: '100%',
            dropdownParent: isEdit ? $('#editOrderModal') : $('#addOrderModal')
        });

        if (productId) {
            $(currentProductSelectElement).val(productId).trigger('change.select2');
        } else {
            $(currentProductSelectElement).val('').trigger('change.select2');
        }

        // Event listeners for the new item
        $(currentProductSelectElement).on('change', isEdit ? calculateEditOrderTotal : calculateOrderTotal);
        currentProductQuantityInput.addEventListener('input', isEdit ? calculateEditOrderTotal : calculateOrderTotal);
        currentProductItemElement.querySelector('.remove-product-item-btn').addEventListener('click', function() {
            currentProductItemElement.remove();
            if (isEdit) calculateEditOrderTotal(); else calculateOrderTotal();
        });

        if (isEdit) editItemCounter++; else itemCounter++;

        if (isEdit) calculateOrderTotal(); else calculateOrderTotal();
    }

    // تابع برای محاسبه مبلغ کل سفارش و باقیمانده (افزودن سفارش)
    function calculateOrderTotal() {
        let total = 0;
        document.querySelectorAll('#product-items-container .product-item').forEach(item => {
            const productSelect = item.querySelector('.product-select');
            const quantityInput = item.querySelector('.product-quantity');
            const priceAtOrderInput = item.querySelector('input[name="prices_at_order[]"]');
            const itemTotalPriceInput = item.querySelector('.item-total-price');

            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const productPrice = parseFloat(selectedOption?.getAttribute('data-price') || 0);
            const quantity = parseInt(quantityInput.value || 0);

            let itemTotal = productPrice * quantity;
            if (isNaN(itemTotal)) itemTotal = 0;

            priceAtOrderInput.value = productPrice;
            itemTotalPriceInput.value = itemTotal.toLocaleString('en-US');
            total += itemTotal;
        });

        const downPayment = parseFloat(downPaymentRawInput.value || 0);
        const remainingAmount = total - downPayment;

        totalAmountRawInput.value = total;
        totalAmountInput.value = total.toLocaleString('en-US');

        remainingAmountRawInput.value = remainingAmount;
        remainingAmountInput.value = remainingAmount.toLocaleString('en-US');
    }

    // تابع برای محاسبه مبلغ کل سفارش و باقیمانده (ویرایش سفارش)
    function calculateEditOrderTotal() {
        let total = 0;
        document.querySelectorAll('#edit-product-items-container .product-item').forEach(item => {
            const productSelect = item.querySelector('.product-select');
            const quantityInput = item.querySelector('.product-quantity');
            const priceAtOrderInput = item.querySelector('input[name="prices_at_order[]"]');
            const itemTotalPriceInput = item.querySelector('.item-total-price');

            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const productPrice = parseFloat(selectedOption?.getAttribute('data-price') || 0);
            const quantity = parseInt(quantityInput.value || 0);

            let itemTotal = productPrice * quantity;
            if (isNaN(itemTotal)) itemTotal = 0;

            priceAtOrderInput.value = productPrice;
            itemTotalPriceInput.value = itemTotal.toLocaleString('en-US');
            total += itemTotal;
        });

        const downPayment = parseFloat(editDownPaymentRawInput.value || 0);
        const remainingAmount = total - downPayment;

        editTotalAmountRawInput.value = total;
        editTotalAmountInput.value = total.toLocaleString('en-US');

        remainingAmountRawInput.value = remainingAmount;
        editRemainingAmountInput.value = remainingAmount.toLocaleString('en-US');
    }


    // Helper function to format Persian dates for display in view modal
    function formatPersianDateForDisplay(dateString) {
        if (!dateString || dateString === '0000-00-00' || dateString === 'N/A') {
            return 'N/A';
        }
        // Assuming dateString is already in<x_bin_880>/MM/DD Jalali format from PHP
        return dateString;
    }

    // Event listener for "Add Product Item" button (Add Modal)
    addProductItemBtn.addEventListener('click', () => addProductItem('', 1, '', 0, productItemsContainer, false));

    // Event listener for "Add Product Item" button (Edit Modal)
    editAddProductItemBtn.addEventListener('click', () => addProductItem('', 1, '', 0, editProductItemsContainer, true));

    // Initialize formatter for Down Payment field (Add Modal)
    initializeAmountFormatter('downPayment', 'downPaymentRaw');
    downPaymentInput.addEventListener('input', calculateOrderTotal);

    // Initialize formatter for Down Payment field (Edit Modal)
    initializeAmountFormatter('editDownPayment', 'editDownPaymentRaw');
    editDownPaymentInput.addEventListener('input', calculateEditOrderTotal);


    // When add modal shows, reset form and initialize Jalali Datepicker defaults
    addOrderModal.addEventListener('show.bs.modal', function() {
        console.log("Add Order Modal: show.bs.modal event triggered.");
        // Reset main form fields
        customerSelect.value = '';
        orderDateDisplayInput.value = '';
        downPaymentInput.value = '0';
        downPaymentRawInput.value = '0';
        shippingMethodSelect.value = '';
        trackingCodeInput.value = ''; // Keep track of it for initial state/reset
        orderStatusSelect.value = 'pending';
        estimatedDeliveryDateDisplayInput.value = '';
        adminNotesTextarea.value = '';

        // Clear existing product items
        productItemsContainer.innerHTML = '';
        itemCounter = 0;

        // Add at least one product item by default
        addProductItem('', 1, '', 0, productItemsContainer, false);

        // Initialize Select2 for customer select
        if ($(customerSelect).data('select2')) {
            $(customerSelect).select2('destroy');
        }
        $(customerSelect).select2({
            width: '100%',
            dropdownParent: $('#addOrderModal'),
            templateResult: formatCustomerOption, // Use the simplified helper
            templateSelection: formatCustomerOption // Use the simplified helper
        });
        $(customerSelect).val('').trigger('change.select2');

        // Set current date for Jalali Datepicker inputs
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        const todayGregorian = `${year}-${month}-${day}`;

        // Set the value. jalaliDatepicker will automatically convert and display in Jalali.
        orderDateDisplayInput.value = todayGregorian;
        estimatedDeliveryDateDisplayInput.value = todayGregorian;

        // Re-initialize jalaliDatepicker for newly shown modal elements
        if (typeof jalaliDatepicker !== 'undefined') {
            setTimeout(() => {
                // FIX: Pass zIndex option when calling startWatch() inside modal context
                jalaliDatepicker.startWatch({
                    zIndex: 2000 // Ensure z-index is high when re-watching inside modal context
                });
                console.log("Jalali Datepicker startWatch() called again for Add Order Modal.");
            }, 100); // 100ms delay
        }

        // If form_data_add exists (from a validation error), populate the form
        <?php if (!empty($form_data_add) && ($open_add_modal ?? false)): ?>
            const savedFormData = <?php echo json_encode($form_data_add); ?>;

            if (savedFormData.customer_id) {
                $(customerSelect).val(savedFormData.customer_id).trigger('change.select2');
            }
            shippingMethodSelect.value = savedFormData.shipping_method || '';
            trackingCodeInput.value = savedFormData.tracking_code || ''; // Restore tracking code
            orderStatusSelect.value = savedFormData.order_status || 'pending';
            adminNotesTextarea.value = savedFormData.admin_notes || '';

            downPaymentRawInput.value = savedFormData.down_payment_raw || '0';
            downPaymentInput.value = parseFloat(downPaymentRawInput.value).toLocaleString('en-US');

            // Set saved dates for Jalali Datepicker (these should be Jalali strings from PHP)
            orderDateDisplayInput.value = savedFormData.order_date || '';
            estimatedDeliveryDateDisplayInput.value = savedFormData.estimated_delivery_date || '';

            productItemsContainer.innerHTML = '';
            itemCounter = 0;
            if (savedFormData.product_ids && savedFormData.product_ids.length > 0) {
                savedFormData.product_ids.forEach((productId, index) => {
                    addProductItem(
                        productId,
                        savedFormData.quantities[index] || 1,
                        savedFormData.custom_descriptions[index] || '',
                        savedFormData.prices_at_order[index] || 0,
                        productItemsContainer,
                        false
                    );
                });
            } else {
                addProductItem();
            }

            calculateOrderTotal();
            setTimeout(() => {
                const addOrderModalInstance = bootstrap.Modal.getInstance(addOrderModal) || new bootstrap.Modal(addOrderModal);
                addOrderModalInstance.show();
            }, 100);
        <?php endif; ?>
    });

    // When edit modal shows, populate form and re-initialize Select2s and Datepickers
    editOrderModal.addEventListener('show.bs.modal', function(event) {
        console.log("Edit Order Modal: show.bs.modal event triggered.");
        let orderData = null;
        const button = event.relatedTarget;

        // Determine source of order data: from PHP (validation error) or from a "View" button click
        <?php if (!empty($order_to_edit) && ($open_edit_modal ?? false)): ?>
            // Data coming from PHP (e.g., after a failed form submission)
            orderData = <?php echo json_encode($order_to_edit); ?>;
            console.log("Loading edit modal with data from PHP (validation error):", orderData);
            populateEditOrderModal(orderData); // Populate the form fields immediately
        <?php else: ?>
            // Data coming from a "Edit" button click, fetch it via AJAX
            const orderId = button ? button.getAttribute('data-order-id') : null;
            if (!orderId) {
                console.error("No Order ID found to edit.");
                return;
            }
            editOrderIdInput.value = orderId;

            // Clear previous items instantly
            editProductItemsContainer.innerHTML = '';
            editItemCounter = 0;

            // Fetch data from server
            fetch(`orders/view?id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error("Error fetching order details for edit:", data.error);
                        alert("خطا در بارگذاری جزئیات سفارش برای ویرایش.");
                        return;
                    }
                    const orderDetails = data.order_details;
                    // Dates are already converted to Jalali in OrderController::view()
                    // So, directly assign them.
                    orderData = orderDetails;
                    orderData.order_items = data.order_items; // Attach items to orderData

                    populateEditOrderModal(orderData); // Populate the form fields
                })
                .catch(error => {
                    console.error("Error fetching order details for edit:", error);
                    alert("خطا در برقراری ارتباط برای barگذاری جزئیات سفارش.");
                });
            // Don't return here, let the rest of the code execute after fetch if needed.
        <?php endif; ?>
    });

    // Function to populate the edit order modal
    function populateEditOrderModal(orderData) {
        // Set Order ID for display and hidden input
        editOrderIdDisplay.textContent = orderData.id;
        editOrderIdInput.value = orderData.id;

        // Populate main order fields
        // Destroy and re-initialize Select2 for customer select
        if ($(editCustomerSelect).data('select2')) {
            $(editCustomerSelect).select2('destroy');
        }
        // Pass the native DOM element directly for populateCustomerSelect
        populateCustomerSelect(editCustomerSelect[0], orderData.customer_id);
        $(editCustomerSelect).select2({
            width: '100%',
            dropdownParent: $('#editOrderModal'),
            templateResult: formatCustomerOption,
            templateSelection: formatCustomerOption
        });
        // Trigger 'change.select2' on the jQuery object to update visual
        // Use a short timeout to ensure Select2 is fully rendered before setting value
        setTimeout(() => {
            $(editCustomerSelect).val(orderData.customer_id).trigger('change.select2');
        }, 50); // Small delay


        // Set saved dates for Jalali Datepicker
        // order_date_shamsi and shipping_date are already in Jalali format from the controller
        editOrderDateDisplayInput.value = orderData.order_date_shamsi || '';
        editEstimatedDeliveryDateDisplayInput.value = orderData.shipping_date || '';


        editShippingMethodSelect.value = orderData.shipping_method || '';
        editTrackingCodeInput.value = orderData.tracking_code || '';
        editOrderStatusSelect.value = orderData.status || 'pending';
        editAdminNotesTextarea.value = orderData.notes || '';

        // Set raw amounts and then format them for display
        editDownPaymentRawInput.value = orderData.deposit_amount || '0';
        editDownPaymentInput.value = parseFloat(editDownPaymentRawInput.value).toLocaleString('en-US');


        // Populate order items for edit
        editProductItemsContainer.innerHTML = ''; // Clear existing items
        editItemCounter = 0; // Reset item counter for edit modal
        if (orderData.order_items && orderData.order_items.length > 0) {
            orderData.order_items.forEach(item => {
                addProductItem(
                    item.product_id,
                    item.quantity,
                    item.custom_description || '',
                    item.price_at_order || 0,
                    editProductItemsContainer, // Pass target container
                    true // Indicate it's for edit modal
                );
            });
        } else {
            addProductItem('', 1, '', 0, editProductItemsContainer, true); // Add one blank item if no items exist
        }

        // Recalculate totals after populating
        calculateEditOrderTotal();

        // Re-add the change listener for editCustomerSelect for user interactions
        $(editCustomerSelect).off('change.editOrderCustomSelect');
        $(editCustomerSelect).on('change.editOrderCustomSelect', function() {
            // This is for future enhancements if customer selection needs to trigger other logic
        });

        // Open the modal only after all fields are populated (especially important for async fetch)
        setTimeout(() => {
            const editOrderModalInstance = bootstrap.Modal.getInstance(editOrderModal) || new bootstrap.Modal(editOrderModal);
            editOrderModalInstance.show();
        }, 150); // Small delay to ensure all Select2s are ready

        // Re-initialize jalaliDatepicker for newly shown modal elements
        if (typeof jalaliDatepicker !== 'undefined') {
            setTimeout(() => { // Add a small delay for edit modal too
                // FIX: Pass zIndex option when calling startWatch() inside modal context
                jalaliDatepicker.startWatch({
                    zIndex: 2000 // Ensure z-index is high when re-watching inside modal context
                });
                console.log("Jalali Datepicker startWatch() called again for Edit Order Modal.");
            }, 100); // 100ms delay
        }
    }

    // Event listener for delete order buttons (added to dynamic rows)
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('delete-order-btn') || event.target.closest('.delete-order-btn')) {
            const button = event.target.closest('.delete-order-btn');
            const orderId = button.getAttribute('data-order-id');
            const orderName = button.getAttribute('data-order-name');

            document.getElementById('deleteOrderIdConfirm').value = orderId;
            document.getElementById('deleteOrderNamePlaceholder').textContent = orderName;

            const deleteModalInstance = bootstrap.Modal.getInstance(deleteOrderModal) || new bootstrap.Modal(deleteOrderModal);
            deleteModalInstance.show();
        }
    });

    // When edit modal hides, reset its form.
    editOrderModal.addEventListener('hide.bs.modal', function() {
        // Reset all form fields
        editOrderIdDisplay.textContent = '';
        editOrderIdInput.value = '';
        editCustomerSelect.value = '';
        editOrderDateDisplayInput.value = '';
        editShippingMethodSelect.value = '';
        editTrackingCodeInput.value = '';
        editOrderStatusSelect.value = '';
        editEstimatedDeliveryDateDisplayInput.value = '';
        editAdminNotesTextarea.value = '';

        editProductItemsContainer.innerHTML = ''; // Clear product items
        editItemCounter = 0; // Reset counter

        // Destroy and re-initialize Select2s for a clean state
        if ($(editCustomerSelect).data('select2')) {
            $(editCustomerSelect).select2('destroy');
        }
        $(editCustomerSelect).select2({
            width: '100%',
            dropdownParent: $('#editOrderModal'),
            templateResult: formatCustomerOption,
            templateSelection: formatCustomerOption
        });

        $(editCustomerSelect).val('').trigger('change.select2'); // Reset visually

        // Jalali Datepicker does not need explicit destroy/re-initialize like persianDatepicker
        // It manages its own state based on the input field's value and data-jdp attribute.
    });

});
</script>