<?php
// This file is a VIEW, it should only display data received from the controller.
// DO NOT put complex PHP logic, database operations, or form processing here.
// All variables used here ($pageTitle, $success_message, $error_message, etc.)
// are passed from the controller (ProductController->index()).

// Include header (top template) - path adjusted for view file
// From views/products/ to views/includes/
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>مدیریت محصولات</h2>
    <!-- Button to trigger Add Product Modal -->
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
        <i class="fas fa-plus"></i> افزودن محصول جدید
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

<!-- Filter & Search Form -->
<div class="card shadow-sm mb-4 p-3">
    <h5 class="card-title">فیلتر و جستجو</h5>
    <form action="products" method="GET" class="row g-3 align-items-end"> <!-- Action now points to the route "products" -->
        <div class="col-md-4">
            <label for="search_term" class="form-label">جستجو (نام، توضیحات، SKU):</label>
            <input type="text" class="form-control rounded-pill" id="search_term" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="جستجو...">
        </div>
        <div class="col-md-3">
            <label for="category_filter" class="form-label">دسته‌بندی:</label>
            <select class="form-select rounded-pill" id="category_filter" name="category_filter">
                <option value="">همه دسته‌بندی‌ها</option>
                <?php
                // all_categories_flat is passed from controller
                foreach ($all_categories_flat as $cat) {
                    $selected = ($category_filter_id == $cat['id']) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($cat['id']) . '" ' . $selected . '>' . htmlspecialchars($cat['name']) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="col-md-2">
            <label for="status_filter" class="form-label">وضعیت:</label>
            <select class="form-select rounded-pill" id="status_filter" name="status_filter">
                <option value="">همه وضعیت‌ها</option>
                <option value="1" <?php echo ($status_filter === 1) ? 'selected' : ''; ?>>فعال</option>
                <option value="0" <?php echo ($status_filter === 0) ? 'selected' : ''; ?>>غیرفعال</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="sort_by" class="form-label">مرتب‌سازی بر اساس:</label>
            <select class="form-select rounded-pill" id="sort_by" name="sort_by">
                <option value="created_at" <?php echo ($sort_by === 'created_at') ? 'selected' : ''; ?>>تاریخ ایجاد</option>
                <option value="name" <?php echo ($sort_by === 'name') ? 'selected' : ''; ?>>نام</option>
                <option value="price" <?php echo ($sort_by === 'price') ? 'selected' : ''; ?>>قیمت</option>
                <option value="stock" <?php echo ($sort_by === 'stock') ? 'selected' : ''; ?>>موجودی</option>
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
            <a href="products" class="btn btn-outline-secondary rounded-pill">پاک کردن فیلترها</a> 
        </div>
    </form>
</div>

<?php if ($num_products > 0): ?>
    <div class="card shadow-lg p-3">
        <h4>لیست محصولات</h4>
        <hr>
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th scope="col">شناسه</th> 
                        <th scope="col">نام محصول</th>
                        <th scope="col">دسته‌بندی</th>
                        <th scope="col">قیمت</th>
                        <th scope="col">موجودی</th>
                        <th scope="col">وضعیت</th>
                        <th scope="col">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products_array as $row): 
                        // اطمینان حاصل کنید که SKU همیشه وجود دارد، حتی اگر null باشد
                        $product_json = htmlspecialchars(json_encode([
                            'id' => $row['id'],
                            'name' => $row['name'],
                            'sku' => $row['sku'] ?? '', // [FIXED] اطمینان از وجود SKU
                            'price' => $row['price'],
                            'description' => $row['description'] ?? null, 
                            'image_url' => $row['image_url'] ?? null, 
                            'stock' => $row['stock'],
                            'is_active' => $row['is_active'],
                            'category_id' => $row['category_id'] ?? null 
                        ]), ENT_QUOTES, 'UTF-8');
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td> 
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name'] ?? 'بدون دسته‌بندی'); ?></td>
                            <td><?php echo number_format($row['price']); ?> تومان</td>
                            <td><?php echo htmlspecialchars($row['stock']); ?></td>
                            <td>
                                <?php if ($row['is_active']): ?>
                                    <span class="badge bg-success">فعال</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">غیرفعال</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- دکمه ویرایش: data-product شامل همه جزئیات محصول است -->
                                <button type="button" class="btn btn-sm btn-info text-white ms-2 edit-product-btn" 
                                        data-bs-toggle="modal" data-bs-target="#editProductModal" 
                                        data-product='<?php echo $product_json; ?>'>
                                    ویرایش
                                </button>
                                <!-- دکمه حذف: data-product-id و data-product-name برای تایید حذف -->
                                <button type="button" class="btn btn-sm btn-danger delete-product-btn" 
                                        data-bs-toggle="modal" data-bs-target="#deleteProductModal" 
                                        data-product-id="<?php echo htmlspecialchars($row['id']); ?>" 
                                        data-product-name="<?php echo htmlspecialchars($row['name']); ?>">
                                    حذف
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?> 
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination links -->
    <nav aria-label="Product page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" 
                   href="products?page=<?php echo $page - 1; 
                   if (!empty($search_term)) echo "&search=" . urlencode($search_term);
                   if ($category_filter_id !== null) echo "&category_filter=" . $category_filter_id;
                   if ($status_filter !== null) echo "&status_filter=" . $status_filter;
                   if (!empty($sort_by)) echo "&sort_by=" . urlencode($sort_by);
                   if (!empty($sort_order)) echo "&sort_order=" . urlencode($sort_order);
                   ?>" 
                   aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php for ($x = 1; $x <= $total_pages; $x++): 
                $pagination_link = "products?page=" . $x;
                if (!empty($search_term)) $pagination_link .= "&search=" . urlencode($search_term);
                if ($category_filter_id !== null) $pagination_link .= "&category_filter=" . $category_filter_id;
                if ($status_filter !== null) $pagination_link .= "&status_filter=" . $status_filter;
                if (!empty($sort_by)) $pagination_link .= "&sort_by=" . urlencode($sort_by);
                if (!empty($sort_order)) $pagination_link .= "&sort_order=" . urlencode($sort_order);
            ?>
                <li class="page-item <?php echo ($x == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo $pagination_link; ?>"><?php echo $x; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                <a class="page-link" 
                   href="products?page=<?php echo $page + 1; 
                   if (!empty($search_term)) echo "&search=" . urlencode($search_term);
                   if ($category_filter_id !== null) echo "&category_filter=" . $category_filter_id;
                   if ($status_filter !== null) echo "&status_filter=" . $status_filter;
                   if (!empty($sort_by)) echo "&sort_by=" . urlencode($sort_by);
                   if (!empty($sort_order)) echo "&sort_order=" . urlencode($sort_order);
                   ?>" 
                   aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>

<?php else: ?>
    <div class="alert alert-info text-center" role="alert">
        هیچ محصولی هنوز ثبت نشده است. برای شروع، یک محصول اضافه کنید.
    </div>
<?php endif; ?>

<!-- مودال افزودن محصول -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addProductModalLabel">افزودن محصول جدید</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="products" method="POST"> <!-- Action points to the route "products" -->
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_product">
                    <!-- ارسال صفحه فعلی و فیلترها به عنوان ورودی‌های مخفی برای حفظ وضعیت پس از ارسال فرم -->
                    <input type="hidden" name="current_page" value="<?php echo htmlspecialchars($page); ?>"> 
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>"> 
                    <input type="hidden" name="category_filter" value="<?php echo htmlspecialchars($category_filter_id ?? ''); ?>"> 
                    <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($status_filter ?? ''); ?>"> 
                    <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($sort_by); ?>"> 
                    <input type="hidden" name="sort_order" value="<?php echo htmlspecialchars($sort_order); ?>"> 

                    <div class="mb-3">
                        <label for="productName" class="form-label">نام محصول:</label>
                        <input type="text" class="form-control rounded-pill" id="productName" name="product_name" required>
                    </div>
                    <!-- ورودی مخفی برای مقدار خام قیمت -->
                    <input type="hidden" id="productPriceRaw" name="product_price_raw">
                    <div class="mb-3">
                        <label for="productPrice" class="form-label">قیمت (تومان):</label>
                        <!-- نوع به text برای فرمت‌بندی تغییر یافته است -->
                        <input type="text" class="form-control rounded-pill" id="productPrice" name="product_price_display" required min="0">
                    </div>
                    <div class="mb-3">
                        <label for="productStock" class="form-label">موجودی:</label>
                        <input type="number" class="form-control rounded-pill" id="productStock" name="product_stock" required min="0">
                    </div>
                    <div class="mb-3">
                        <label for="productDescription" class="form-label">توضیحات (اختیاری):</label>
                        <textarea class="form-control rounded-lg" id="productDescription" name="product_description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="productImageUrl" class="form-label">لینک تصویر (اختیاری):</label>
                        <input type="url" class="form-control rounded-pill" id="productImageUrl" name="product_image_url" placeholder="مثال: https://example.com/image.jpg">
                    </div>
                    <div class="mb-3">
                        <label for="productCategory" class="form-label">دسته‌بندی:</label>
                        <select class="form-select rounded-pill" id="productCategory" name="product_category_id" required>
                            <option value="">انتخاب دسته‌بندی...</option>
                            <?php
                            // پر کردن دراپ‌داون دسته‌بندی
                            foreach ($all_categories_flat as $cat) {
                                echo '<option value="' . htmlspecialchars($cat['id']) . '">' . htmlspecialchars($cat['name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="productIsActive" name="product_is_active" checked>
                        <label class="form-check-label" for="productIsActive">فعال/غیرفعال</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary rounded-pill">ذخیره محصول</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- پایان مودال افزودن محصول -->

<!-- مودال ویرایش محصول -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="editProductModalLabel">ویرایش محصول</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="products" method="POST"> <!-- Action اکنون به روت "products" اشاره می‌کند -->
                <input type="hidden" name="action" value="edit_product">
                <input type="hidden" name="product_id" id="editProductId">
                <input type="hidden" name="edit_product_sku" id="editProductSku"> 
                <!-- ارسال صفحه فعلی و فیلترها به عنوان ورودی‌های مخفی برای حفظ وضعیت پس از ارسال فرم -->
                <input type="hidden" name="current_page" value="<?php echo htmlspecialchars($page); ?>"> 
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>"> 
                <input type="hidden" name="category_filter" value="<?php echo htmlspecialchars($category_filter_id ?? ''); ?>"> 
                <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($status_filter ?? ''); ?>"> 
                <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($sort_by); ?>"> 
                <input type="hidden" name="sort_order" value="<?php echo htmlspecialchars($sort_order); ?>"> 

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editProductName" class="form-label">نام محصول:</label>
                        <input type="text" class="form-control rounded-pill" id="editProductName" name="edit_product_name" required>
                    </div>
                    <!-- ورودی مخفی برای مقدار خام قیمت -->
                    <input type="hidden" id="editProductPriceRaw" name="edit_product_price_raw">
                    <div class="mb-3">
                        <label for="editProductPrice" class="form-label">قیمت (تومان):</label>
                        <!-- نوع به text برای فرمت‌بندی تغییر یافته است -->
                        <input type="text" class="form-control rounded-pill" id="editProductPrice" name="edit_product_price_display" required min="0">
                    </div>
                    <div class="mb-3">
                        <label for="editProductStock" class="form-label">موجودی:</label>
                        <input type="number" class="form-control rounded-pill" id="editProductStock" name="edit_product_stock" required min="0">
                    </div>
                    <div class="mb-3">
                        <label for="editProductDescription" class="form-label">توضیحات (اختیاری):</label>
                        <textarea class="form-control rounded-lg" id="editProductDescription" name="edit_product_description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editProductImageUrl" class="form-label">لینک تصویر (اختیاری):</label>
                        <input type="url" class="form-control rounded-pill" id="editProductImageUrl" name="edit_product_image_url" placeholder="مثال: https://example.com/image.jpg">
                    </div>
                    <div class="mb-3">
                        <label for="editProductCategory" class="form-label">دسته‌بندی:</label>
                        <select class="form-select rounded-pill" id="editProductCategory" name="edit_product_category_id" required>
                            <option value="">انتخاب دسته‌بندی...</option>
                            <?php
                            // پر کردن دراپ‌داون دسته‌بندی
                            foreach ($all_categories_flat as $cat) {
                                echo '<option value="' . htmlspecialchars($cat['id']) . '">' . htmlspecialchars($cat['name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="editProductIsActive" name="edit_product_is_active">
                        <label class="form-check-label" for="editProductIsActive">فعال/غیرفعال</label>
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
<!-- پایان مودال ویرایش محصول -->

<!-- مودال تایید حذف محصول -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteProductModalLabel">تایید حذف محصول</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="products" method="POST" class="d-inline"> <!-- Action اکنون به روت "products" اشاره می‌کند -->
                <div class="modal-body">
                    <p>آیا از حذف محصول "<strong id="deleteProductNamePlaceholder"></strong>" اطمینان دارید؟</p>
                    <p class="text-danger">توجه: این عملیات غیرقابل بازگشت است.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">انصراف</button>
                    <input type="hidden" name="action" value="delete_product">
                    <input type="hidden" name="delete_product_id" id="deleteProductIdConfirm">
                    <!-- ارسال صفحه فعلی و فیلترها به عنوان ورودی‌های مخفی برای حفظ وضعیت پس از ارسال فرم -->
                    <input type="hidden" name="current_page" value="<?php echo htmlspecialchars($page); ?>"> 
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>"> 
                    <input type="hidden" name="category_filter" value="<?php echo htmlspecialchars($category_filter_id ?? ''); ?>"> 
                    <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($status_filter ?? ''); ?>"> 
                    <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($sort_by); ?>"> 
                    <input type="hidden" name="sort_order" value="<?php echo htmlspecialchars($sort_order); ?>"> 
                    <button type="submit" class="btn btn-danger rounded-pill">حذف کن</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- پایان مودال تایید حذف محصول -->

<script type="module">
// Import the formatter function
// [FIXED] مسیر ایمپورت اصلاح شد
import { initializeAmountFormatter } from '../public/assets/js/input-formatter.js';

document.addEventListener('DOMContentLoaded', function() {
    // Initialize formatter for Add Product Modal price field
    initializeAmountFormatter('productPrice', 'productPriceRaw');

    // JavaScript for Edit Product Modal
    var editProductModal = document.getElementById('editProductModal');
    editProductModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Button that triggered the modal
        var productData = null;

        if (button && button.getAttribute('data-product')) { // اگر مودال با کلیک دکمه باز شده
            try {
                productData = JSON.parse(button.getAttribute('data-product'));
            } catch (e) {
                console.error("Error parsing product data from data-product attribute:", e);
                // اگر مشکلی در JSON.parse وجود داشت، لاگ کنید و ادامه ندهید
                return; 
            }
        } else if (typeof product_to_edit_php_data !== 'undefined' && product_to_edit_php_data !== null) {
            // اگر مودال با PHP باز شده (مثلاً بعد از ریدایرکت از تلاش ویرایش)
            productData = product_to_edit_php_data;
        }

        if (!productData) { // [FIXED] اگر productData هنوز null است، نمایش نده و خارج شو
            console.warn("No product data found to populate edit modal.");
            return;
        }

        // Debugging logs - Keep these enabled for now
        console.log("Product Data on Modal Show:", productData);
        console.log("Target Category ID from Product Data:", productData.category_id);

        // Update the modal's content.
        var modalTitle = editProductModal.querySelector('.modal-title');
        var productIdInput = editProductModal.querySelector('#editProductId');
        var productNameInput = editProductModal.querySelector('#editProductName');
        var productSkuInput = editProductModal.querySelector('#editProductSku'); // hidden SKU
        var editProductPriceInput = editProductModal.querySelector('#editProductPrice'); // Visible formatted input
        var editProductPriceRawInput = editProductModal.querySelector('#editProductPriceRaw'); // Hidden raw input
        var productStockInput = editProductModal.querySelector('#editProductStock');
        var productDescriptionInput = editProductModal.querySelector('#editProductDescription');
        var productImageUrlInput = editProductModal.querySelector('#editProductImageUrl');
        var productCategorySelect = editProductModal.querySelector('#editProductCategory');
        var productIsActiveCheckbox = editProductModal.querySelector('#editProductIsActive');

        // [FIXED] اطمینان از وجود productData قبل از دسترسی به ویژگی‌ها
        if (productData.name) modalTitle.textContent = 'ویرایش محصول: ' + productData.name;
        productIdInput.value = productData.id || '';
        productNameInput.value = productData.name || '';
        productSkuInput.value = productData.sku || ''; // Set hidden SKU
        
        // Set price for edit modal (both raw and formatted display)
        if (productData.price !== null && productData.price !== undefined) {
             editProductPriceRawInput.value = productData.price; // Set raw value for hidden input
             // Format and set display value for visible input
             editProductPriceInput.value = parseFloat(productData.price).toLocaleString('en-US'); // Use parseFloat for price
        } else {
             editProductPriceRawInput.value = '';
             editProductPriceInput.value = '';
        }
        
        productStockInput.value = productData.stock || 0; // [FIXED] مقدار پیش فرض برای stock
        productDescriptionInput.value = productData.description || '';
        productImageUrlInput.value = productData.image_url || '';
        
        // --- FIXED: Set the correct category in the dropdown ---
        var targetCategoryId = productData.category_id;
        
        // Reset dropdown explicitly before attempting to select
        // This ensures no lingering selection from previous modal opens
        productCategorySelect.value = ''; 

        if (targetCategoryId !== null && targetCategoryId !== undefined) { 
            var targetCategoryIdString = String(targetCategoryId); // Ensure it's a string for comparison
            
            // Set the value directly. This is the most common and reliable way.
            productCategorySelect.value = targetCategoryIdString;

            // Optional: Add logging to verify selection (for debugging purposes)
            if (productCategorySelect.value !== targetCategoryIdString) {
                console.warn("Direct dropdown selection failed for category ID:", targetCategoryIdString, ". Trying fallback.");
                // Fallback: If direct setting fails, iterate through options
                var foundOption = false;
                for (var i = 0; i < productCategorySelect.options.length; i++) {
                    // Compare values as strings, trimming any whitespace to be safe
                    if (productCategorySelect.options[i].value.trim() === targetCategoryIdString.trim()) {
                        productCategorySelect.options[i].selected = true;
                        foundOption = true;
                        console.log("Category selected via iteration:", targetCategoryIdString);
                        break;
                    }
                }
                if (!foundOption) {
                    console.warn("Category ID not found in dropdown options:", targetCategoryIdString);
                    productCategorySelect.value = ''; // Ensure default 'انتخاب دسته‌بندی...' is selected
                }
            } else {
                console.log("Category selected successfully via direct assignment:", targetCategoryIdString);
            }
        } else {
            productCategorySelect.value = ''; // If category_id is null or undefined, select default
            console.log("Category ID is null/undefined. Default option selected.");
        }
        // --- END FIXED CATEGORY DROPDOWN ---

        // Set the active status checkbox
        productIsActiveCheckbox.checked = (productData.is_active == 1);

        // Initialize formatter for Edit Product Modal price field when modal shows
        initializeAmountFormatter('editProductPrice', 'editProductPriceRaw');
    });

    editProductModal.addEventListener('hide.bs.modal', function (event) {
        // Reset form fields when modal is hidden
        var productIdInput = editProductModal.querySelector('#editProductId');
        var productNameInput = editProductModal.querySelector('#editProductName');
        var productSkuInput = editProductModal.querySelector('#editProductSku');
        var productPriceInput = editProductModal.querySelector('#editProductPrice');
        var editProductPriceRawInput = editProductModal.querySelector('#editProductPriceRaw'); // Raw input
        var productStockInput = editProductModal.querySelector('#editProductStock');
        var productDescriptionInput = editProductModal.querySelector('#editProductDescription');
        var productImageUrlInput = editProductModal.querySelector('#editProductImageUrl');
        var productCategorySelect = editProductModal.querySelector('#editProductCategory');
        var productIsActiveCheckbox = editProductModal.querySelector('#editProductIsActive');

        productIdInput.value = '';
        productNameInput.value = '';
        productSkuInput.value = '';
        productPriceInput.value = '';
        editProductPriceRawInput.value = ''; // Reset raw input
        productStockInput.value = '';
        productDescriptionInput.value = '';
        productImageUrlInput.value = '';
        productCategorySelect.value = '';
        productIsActiveCheckbox.checked = true; // Default to active
    });


    // JavaScript for Delete Product Confirmation Modal
    var deleteProductModal = document.getElementById('deleteProductModal');
    deleteProductModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Button that triggered the modal
        var productId = button.getAttribute('data-product-id');
        var productName = button.getAttribute('data-product-name');

        // Update the modal's content.
        var productNamePlaceholder = deleteProductModal.querySelector('#deleteProductNamePlaceholder');
        var deleteProductIdConfirmInput = deleteProductModal.querySelector('#deleteProductIdConfirm');
        
        productNamePlaceholder.textContent = productName;
        deleteProductIdConfirmInput.value = productId;
    });

    // [New Logic]: Check if product_to_edit_php_data is available and open modal
    // This is for cases where the page reloads after an edit operation
    <?php if (isset($product_to_edit) && $product_to_edit): ?>
    // Embed the PHP product data directly into a JavaScript variable
    const product_to_edit_php_data = <?php echo json_encode($product_to_edit); ?>;
    
    // Create a new custom event to simulate a button click for the modal
    const event = new Event('show.bs.modal');
    // Attach the product data to the event for the listener to pick up
    // This is a custom property, not part of standard Event, but Bootstrap's show.bs.modal expects relatedTarget.
    // We simulate relatedTarget to pass data.
    event.relatedTarget = {
        getAttribute: function(attr) {
            if (attr === 'data-product') {
                return JSON.stringify(product_to_edit_php_data);
            }
            return null;
        }
    };
    
    // Manually dispatch the event to trigger the modal 'show' listener
    // This will cause the modal to open and populate with product_to_edit_php_data
    document.getElementById('editProductModal').dispatchEvent(event);

    // After the modal is shown and data populated, initialize the formatter for the price field
    // This ensures formatting is applied when modal opens automatically
    initializeAmountFormatter('editProductPrice', 'editProductPriceRaw');

    <?php endif; ?>
});
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
