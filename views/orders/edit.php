<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="card shadow-sm">
    <div class="card-header bg-warning text-white">
        <h4 class="mb-0">ویرایش سفارش</h4>
    </div>
    <div class="card-body">
        <form action="/orders/update/<?php echo htmlspecialchars($order['id']); ?>" method="POST">
            <div class="mb-3">
                <label for="customer_id" class="form-label">مشتری:</label>
                <select class="form-control" id="customer_id" name="customer_id" required>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo htmlspecialchars($customer['id']); ?>"
                            <?php echo ($customer['id'] == $order['customer_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($customer['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="order_date" class="form-label">تاریخ سفارش:</label>
                <!-- Changed class from persian-datepicker to data-jdp -->
                <input type="text" class="form-control" id="order_date" name="order_date" data-jdp
                       value="<?php echo htmlspecialchars($order['order_date_jalali']); ?>" required>
                <!-- The hidden Gregorian field is no longer needed with data-jdp -->
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">وضعیت:</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="pending" <?php echo ($order['status'] == 'pending') ? 'selected' : ''; ?>>در انتظار</option>
                    <option value="completed" <?php echo ($order['status'] == 'completed') ? 'selected' : ''; ?>>تکمیل شده</option>
                    <option value="cancelled" <?php echo ($order['status'] == 'cancelled') ? 'selected' : ''; ?>>لغو شده</option>
                </select>
            </div>
            <!-- You might want to add fields for order items here later -->
            <button type="submit" class="btn btn-warning">به‌روزرسانی سفارش</button>
            <a href="/orders" class="btn btn-secondary">بازگشت</a>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
