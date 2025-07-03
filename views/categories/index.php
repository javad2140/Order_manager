<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>مدیریت دسته‌بندی‌ها</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="fas fa-plus"></i> افزودن دسته‌بندی جدید
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

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">لیست دسته‌بندی‌ها</h4>
    </div>
    <div class="card-body">
        <?php if (!empty($categories_hierarchical)): ?>
            <ul class="category-tree">
                <?php
                // Function to recursively render categories as a nested tree view
                function renderCategories($categories, $level = 0) {
                    foreach ($categories as $category) {
                        $hasChildren = !empty($category['children']);
                        
                        // Prepare category data for JSON encoding in data-category attribute
                        $category_data_json = htmlspecialchars(json_encode([
                            'id' => $category['id'],
                            'name' => $category['name'],
                            'description' => $category['description'],
                            'parent_id' => $category['parent_id']
                        ]), ENT_QUOTES, 'UTF-8');
                        ?>
                        <li class="category-item level-<?php echo $level; ?>">
                            <!-- The entire wrapper is now clickable to toggle collapse -->
                            <div class="category-title-wrapper <?php echo $hasChildren ? 'tree-toggle-wrapper' : ''; ?>"
                                 <?php if ($hasChildren): ?>
                                 data-bs-toggle="collapse"
                                 data-bs-target="#children-of-<?php echo htmlspecialchars($category['id']); ?>"
                                 aria-expanded="false"
                                 aria-controls="children-of-<?php echo htmlspecialchars($category['id']); ?>"
                                 <?php endif; ?>>
                                <span class="category-name-display">
                                    <?php if ($hasChildren): ?>
                                        <i class="fas fa-caret-right icon"></i> <!-- Icon remains, but click is on parent div -->
                                    <?php else: ?>
                                        <i class="fas fa-tag icon leaf-icon"></i>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </span>
                                <span class="buttons">
                                    <button type="button" class="btn btn-sm btn-info edit-btn"
                                            data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                            data-category='<?php echo $category_data_json; ?>'
                                            onclick="event.stopPropagation();"> <!-- Prevent parent click event -->
                                        ویرایش
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-bs-toggle="modal" data-bs-target="#deleteCategoryModal"
                                            data-category-id="<?php echo htmlspecialchars($category['id']); ?>"
                                            data-category-name="<?php echo htmlspecialchars($category['name']); ?>"
                                            onclick="event.stopPropagation();"> <!-- Prevent parent click event -->
                                        حذف
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success add-btn add-subcategory-btn"
                                            data-bs-toggle="modal" data-bs-target="#addCategoryModal"
                                            data-parent-id="<?php echo htmlspecialchars($category['id']); ?>"
                                            data-parent-name="<?php echo htmlspecialchars($category['name']); ?>"
                                            onclick="event.stopPropagation();"> <!-- Prevent parent click event -->
                                        افزودن زیر‌دسته
                                    </button>
                                </span>
                            </div>
                            <?php if ($hasChildren): ?>
                                <ul class="category-children collapse" id="children-of-<?php echo htmlspecialchars($category['id']); ?>">
                                    <?php
                                    // Recursively render children
                                    renderCategories($category['children'], $level + 1);
                                    ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                        <?php
                    }
                }
                // Start rendering from top-level categories
                renderCategories($categories_hierarchical);
                ?>
            </ul>
        <?php else: ?>
            <div class="alert alert-info text-center" role="alert">
                هیچ دسته‌بندی‌ای هنوز ثبت نشده است. برای شروع، یک دسته‌بندی اضافه کنید.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addCategoryModalLabel">افزودن دسته‌بندی جدید</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_URL; ?>categories" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_category">
                    <input type="hidden" name="parent_category_id" id="addParentCategoryId"> <!-- Hidden field for parent ID -->
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">نام دسته‌بندی:</label>
                        <input type="text" class="form-control rounded-pill" id="categoryName" name="category_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label">توضیحات (اختیاری):</label>
                        <textarea class="form-control rounded-lg" id="categoryDescription" name="category_description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="parentCategorySelect" class="form-label">دسته‌بندی والد (اختیاری):</label>
                        <select class="form-select rounded-pill" id="parentCategorySelect" name="parent_category_id_display"> <!-- Changed ID to avoid conflict with hidden field -->
                            <option value="">(بدون والد - دسته‌بندی اصلی)</option>
                            <?php
                            // Render flat list for parent selection
                            // Use the hierarchical data to build a flat list with indentation for readability
                            function renderParentOptionsForSelect($categories, $level = 0) {
                                foreach ($categories as $cat) {
                                    echo '<option value="' . htmlspecialchars($cat['id']) . '">' . str_repeat('--', $level) . ' ' . htmlspecialchars($cat['name']) . '</option>';
                                    if (!empty($cat['children'])) {
                                        renderParentOptionsForSelect($cat['children'], $level + 1);
                                    }
                                }
                            }
                            renderParentOptionsForSelect($categories_hierarchical); // Use the hierarchical data
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary rounded-pill">ذخیره دسته‌بندی</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="editCategoryModalLabel">ویرایش دسته‌بندی</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_URL; ?>categories" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_category">
                    <input type="hidden" name="category_id" id="editCategoryId">
                    <div class="mb-3">
                        <label for="editCategoryName" class="form-label">نام دسته‌بندی:</label>
                        <input type="text" class="form-control rounded-pill" id="editCategoryName" name="edit_category_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCategoryDescription" class="form-label">توضیحات (اختیاری):</label>
                        <textarea class="form-control rounded-lg" id="editCategoryDescription" name="edit_category_description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editParentCategory" class="form-label">دسته‌بندی والد (اختیاری):</label>
                        <select class="form-select rounded-pill" id="editParentCategory" name="edit_parent_category_id">
                            <option value="">(بدون والد - دسته‌بندی اصلی)</option>
                            <?php
                            // Render flat list for parent selection in edit modal
                            renderParentOptionsForSelect($categories_hierarchical); // Reuse the function
                            ?>
                        </select>
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

<!-- Delete Category Confirmation Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteCategoryModalLabel">تایید حذف دسته‌بندی</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_URL; ?>categories" method="POST" class="d-inline">
                <div class="modal-body">
                    <p>آیا از حذف دسته‌بندی "<strong id="deleteCategoryNamePlaceholder"></strong>" اطمینان دارید؟</p>
                    <p class="text-danger">توجه: این عملیات غیرقابل بازگشت است و اگر محصولی به این دسته‌بندی مرتبط باشد، حذف نخواهد شد.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">انصراف</button>
                    <input type="hidden" name="action" value="delete_category">
                    <input type="hidden" name="delete_category_id" id="deleteCategoryIdConfirm">
                    <button type="submit" class="btn btn-danger rounded-pill">حذف کن</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debugging: Check if Bootstrap is loaded
    if (typeof bootstrap === 'undefined') {
        console.error("Bootstrap JavaScript is NOT loaded!");
    } else {
        console.log("Bootstrap JavaScript is loaded.");
    }

    // JavaScript for Tree View Toggle (Custom implementation)
    // Now, the entire .category-title-wrapper is clickable
    document.querySelectorAll('.category-title-wrapper').forEach(wrapper => {
        // Only add listener if it's a parent category (has data-bs-toggle)
        if (wrapper.hasAttribute('data-bs-toggle')) {
            wrapper.addEventListener('click', function() {
                const icon = this.querySelector('.icon'); // Get the icon within this wrapper
                if (icon) {
                    // Toggle the icon between caret-right (collapsed) and caret-down (expanded)
                    icon.classList.toggle('fa-caret-right');
                    icon.classList.toggle('fa-caret-down');
                }
            });
        }
    });

    // JavaScript for Add Category Modal (pre-filling parent ID)
    var addCategoryModal = document.getElementById('addCategoryModal');
    addCategoryModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Button that triggered the modal
        var addParentCategoryIdInput = addCategoryModal.querySelector('#addParentCategoryId');
        var parentCategorySelect = addCategoryModal.querySelector('#parentCategorySelect'); // The visible select

        // Reset form fields
        addCategoryModal.querySelector('#categoryName').value = '';
        addCategoryModal.querySelector('#categoryDescription').value = '';
        parentCategorySelect.value = ''; // Reset visible select

        // Check if the modal was triggered by an "Add Subcategory" button
        if (button && button.classList.contains('add-subcategory-btn')) {
            const parentId = button.getAttribute('data-parent-id');
            const parentName = button.getAttribute('data-parent-name');
            
            addParentCategoryIdInput.value = parentId; // Set hidden parent ID
            parentCategorySelect.value = parentId; // Set visible select
            
            // Optionally update modal title to indicate adding a subcategory
            addCategoryModal.querySelector('.modal-title').textContent = 'افزودن زیر‌دسته برای: ' + parentName;
        } else {
            // If not a subcategory button, ensure parent fields are clear
            addParentCategoryIdInput.value = '';
            parentCategorySelect.value = '';
            addCategoryModal.querySelector('.modal-title').textContent = 'افزودن دسته‌بندی جدید';
        }
    });

    addCategoryModal.addEventListener('hide.bs.modal', function() {
        // Reset modal title and hidden parent ID when modal hides
        addCategoryModal.querySelector('.modal-title').textContent = 'افزودن دسته‌بندی جدید';
        addCategoryModal.querySelector('#addParentCategoryId').value = '';
        addCategoryModal.querySelector('#parentCategorySelect').value = '';
    });


    // JavaScript for Edit Category Modal
    var editCategoryModal = document.getElementById('editCategoryModal');
    editCategoryModal.addEventListener('show.bs.modal', function (event) {
        console.log("Edit Category Modal: show.bs.modal event triggered.");
        var button = event.relatedTarget; // Button that triggered the modal
        var categoryData = JSON.parse(button.getAttribute('data-category'));
        console.log("Category Data for Edit:", categoryData);

        var modalTitle = editCategoryModal.querySelector('.modal-title');
        var categoryIdInput = editCategoryModal.querySelector('#editCategoryId');
        var categoryNameInput = editCategoryModal.querySelector('#editCategoryName');
        var categoryDescriptionInput = editCategoryModal.querySelector('#editCategoryDescription');
        var parentCategorySelect = editCategoryModal.querySelector('#editParentCategory');

        modalTitle.textContent = 'ویرایش دسته‌بندی: ' + categoryData.name;
        categoryIdInput.value = categoryData.id;
        categoryNameInput.value = categoryData.name;
        categoryDescriptionInput.value = categoryData.description || ''; 

        // Handle parent category dropdown
        // Reset options disabled state first
        for (var i = 0; i < parentCategorySelect.options.length; i++) {
            parentCategorySelect.options[i].disabled = false;
        }

        if (categoryData.parent_id) {
            parentCategorySelect.value = String(categoryData.parent_id); // Ensure string comparison
        } else {
            parentCategorySelect.value = ''; // Select "بدون والد"
        }

        // Disable the current category from being its own parent
        // This loop must run AFTER setting the value, otherwise the selected option might be disabled immediately.
        for (var i = 0; i < parentCategorySelect.options.length; i++) {
            if (parentCategorySelect.options[i].value == categoryData.id) {
                parentCategorySelect.options[i].disabled = true;
                console.log("Disabled option for self-parenting:", categoryData.id);
                break; // No need to continue after finding
            }
        }
    });

    editCategoryModal.addEventListener('hide.bs.modal', function (event) {
        console.log("Edit Category Modal: hide.bs.modal event triggered.");
        var categoryIdInput = editCategoryModal.querySelector('#editCategoryId');
        var categoryNameInput = editCategoryModal.querySelector('#editCategoryName');
        var categoryDescriptionInput = editCategoryModal.querySelector('#editCategoryDescription');
        var parentCategorySelect = editCategoryModal.querySelector('#editParentCategory');

        categoryIdInput.value = '';
        categoryNameInput.value = '';
        categoryDescriptionInput.value = '';
        parentCategorySelect.value = '';

        // Re-enable all options when modal hides
        for (var i = 0; i < parentCategorySelect.options.length; i++) {
            parentCategorySelect.options[i].disabled = false;
        }
    });

    // JavaScript for Delete Category Confirmation Modal
    var deleteCategoryModal = document.getElementById('deleteCategoryModal');
    deleteCategoryModal.addEventListener('show.bs.modal', function (event) {
        console.log("Delete Category Modal: show.bs.modal event triggered.");
        var button = event.relatedTarget; // Button that triggered the modal
        var categoryId = button.getAttribute('data-category-id');
        var categoryName = button.getAttribute('data-category-name');
        console.log("Category Data for Delete:", {id: categoryId, name: categoryName});

        // Update the modal's content.
        var categoryNamePlaceholder = deleteCategoryModal.querySelector('#deleteCategoryNamePlaceholder');
        var deleteCategoryIdConfirmInput = deleteCategoryModal.querySelector('#deleteCategoryIdConfirm');
        
        categoryNamePlaceholder.textContent = categoryName;
        deleteCategoryIdConfirmInput.value = categoryId;
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
