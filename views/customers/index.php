<?php
// views/customers/index.php
// این یک فایل نمای ساده برای مدیریت مشتریان است.

// متغیرهایی مانند $pageTitle, $customers_array, $success_message, $error_message, $customer_to_edit
// و همچنین متغیرهای فیلتر و صفحه‌بندی ($search_term, $state_filter, $sort_by, $sort_order, $page, $total_pages, $all_states)
// از کنترلر (CustomerController) ارسال می‌شوند.
// این فایل شامل header و footer نمی‌شود، زیرا توسط کنترلر مدیریت می‌شوند.

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>مدیریت مشتریان</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
        <i class="fas fa-plus"></i> افزودن مشتری جدید
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
    <form action="customers" method="GET" class="row g-3 align-items-end">
        <div class="col-md-5">
            <label for="search_term" class="form-label">جستجو (نام، ایمیل، شماره همراه، اینستاگرام):</label>
            <input type="text" class="form-control rounded-pill" id="search_term" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="جستجو...">
        </div>
        <div class="col-md-4">
            <label for="state_filter" class="form-label">استان:</label>
            <select class="form-select rounded-pill" id="state_filter" name="state_filter">
                <option value="">همه استان‌ها</option>
                <?php
                // all_states is passed from controller
                foreach ($all_states as $state) {
                    $selected = ($state_filter === $state) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($state) . '" ' . $selected . '>' . htmlspecialchars($state) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="sort_by" class="form-label">مرتب‌سازی بر اساس:</label>
            <select class="form-select rounded-pill" id="sort_by" name="sort_by">
                <option value="created_at" <?php echo ($sort_by === 'created_at') ? 'selected' : ''; ?>>تاریخ ثبت نام</option>
                <option value="first_name" <?php echo ($sort_by === 'first_name') ? 'selected' : ''; ?>>نام</option>
                <option value="last_name" <?php echo ($sort_by === 'last_name') ? 'selected' : ''; ?>>نام خانوادگی</option>
                <option value="email" <?php echo ($sort_by === 'email') ? 'selected' : ''; ?>>ایمیل</option>
                <option value="phone" <?php echo ($sort_by === 'phone') ? 'selected' : ''; ?>>شماره همراه</option>
                <option value="id" <?php echo ($sort_by === 'id') ? 'selected' : ''; ?>>شناسه</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="sort_order" class="form-label">ترتیب:</label>
            <select class="form-select rounded-pill" id="sort_order" name="sort_order">
                <option value="DESC" <?php echo ($sort_order === 'DESC') ? 'selected' : ''; ?>>نزولی</option>
                <option value="ASC" <?php echo ($sort_order === 'ASC') ? 'selected' : ''; ?>>صعودی</option>
            </select>
        </div>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-secondary rounded-pill">اعمال فیلتر</button>
            <a href="customers" class="btn btn-outline-secondary rounded-pill">پاک کردن فیلترها</a>
        </div>
    </form>
</div>

<div class="card shadow-lg p-3">
    <h4>لیست مشتریان</h4>
    <hr>
    <?php if (!empty($customers_array) && is_array($customers_array)): ?>
        <div class="table-responsive">
            <!-- [تغییر] کلاس‌های text-center و align-middle برای وسط‌چین کردن اضافه شد -->
            <table class="table table-hover table-striped text-center align-middle">
                <thead>
                    <tr>
                        <th scope="col">شناسه مشتری</th>
                        <th scope="col">نام</th>
                        <th scope="col">ایمیل</th>
                        <th scope="col">آی‌دی اینستاگرام</th>
                        <th scope="col">شماره همراه</th>
                        <th scope="col">کد پستی</th>
                        <th scope="col">استان</th>
                        <th scope="col">شهر</th>
                        <th scope="col" style="min-width: 120px;">تاریخ ثبت نام</th>
                        <th scope="col" style="min-width: 150px;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers_array as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['id']); ?></td>
                            <td><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></td>
                            <td class="text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($customer['email'] ?? 'N/A'); ?></td>
                            <td class="text-truncate" style="max-width: 100px;"><?php echo htmlspecialchars($customer['instagram_id'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($customer['postal_code'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($customer['state'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($customer['city'] ?? 'N/A'); ?></td>
                            <td style="white-space: nowrap;"><?php echo htmlspecialchars($customer['created_at']); ?></td>
                            <td>
                                <!-- [تغییر] کلاس justify-content-center برای وسط‌چین کردن دکمه‌ها اضافه شد -->
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" class="btn btn-sm btn-info text-white edit-customer-btn"
                                            data-bs-toggle="modal" data-bs-target="#editCustomerModal"
                                            data-customer='<?php echo htmlspecialchars(json_encode($customer), ENT_QUOTES, 'UTF-8'); ?>'>
                                        ویرایش
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-customer-btn"
                                            data-bs-toggle="modal" data-bs-target="#deleteCustomerModal"
                                            data-customer-id="<?php echo htmlspecialchars($customer['id']); ?>"
                                            data-customer-name="<?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>">
                                        حذف
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <nav aria-label="Customer page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <!-- لینک صفحه قبلی -->
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link"
                       href="customers?page=<?php echo $page - 1 . (!empty($search_term) ? '&search=' . urlencode($search_term) : '') . (!empty($state_filter) ? '&state_filter=' . urlencode($state_filter) : '') . (!empty($sort_by) ? '&sort_by=' . urlencode($sort_by) : '') . (!empty($sort_order) ? '&sort_order=' . urlencode($sort_order) : ''); ?>"
                       aria-label="Previous">
                        <span aria-hidden="true">«</span>
                    </a>
                </li>

                <!-- لینک‌های شماره صفحات -->
                <?php for ($x = 1; $x <= $total_pages; $x++): ?>
                    <li class="page-item <?php echo ($x == $page) ? 'active' : ''; ?>">
                        <a class="page-link"
                           href="customers?page=<?php echo $x . (!empty($search_term) ? '&search=' . urlencode($search_term) : '') . (!empty($state_filter) ? '&state_filter=' . urlencode($state_filter) : '') . (!empty($sort_by) ? '&sort_by=' . urlencode($sort_by) : '') . (!empty($sort_order) ? '&sort_order=' . urlencode($sort_order) : ''); ?>">
                            <?php echo $x; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- لینک صفحه بعدی -->
                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                    <a class="page-link"
                       href="customers?page=<?php echo $page + 1 . (!empty($search_term) ? '&search=' . urlencode($search_term) : '') . (!empty($state_filter) ? '&state_filter=' . urlencode($state_filter) : '') . (!empty($sort_by) ? '&sort_by=' . urlencode($sort_by) : '') . (!empty($sort_order) ? '&sort_order=' . urlencode($sort_order) : ''); ?>"
                       aria-label="Next">
                        <span aria-hidden="true">»</span>
                    </a>
                </li>
            </ul>
        </nav>

    <?php else: ?>
        <div class="alert alert-info text-center" role="alert">
            هیچ مشتری هنوز ثبت نشده است. از دکمه "افزودن مشتری جدید" برای شروع استفاده کنید.
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addCustomerModalLabel">افزودن مشتری جدید</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="customers" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_customer">
                    <input type="hidden" name="current_page" value="<?php echo htmlspecialchars($page); ?>">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                    <input type="hidden" name="state_filter" value="<?php echo htmlspecialchars($state_filter ?? ''); ?>">
                    <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($sort_by); ?>">
                    <input type="hidden" name="sort_order" value="<?php echo htmlspecialchars($sort_order); ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName" class="form-label">نام (اختیاری):</label>
                            <input type="text" class="form-control rounded-pill" id="firstName" name="first_name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastName" class="form-label">نام خانوادگی (اختیاری):</label>
                            <input type="text" class="form-control rounded-pill" id="lastName" name="last_name">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="customerEmail" class="form-label">ایمیل (اختیاری):</label>
                        <input type="email" class="form-control rounded-pill" id="customerEmail" name="email">
                    </div>

                    <div class="mb-3">
                        <label for="instagramId" class="form-label">آی‌دی اینستاگرام (اختیاری):</label>
                        <input type="text" class="form-control rounded-pill" id="instagramId" name="instagram_id">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customerPhone" class="form-label">شماره همراه (اختیاری):</label>
                            <input type="tel" class="form-control rounded-pill" id="customerPhone" name="phone" placeholder="مثال: 09121234567" pattern="09[0-9]{9}" title="لطفاً شماره موبایل معتبر (۱۱ رقمی با ۰۹) وارد کنید.">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="customerPostalCode" class="form-label">کد پستی (اختیاری):</label>
                            <input type="text" class="form-control rounded-pill" id="customerPostalCode" name="postal_code" pattern="[0-9]{10}" title="لطفاً کد پستی ۱۰ رقمی وارد کنید.">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="customerState" class="form-label">استان (اختیاری):</label>
                            <select class="form-select rounded-pill" id="customerState" name="state">
                                <option value="">انتخاب استان...</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="customerCity" class="form-label">شهر (اختیاری):</label>
                            <select class="form-select rounded-pill" id="customerCity" name="city" disabled>
                                <option value="">ابتدا استان را انتخاب کنید</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="customerAddressDetail" class="form-label">ادامه آدرس (بلوار، کوچه، پلاک و... - اختیاری):</label>
                            <textarea class="form-control rounded-lg" id="customerAddressDetail" name="address_detail" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary rounded-pill">ثبت مشتری</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="editCustomerModalLabel">ویرایش مشتری</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="customers" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_customer">
                    <input type="hidden" name="customer_id" id="editCustomerId">
                    <input type="hidden" name="current_page" value="<?php echo htmlspecialchars($page); ?>">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                    <input type="hidden" name="state_filter" value="<?php echo htmlspecialchars($state_filter ?? ''); ?>">
                    <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($sort_by); ?>">
                    <input type="hidden" name="sort_order" value="<?php echo htmlspecialchars($sort_order); ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editFirstName" class="form-label">نام (اختیاری):</label>
                            <input type="text" class="form-control rounded-pill" id="editFirstName" name="edit_first_name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editLastName" class="form-label">نام خانوادگی (اختیاری):</label>
                            <input type="text" class="form-control rounded-pill" id="editLastName" name="edit_last_name">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editCustomerEmail" class="form-label">ایمیل (اختیاری):</label>
                            <input type="email" class="form-control rounded-pill" id="editCustomerEmail" name="edit_email">
                    </div>

                    <div class="mb-3">
                        <label for="editInstagramId" class="form-label">آی‌دی اینستاگرام (اختیاری):</label>
                            <input type="text" class="form-control rounded-pill" id="editInstagramId" name="edit_instagram_id">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editCustomerPhone" class="form-label">شماره همراه (اختیاری):</label>
                            <input type="tel" class="form-control rounded-pill" id="editCustomerPhone" name="edit_phone" placeholder="مثال: 09121234567" pattern="09[0-9]{9}" title="لطفاً شماره موبایل معتبر (۱۱ رقمی با ۰۹) وارد کنید.">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editCustomerPostalCode" class="form-label">کد پستی (اختیاری):</label>
                            <input type="text" class="form-control rounded-pill" id="editCustomerPostalCode" name="edit_postal_code" pattern="[0-9]{10}" title="لطفاً کد پستی ۱۰ رقمی وارد کنید.">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="editCustomerState" class="form-label">استان (اختیاری):</label>
                            <select class="form-select rounded-pill" id="editCustomerState" name="edit_state">
                                <option value="">انتخاب استان...</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="editCustomerCity" class="form-label">شهر (اختیاری):</label>
                            <select class="form-select rounded-pill" id="editCustomerCity" name="edit_city" disabled>
                                <option value="">ابتدا استان را انتخاب کنید</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="editCustomerAddressDetail" class="form-label">ادامه آدرس (بلوار، کوچه، پلاک و... - اختیاری):</label>
                            <textarea class="form-control rounded-lg" id="editCustomerAddressDetail" name="edit_address_detail" rows="3"></textarea>
                        </div>
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

<div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-labelledby="deleteCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteCustomerModalLabel">تایید حذف مشتری</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="customers" method="POST" class="d-inline">
                <div class="modal-body">
                    <p>آیا از حذف مشتری "<strong id="deleteCustomerNamePlaceholder"></strong>" اطمینان دارید؟</p>
                    <p class="text-danger">توجه: این عملیات غیرقابل بازگشت است.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">انصراف</button>
                    <input type="hidden" name="action" value="delete_customer">
                    <input type="hidden" name="delete_customer_id" id="deleteCustomerIdConfirm">
                    <input type="hidden" name="current_page" value="<?php echo htmlspecialchars($page); ?>">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                    <input type="hidden" name="state_filter" value="<?php echo htmlspecialchars($state_filter ?? ''); ?>">
                    <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($sort_by); ?>">
                    <input type="hidden" name="sort_order" value="<?php echo htmlspecialchars($sort_order); ?>">
                    <button type="submit" class="btn btn-danger rounded-pill">حذف کن</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const provincesAndCities = {
        "آذربایجان شرقی": ["تبریز", "مراغه", "مرند", "اهر", "میانه", "بناب", "شبستر", "جلفا", "عجب‌شیر", "آذرشهر", "سراب", "بستان‌آباد", "هریس", "کلیبر", "ورزقان", "اسکو", "تسوج", "خسروشهر"],
        "آذربایجان غربی": ["ارومیه", "خوی", "بوکان", "مهاباد", "سلماس", "میاندوآب", "نقده", "پیرانشهر", "شاهین‌دژ", "تکاب", "ماکو", "سردشت", "چالدران", "شوط", "پلدشت", "اشنویه"],
        "اردبیل": ["اردبیل", "پارس‌آباد", "مشگین‌شهر", "خلخال", "نمین", "گرمی", "بیله‌سوار", "کوثر", "سرعین", "نیر", "اصلاندوز"],
        "اصفهان": ["اصفهان", "کاشان", "خمینی‌شهر", "نجف‌آباد", "فولادشهر", "شاهین‌شهر", "شهرضا", "مبارکه", "زرین‌شهر", "گلپایگان", "فلاورجان", "آران و بیدگل", "سمیرم", "خوانسار", "نائین", "نطنز", "اردستان", "چادگان"],
        "البرز": ["کرج", "فردیس", "کمال‌شهر", "نظرآباد", "محمدشهر", "ساوجبلاغ", "اشتهارد", "طالقان", "چهارباغ"],
        "ایلام": ["ایلام", "دهلران", "ایوان", "آبدانان", "دره‌شهر", "مهران", "چرداول", "ملکشاهی", "سیروان", "بدره"],
        "بوشهر": ["بوشهر", "برازجان", "گناوه", "خورموج", "کنگان", "جم", "دیر", "دیلم", "دشتستان", "تنگستان"],
        "تهران": ["تهران", "ورامین", "شهریار", "اسلامشهر", "پاکدشت", "قدس", "رباط‌کریم", "ملارد", "قرچک", "ری", "دماوند", "فیروزکوه", "پردیس", "بومهن", "اندیشه", "لواسان", "فشم", "کهریزک", "جوادآباد", "پیشوا"],
        "چهارمحال و بختیاری": ["شهرکرد", "بروجن", "فرخ‌شهر", "فارسان", "هفشجان", "لردگان", "اردل", "کوهرنگ", "کیار", "سامان", "بن"],
        "خراسان جنوبی": ["بیرجند", "قاین", "فردوس", "طبس", "نهبندان", "سرایان", "سربیشه", "بشرویه", "درمیان", "خوسف", "زیرکوه"],
        "خراسان رضوی": ["مشهد", "نیشابور", "سبزوار", "تربت حیدریه", "قوچان", "کاشمر", "گناباد", "تربت جام", "چناران", "سرخس", "خواف", "بردسکن", "تایباد", "کلات", "فریمان", "رشتخوار", "درگز", "طرقبه شاندیز", "بینالود"],
        "خراسان شمالی": ["بجنورد", "شیروان", "اسفراین", "گرمه", "آشخانه", "جاجرم", "مانه و سملقان", "راز و جرگلان", "فاروج"],
        "خوزستان": ["اهواز", "دزفول", "آبادان", "خرمشهر", "ماهشهر", "اندیمشک", "ایذه", "بهبهان", "مسجدسلیمان", "شوشتر", "سوسنگرد", "شادگان", "رامشیر", "هندیجان", "گتوند", "شوش", "لالی", "باوی", "حمیدیه", "هویزه", "میداود", "امیدیه", "باغ ملک"],
        "زنجان": ["زنجان", "ابهر", "خرمدره", "قیدار", "طارم", "خدابنده", "ماهنشان", "سلطانیه", "ایجرود"],
        "سمنان": ["سمنان", "شاهرود", "دامغان", "گرمسار", "مهدی‌شهر", "میامی", "آرادان", "سرخه"],
        "سیستان و بلوچستان": ["زاهدان", "چابهار", "ایرانشهر", "سراوان", "خاش", "زابل", "کنارک", "نیک‌شهر", "سیب و سوران", "مهرستان", "سرباز", "دلگان", "زهک", "فنوج", "قصرقند", "بزمان", "بنت", "دشتیاری", "لاشار"],
        "فارس": ["شیراز", "مرودشت", "جهرم", "فسا", "کازرون", "داراب", "فیروزآباد", "آباده", "نی‌ریز", "اقلید", "سپیدان", "لامرد", "ممسنی", "لارستان", "گراش", "اوز", "خرامه", "کوار", "فراشبند", "خرم‌بید"],
        "قزوین": ["قزوین", "تاکستان", "الوند", "محمدیه", "آبیک", "بوئین‌زهرا", "آوج", "البرز"],
        "قم": ["قم", "جعفریه", "دستجرد", "کهک", "سلفچگان", "قنوات"],
        "کردستان": ["سنندج", "سقز", "مریوان", "بانه", "قروه", "بیجار", "کامیاران", "دیواندره", "سروآباد", "دهگلان"],
        "کرمان": ["کرمان", "سیرجان", "رفسنجان", "جیرفت", "بم", "زرند", "بافت", "بردسیر", "کهنوج", "منوجان", "قلعه گنج", "راور", "کوهبنان", "فهرج", "ریگان", "نرماشیر", "رودبار جنوب", "عنبرآباد", "رابر", "شهربابک"],
        "کرمانشاه": ["کرمانشاه", "اسلام‌آباد غرب", "سنقر", "هرسین", "کنگاور", "پاوه", "جوانرود", "صحنه", "سرپل ذهاب", "قصر شیرین", "روانسر", "گیلانغرب", "دالاهو", "ثلاث باباجانی"],
        "کهگیلویه و بویراحمد": ["یاسوج", "گچساران", "دهدشت", "سی‌سخت", "چرام", "باشت", "لنده", "بهمئی", "دنا"],
        "گلستان": ["گرگان", "گنبد کاووس", "علی‌آباد کتول", "کردکوی", "آق‌قلا", "بندر ترکمن", "آزادشهر", "مینودشت", "کلاله", "رامیان", "گالیکش", "گمیشان", "مراوه‌تپه"],
        "گیلان": ["رشت", "بندر انزلی", "لاهیجان", "تالش", "فومن", "لنگرود", "رودسر", "صومعه‌سرا", "آستانه اشرفیه", "آستارا", "رضوانشهر", "ماسال", "رودبار", "سیاهکل", "املش", "شفت", "خمام", "کوچصفهان"],
        "لرستان": ["خرم‌آباد", "بروجرد", "دورود", "کوهدشت", "الیگودرز", "ازنا", "دلفان", "پلدختر", "سلسله", "رومشکان", "چگنی"],
        "مازندران": ["ساری", "بابل", "آمل", "قائم‌شهر", "بهشهر", "چالوس", "بابلسر", "تنکابن", "نور", "نوشهر", "محمودآباد", "نکا", "رامسر", "جویبار", "فریدونکنار", "سوادکوه", "میاندورود", "عباس‌آباد"],
        "مرکزی": ["اراک", "ساوه", "خمین", "محلات", "دلیجان", "زرندیه", "تفرش", "آشتیان", "کمیجان", "فراهان", "شازند", "خنداب"],
        "هرمزگان": ["بندرعباس", "میناب", "دهبارز", "بندر لنگه", "قشم", "کیش", "جاسک", "پارسیان", "رودان", "حاجی‌آباد", "بستک", "ابوموسی", "سیریک", "بندر خمیر", "بشاگرد"],
        "همدان": ["همدان", "ملایر", "نهاوند", "تویسرکان", "اسدآباد", "کبودرآهنگ", "بهار", "رزن", "فامنین", "درگزین"],
        "یزد": ["یزد", "میبد", "اردکان", "بافق", "تفت", "اشکذر", "ابرکوه", "مهریز", "خاتم", "صدوق", "بهاباد"]
    };

    const customerStateSelect = document.getElementById('customerState');
    const customerCitySelect = document.getElementById('customerCity');
    const addCustomerModal = document.getElementById('addCustomerModal');
    const editCustomerModal = document.getElementById('editCustomerModal');
    const deleteCustomerModal = document.getElementById('deleteCustomerModal');

    // تابع برای پر کردن Dropdown استان‌ها
    function populateStates(targetSelectElement, selectedState = '') {
        targetSelectElement.innerHTML = '<option value="">انتخاب استان...</option>';
        for (const state in provincesAndCities) {
            const option = document.createElement('option');
            option.value = state;
            option.textContent = state;
            targetSelectElement.appendChild(option);
        }
        if (selectedState) {
            targetSelectElement.value = selectedState;
        }
    }

    // تابع برای پر کردن Dropdown شهرها بر اساس استان انتخاب شده
    function populateCities(targetStateSelectElement, targetCitySelectElement, selectedCity = '') {
        const selectedState = targetStateSelectElement.value;
        targetCitySelectElement.innerHTML = '<option value="">ابتدا استان را انتخاب کنید</option>';
        targetCitySelectElement.disabled = true; // Disabled by default

        if (selectedState && provincesAndCities[selectedState]) {
            provincesAndCities[selectedState].forEach(city => {
                const option = document.createElement('option');
                option.value = city;
                option.textContent = city;
                targetCitySelectElement.appendChild(option);
            });
            targetCitySelectElement.disabled = false; // Enable if state selected
        }

        if (selectedCity) {
            targetCitySelectElement.value = selectedCity;
        } else {
            targetCitySelectElement.value = ''; // Ensure native select value is empty if no city is selected
        }
    }

    // رویداد listener برای تغییر انتخاب استان در مودال افزودن
    $(customerStateSelect).on('change', function() {
        populateCities(customerStateSelect, customerCitySelect);
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
            $('#customerCity').val('').trigger('change.select2'); // Reset city on state change
        }
    });

    // هنگام باز شدن مودال افزودن، Dropdown استان‌ها را پر کن و شهرها را ریست کن
    addCustomerModal.addEventListener('show.bs.modal', function() {
        // ریست کردن سایر فیلدهای فرم هنگام باز شدن مودال
        document.getElementById('firstName').value = '';
        document.getElementById('lastName').value = '';
        document.getElementById('customerEmail').value = '';
        document.getElementById('instagramId').value = '';
        document.getElementById('customerPhone').value = '';
        document.getElementById('customerPostalCode').value = '';
        document.getElementById('customerAddressDetail').value = '';

        populateStates(customerStateSelect); // پر کردن استان‌ها (بدون انتخاب پیش‌فرض)
        populateCities(customerStateSelect, customerCitySelect); // پر کردن شهرها بر اساس استان پیش‌فرض (که خالی است)

        // Manually trigger Select2 updates after populating for add modal
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
            $('#customerState').val('').trigger('change.select2');
            $('#customerCity').val('').trigger('change.select2');
        }
    });

    // منطق ریست کردن فرم افزودن هنگام بسته شدن مودال
    addCustomerModal.addEventListener('hide.bs.modal', function() {
        // برای Select2 بهتر است از متدهای آن برای ریست استفاده شود
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
            $('#customerState').val('').trigger('change.select2');
            $('#customerCity').val('').trigger('change.select2');
        } else {
            customerStateSelect.value = '';
            customerCitySelect.value = '';
            customerCitySelect.disabled = true;
        }
        document.getElementById('firstName').value = '';
        document.getElementById('lastName').value = '';
        document.getElementById('customerEmail').value = '';
        document.getElementById('instagramId').value = '';
        document.getElementById('customerPhone').value = '';
        document.getElementById('customerPostalCode').value = '';
        document.getElementById('customerAddressDetail').value = '';
    });

    // JavaScript برای مودال ویرایش مشتری
    editCustomerModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var customerData = JSON.parse(button.getAttribute('data-customer'));

        document.getElementById('editCustomerId').value = customerData.id;
        document.getElementById('editFirstName').value = customerData.first_name;
        document.getElementById('editLastName').value = customerData.last_name;
        document.getElementById('editCustomerEmail').value = customerData.email || '';
        document.getElementById('editInstagramId').value = customerData.instagram_id || '';
        document.getElementById('editCustomerPhone').value = customerData.phone;
        document.getElementById('editCustomerPostalCode').value = customerData.postal_code || '';
        document.getElementById('editCustomerAddressDetail').value = customerData.address_detail;

        const editCustomerStateSelect = $('#editCustomerState'); // Use jQuery object for Select2
        const editCustomerCitySelect = $('#editCustomerCity');     // Use jQuery object for Select2

        // [FIXED]: ابتدا تمام شنونده‌های 'change' را از Select2 استان حذف کنید تا تداخل ایجاد نشود.
        editCustomerStateSelect.off('change.myCustomStateChange'); // Off with namespace

        // Destroy and re-initialize Select2 for both state and city
        if (editCustomerStateSelect.data('select2')) {
            editCustomerStateSelect.select2('destroy');
        }
        if (editCustomerCitySelect.data('select2')) {
            editCustomerCitySelect.select2('destroy');
        }

        // 1. Populate states dropdown with the customer's state
        populateStates(editCustomerStateSelect[0], customerData.state);
        // 2. Initialize Select2 on the state dropdown (after populating options)
        editCustomerStateSelect.select2({ width: '100%', dropdownParent: $('#editCustomerModal') });
        // 3. Set the state value for Select2 and trigger change to ensure visual update and city population
        // Use setTimeout to ensure Select2 has fully rendered before setting value.
        // This is a common workaround for Select2 rendering issues.
        setTimeout(() => {
            editCustomerStateSelect.val(customerData.state).trigger('change');
        }, 50); // تأخیر کوتاه برای رندر Select2

        // 4. Populate cities dropdown based on the customer's state, and set the customer's city
        // Note: populateCities will be triggered by the 'change' event on stateSelect.val().trigger('change') above.
        // So, populateCities doesn't need to be called explicitly here with customerData.city
        // Instead, the triggered 'change' event on state will handle populating cities.
        // We will then set the city value.

        // 5. Initialize Select2 on the city dropdown (after options are populated by state change)
        editCustomerCitySelect.select2({ width: '100%', dropdownParent: $('#editCustomerModal') });
        // 6. Set the city value for Select2 and trigger change for visual update
        // Use setTimeout to ensure Select2 has fully rendered before attempting to set value.
        setTimeout(() => {
            editCustomerCitySelect.val(customerData.city).trigger('change');
        }, 100); // Slightly longer delay for city to ensure state has processed

        // 7. Re-add the change listener for editCustomerStateSelect for user interactions
        editCustomerStateSelect.on('change.myCustomStateChange', function() {
            populateCities(this, editCustomerCitySelect[0]);
            // When state changes by user, reset city Select2 value and display
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                editCustomerCitySelect.val('').trigger('change.select2');
            }
        });
    });

    // منطق ریست کردن فرم ویرایش هنگام بسته شدن مودال
    editCustomerModal.addEventListener('hide.bs.modal', function() {
        document.getElementById('editCustomerId').value = '';
        document.getElementById('editFirstName').value = '';
        document.getElementById('editLastName').value = '';
        document.getElementById('editCustomerEmail').value = '';
        document.getElementById('editInstagramId').value = '';
        document.getElementById('editCustomerPhone').value = '';
        document.getElementById('editCustomerPostalCode').value = '';
        document.getElementById('editCustomerAddressDetail').value = '';

        // برای Select2 بهتر است از متدهای آن برای ریست استفاده شود
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
            $('#editCustomerState').val('').trigger('change.select2');
            $('#editCustomerCity').val('').trigger('change.select2');
        } else {
            customerStateSelect.value = '';
            customerCitySelect.value = '';
            customerCitySelect.disabled = true;
        }
        // حذف شنونده رویداد change با namespace در هنگام بسته شدن مودال
        $('#editCustomerState').off('change.myCustomStateChange');
    });

    // JavaScript برای مودال تأیید حذف مشتری
    deleteCustomerModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var customerId = button.getAttribute('data-customer-id');
        var customerName = button.getAttribute('data-customer-name');

        document.getElementById('deleteCustomerIdConfirm').value = customerId;
        document.getElementById('deleteCustomerNamePlaceholder').textContent = customerName;
    });

    // فعال کردن Select2 برای فیلدهای مشتری و محصول (نیاز به بارگذاری کتابخانه Select2)
    // اگر از Select2 استفاده می‌کنید، مطمئن شوید که کتابخانه jQuery و Select2 در header.php بارگذاری شده‌اند.
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
        // برای مودال افزودن مشتری
        $('#customerState').select2({ width: '100%', dropdownParent: $('#addCustomerModal') });
        $('#customerCity').select2({ width: '100%', dropdownParent: $('#addCustomerModal') });

        // برای مودال ویرایش مشتری
        // Select2 initialization for edit modal is now handled within its 'show.bs.modal' event listener
        // to ensure it's re-initialized correctly with data.
    } else {
        console.warn("Select2 library not loaded. Search functionality will not be available for dropdowns.");
    }

    // [NEW] اگر open_add_modal یا open_edit_modal در PHP فعال باشد، مودال را باز کن
    <?php if (($open_add_modal ?? false) || ($open_edit_modal ?? false)): ?>
        setTimeout(() => {
            <?php if ($open_add_modal ?? false): ?>
                const addModalInstance = bootstrap.Modal.getInstance(addCustomerModal) || new bootstrap.Modal(addCustomerModal);
                addModalInstance.show();
            <?php elseif ($open_edit_modal ?? false): ?>
                // اگر از PHP به دلیل خطای اعتبارسنجی به اینجا رسیده‌ایم،
                // داده‌ها در $customer_to_edit موجود هستند.
                // پس با استفاده از تابع populateEditCustomerModal آن را پر می‌کنیم و باز می‌کنیم.
                const customerDataFromPHP = <?php echo json_encode($customer_to_edit ?? null); ?>;
                if (customerDataFromPHP) {
                    // Populate the form fields (excluding state/city selects which are handled by Select2)
                    document.getElementById('editCustomerId').value = customerDataFromPHP.id || '';
                    document.getElementById('editFirstName').value = customerDataFromPHP.first_name || '';
                    document.getElementById('editLastName').value = customerDataFromPHP.last_name || '';
                    document.getElementById('editCustomerEmail').value = customerDataFromPHP.email || '';
                    document.getElementById('editInstagramId').value = customerDataFromPHP.instagram_id || '';
                    document.getElementById('editCustomerPhone').value = customerDataFromPHP.phone || '';
                    document.getElementById('editCustomerPostalCode').value = customerDataFromPHP.postal_code || '';
                    document.getElementById('editCustomerAddressDetail').value = customerDataFromPHP.address_detail || '';

                    // Handle Select2 for state and city
                    const editCustomerStateSelect = $('#editCustomerState');
                    const editCustomerCitySelect = $('#editCustomerCity');

                    // Destroy and re-initialize Select2s (important for correct behavior)
                    if (editCustomerStateSelect.data('select2')) {
                        editCustomerStateSelect.select2('destroy');
                    }
                    if (editCustomerCitySelect.data('select2')) {
                        editCustomerCitySelect.select2('destroy');
                    }

                    // Populate and set values for state and city using Select2
                    populateStates(editCustomerStateSelect[0], customerDataFromPHP.state);
                    editCustomerStateSelect.select2({ width: '100%', dropdownParent: $('#editCustomerModal') });
                    editCustomerStateSelect.val(customerDataFromPHP.state).trigger('change.select2'); // Trigger change for cities

                    // Wait a bit for cities to populate then set city value
                    setTimeout(() => {
                        populateCities(editCustomerStateSelect[0], editCustomerCitySelect[0], customerDataFromPHP.city);
                        editCustomerCitySelect.select2({ width: '100%', dropdownParent: $('#editCustomerModal') });
                        editCustomerCitySelect.val(customerDataFromPHP.city).trigger('change.select2');
                    }, 50); // Small delay

                    // Re-attach change listener for state after initialization
                    editCustomerStateSelect.on('change.myCustomStateChange', function() {
                        populateCities(this, editCustomerCitySelect[0]);
                        editCustomerCitySelect.val('').trigger('change.select2'); // Reset city
                    });

                    // Show the modal
                    const editModalInstance = bootstrap.Modal.getInstance(editCustomerModal) || new bootstrap.Modal(editCustomerModal);
                    editModalInstance.show();
                }
            <?php endif; ?>
        }, 200); // General delay to ensure all DOM is ready
    <?php endif; ?>
});
</script>	