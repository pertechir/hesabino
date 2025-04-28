<?php
$page = 'edit-product';
require_once 'includes/header.php';
$pageTitle = 'ویرایش محصول';

// بررسی وجود شناسه محصول
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div class='alert alert-danger'>شناسه محصول معتبر نیست.</div>";
    exit;
}

$productId = (int) $_GET['id'];

// دریافت اطلاعات محصول از پایگاه داده
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='alert alert-danger'>محصول مورد نظر یافت نشد.</div>";
    exit;
}

// بررسی ارسال فرم
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    try {
        $pdo->beginTransaction();

        // آماده‌سازی داده‌ها
        $data = [
            'name' => $_POST['name'],
            'barcode' => $_POST['barcode'],
            'category_id' => $_POST['category_id'],
            'sales_price' => $_POST['sales_price'],
            'sales_description' => $_POST['sales_description'],
            'purchase_price' => $_POST['purchase_price'],
            'purchase_description' => $_POST['purchase_description'],
            'main_unit' => $_POST['main_unit'],
            'sub_unit' => $_POST['sub_unit'] ?? null,
            'conversion_factor' => $_POST['conversion_factor'] ?? null,
            'initial_stock' => $_POST['initial_stock'],
            'reorder_point' => $_POST['reorder_point'],
            'minimum_stock' => $_POST['minimum_stock'],
            'maximum_stock' => $_POST['maximum_stock'] ?? null,
            'minimum_order' => $_POST['minimum_order'],
            'wait_time' => $_POST['wait_time'],
            'storage_location' => $_POST['storage_location'] ?? null,
            'storage_note' => $_POST['storage_note'] ?? null,
            'sales_tax' => $_POST['sales_tax'] ?? 0,
            'purchase_tax' => $_POST['purchase_tax'] ?? 0,
            'tax_type' => $_POST['tax_type'] ?? null,
            'tax_code' => $_POST['tax_code'] ?? null,
            'tax_unit' => $_POST['tax_unit'] ?? null,
            'is_sales_taxable' => isset($_POST['is_sales_taxable']) ? 1 : 0,
            'is_purchase_taxable' => isset($_POST['is_purchase_taxable']) ? 1 : 0,
            'inventory_control' => isset($_POST['inventory_control']) ? 1 : 0,
            'id' => $productId
        ];

        // بروزرسانی محصول
        $sql = "UPDATE products SET 
            name = ?, 
            barcode = ?, 
            category_id = ?, 
            sales_price = ?, 
            sales_description = ?, 
            purchase_price = ?, 
            purchase_description = ?, 
            main_unit = ?, 
            sub_unit = ?, 
            conversion_factor = ?, 
            initial_stock = ?, 
            reorder_point = ?, 
            minimum_stock = ?, 
            maximum_stock = ?, 
            minimum_order = ?, 
            wait_time = ?, 
            storage_location = ?, 
            storage_note = ?, 
            sales_tax = ?, 
            purchase_tax = ?, 
            tax_type = ?, 
            tax_code = ?, 
            tax_unit = ?, 
            is_sales_taxable = ?, 
            is_purchase_taxable = ?, 
            inventory_control = ? 
            WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($data));

        // ذخیره تصاویر
        if (isset($_POST['uploaded_files']) && is_array($_POST['uploaded_files'])) {
            $stmt = $pdo->prepare("UPDATE products SET images = ? WHERE id = ?");
            $stmt->execute([json_encode($_POST['uploaded_files']), $productId]);
        }

        $pdo->commit();
        $_SESSION['success_message'] = "محصول با موفقیت بروزرسانی شد.";
        header('Location: index.php?page=products');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "خطا در بروزرسانی محصول: " . $e->getMessage();
    }
}

// نمایش تصاویر فعلی محصول
$currentImages = !empty($product['images']) ? json_decode($product['images'], true) : [];
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ویرایش محصول</title>
    
    <!-- CSS های مورد نیاز -->
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css" rel="stylesheet">


        <!-- سایر meta tags و CSS ها -->
    
    <!-- اضافه کردن jQuery قبل از همه اسکریپت‌ها -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- اضافه کردن Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <!-- اضافه کردن Dropzone -->
    <link href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" rel="stylesheet" type="text/css" />
    
    <!-- سایر CSS ها -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/edit-product.css" rel="stylesheet">
    
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">ویرایش محصول</h5>
                    <button type="button" class="btn btn-outline-light" onclick="history.back()">
                        <i class="bi bi-arrow-right"></i>
                        بازگشت
                    </button>
                </div>
                <div class="card-body">
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form method="POST" id="editProductForm" class="needs-validation" novalidate>
                        <!-- تب‌های محصول -->
                        <ul class="nav nav-tabs mb-4" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#basicInfo">
                                    <i class="bi bi-info-circle"></i>
                                    اطلاعات اصلی
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#priceTab">
                                    <i class="bi bi-currency-dollar"></i>
                                    قیمت‌گذاری
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#unitsTab">
                                    <i class="bi bi-box"></i>
                                    واحدها
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#inventoryTab">
                                    <i class="bi bi-clipboard-data"></i>
                                    موجودی
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#taxTab">
                                    <i class="bi bi-percent"></i>
                                    مالیات
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#imagesTab">
                                    <i class="bi bi-images"></i>
                                    تصاویر
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- تب اطلاعات اصلی -->
                            <div class="tab-pane fade show active" id="basicInfo">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">نام محصول</label>
                                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">بارکد</label>
                                            <div class="input-group">
                                                <input type="text" name="barcode" class="form-control" value="<?php echo htmlspecialchars($product['barcode']); ?>">
                                                <button type="button" class="btn btn-outline-secondary" onclick="generateBarcode()">
                                                    <i class="bi bi-upc"></i>
                                                    تولید بارکد
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">دسته‌بندی</label>
                                            <select name="category_id" class="form-select select2" required>
                                                <option value="">انتخاب دسته‌بندی</option>
                                                <?php
                                                $categories = $pdo->query("SELECT * FROM categories ORDER BY name");
                                                while ($category = $categories->fetch()) {
                                                    $selected = $category['id'] === $product['category_id'] ? 'selected' : '';
                                                    echo "<option value='{$category['id']}' {$selected}>{$category['name']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input type="checkbox" name="inventory_control" class="form-check-input" id="inventoryControl" <?php echo $product['inventory_control'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label">کنترل موجودی</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- تب قیمت‌گذاری -->
                            <div class="tab-pane fade" id="priceTab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h6 class="mb-0">اطلاعات فروش</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">قیمت فروش (ریال)</label>
                                                    <input type="number" name="sales_price" class="form-control" value="<?php echo $product['sales_price']; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">توضیحات فروش</label>
                                                    <textarea name="sales_description" class="form-control" rows="3"><?php echo htmlspecialchars($product['sales_description']); ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h6 class="mb-0">اطلاعات خرید</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">قیمت خرید (ریال)</label>
                                                    <input type="number" name="purchase_price" class="form-control" value="<?php echo $product['purchase_price']; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">توضیحات خرید</label>
                                                    <textarea name="purchase_description" class="form-control" rows="3"><?php echo htmlspecialchars($product['purchase_description']); ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- تب واحدها -->
                            <div class="tab-pane fade" id="unitsTab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h6 class="mb-0">واحد اصلی</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">واحد اصلی</label>
                                                    <input type="text" name="main_unit" class="form-control" value="<?php echo htmlspecialchars($product['main_unit']); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h6 class="mb-0">واحد فرعی</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">واحد فرعی</label>
                                                    <input type="text" name="sub_unit" class="form-control" value="<?php echo htmlspecialchars($product['sub_unit']); ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">ضریب تبدیل</label>
                                                    <input type="number" name="conversion_factor" class="form-control" value="<?php echo $product['conversion_factor']; ?>" step="0.01">
                                                    <div class="form-text">هر واحد فرعی معادل چند واحد اصلی است؟</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- تب موجودی -->
                            <div class="tab-pane fade" id="inventoryTab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h6 class="mb-0">اطلاعات موجودی</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">موجودی اولیه</label>
                                                    <input type="number" name="initial_stock" class="form-control" value="<?php echo $product['initial_stock']; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">نقطه سفارش</label>
                                                    <input type="number" name="reorder_point" class="form-control" value="<?php echo $product['reorder_point']; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">حداقل موجودی</label>
                                                    <input type="number" name="minimum_stock" class="form-control" value="<?php echo $product['minimum_stock']; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">حداکثر موجودی</label>
                                                    <input type="number" name="maximum_stock" class="form-control" value="<?php echo $product['maximum_stock']; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h6 class="mb-0">اطلاعات انبار</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">محل نگهداری</label>
                                                    <input type="text" name="storage_location" class="form-control" value="<?php echo htmlspecialchars($product['storage_location']); ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">توضیحات انبار</label>
                                                    <textarea name="storage_note" class="form-control" rows="3"><?php echo htmlspecialchars($product['storage_note']); ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- تب مالیات -->
                            <div class="tab-pane fade" id="taxTab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h6 class="mb-0">مالیات فروش</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-check mb-3">
                                                    <input type="checkbox" name="is_sales_taxable" class="form-check-input" id="salesTax" <?php echo $product['is_sales_taxable'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label">مشمول مالیات فروش</label>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">درصد مالیات فروش</label>
                                                    <input type="number" name="sales_tax" class="form-control" value="<?php echo $product['sales_tax']; ?>" step="0.01">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h6 class="mb-0">مالیات خرید</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-check mb-3">
                                                    <input type="checkbox" name="is_purchase_taxable" class="form-check-input" id="purchaseTax" <?php echo $product['is_purchase_taxable'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label">مشمول مالیات خرید</label>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">درصد مالیات خرید</label>
                                                    <input type="number" name="purchase_tax" class="form-control" value="<?php echo $product['purchase_tax']; ?>" step="0.01">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h6 class="mb-0">اطلاعات تکمیلی مالیات</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="mb-3">
                                                            <label class="form-label">نوع مالیات</label>
                                                            <input type="text" name="tax_type" class="form-control" value="<?php echo htmlspecialchars($product['tax_type']); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mb-3">
                                                            <label class="form-label">کد مالیاتی</label>
                                                            <input type="text" name="tax_code" class="form-control" value="<?php echo htmlspecialchars($product['tax_code']); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mb-3">
                                                            <label class="form-label">واحد مالیاتی</label>
                                                            <input type="text" name="tax_unit" class="form-control" value="<?php echo htmlspecialchars($product['tax_unit']); ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- تب تصاویر -->
                            <div class="tab-pane fade" id="imagesTab">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0">تصاویر محصول</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="currentImages" class="mb-3">
                                            <?php foreach ($currentImages as $image): ?>
                                            <div class="d-inline-block position-relative m-2">
                                                <img src="<?php echo $image; ?>" alt="Product Image" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" onclick="removeImage(this, '<?php echo $image; ?>')">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div id="productImages" class="dropzone"></div>
                                        <input type="hidden" name="uploaded_files[]" id="uploadedFiles">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" name="update_product" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-lg"></i>
                                ذخیره تغییرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* استایل Dropzone */
.dropzone {
    border: 2px dashed #3498db !important;
    border-radius: 10px !important;
    min-height: 150px !important;
    padding: 20px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: white !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    margin-bottom: 20px !important;
}

.dropzone:hover {
    border-color: #2980b9 !important;
    background-color: #f8f9fa !important;
}

.dropzone .dz-message {
    margin: 0 !important;
    font-size: 1.1em !important;
    color: #6c757d !important;
    text-align: center !important;
}

.dropzone .dz-message .bi {
    font-size: 2em !important;
    margin-bottom: 10px !important;
    color: #3498db !important;
}

.dropzone .dz-preview {
    margin: 10px !important;
    min-height: 100px !important;
}

.dropzone .dz-preview .dz-image {
    border-radius: 10px !important;
    overflow: hidden !important;
    width: 120px !important;
    height: 120px !important;
    border: 1px solid #e0e0e0 !important;
}

.dropzone .dz-preview .dz-image img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
}

.dropzone .dz-preview .dz-error-message {
    min-width: 140px !important;
}

.dropzone .dz-preview.dz-image-preview {
    background: transparent !important;
}

.dz-remove {
    color: #dc3545 !important;
    text-decoration: none !important;
    margin-top: 5px !important;
    display: inline-block !important;
    font-size: 0.9em !important;
    padding: 3px 8px !important;
    border-radius: 5px !important;
    background-color: rgba(220, 53, 69, 0.1) !important;
    transition: all 0.3s ease !important;
}

.dz-remove:hover {
    color: white !important;
    background-color: #dc3545 !important;
}

/* استایل تصاویر موجود */
.existing-images {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 20px;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 10px;
    border: 1px solid #e0e0e0;
}

.image-container {
    position: relative;
    width: 150px;
    height: 150px;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.image-container:hover img {
    transform: scale(1.05);
}

.image-container .remove-image {
    position: absolute;
    top: 5px;
    right: 5px;
    background-color: rgba(220, 53, 69, 0.9);
    color: white;
    border: none;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    opacity: 0;
}

.image-container:hover .remove-image {
    opacity: 1;
}

.image-container .remove-image:hover {
    background-color: #dc3545;
    transform: scale(1.1);
}

/* Loading State */
.uploading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
}

.uploading::before {
    content: '';
    width: 30px;
    height: 30px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #3498db;
    border-radius: 50%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    animation: spin 1s linear infinite;
    z-index: 1;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}
</style>

<!-- Scripts -->

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Dropzone.autoDiscover = false;
const dropzone = new Dropzone("#productImages", {
    url: "upload.php",
    acceptedFiles: "image/*",
    maxFiles: 5,
    maxFilesize: 2,
    dictDefaultMessage: "تصاویر را اینجا بکشید و رها کنید یا کلیک کنید",
    addRemoveLinks: true,
    success: function (file, response) {
        const uploadedFilesInput = document.getElementById('uploadedFiles');
        uploadedFilesInput.value += (uploadedFilesInput.value ? ',' : '') + response.filePath;
    }
});

function removeImage(button, filePath) {
    button.parentElement.remove();
    const uploadedFilesInput = document.getElementById('uploadedFiles');
    uploadedFilesInput.value = uploadedFilesInput.value.split(',').filter(file => file !== filePath).join(',');
}
// پیکربندی Dropzone
Dropzone.autoDiscover = false;
$(document).ready(function() {
    // راه‌اندازی Select2
    $('.select2').select2({
        theme: 'bootstrap-5'
    });

    // پیکربندی Dropzone
    var myDropzone = new Dropzone("#productImages", {
        url: "upload.php",
        acceptedFiles: "image/*",
        maxFilesize: 2,
        maxFiles: 5,
        addRemoveLinks: true,
        dictDefaultMessage: "فایل‌ها را اینجا بکشید و رها کنید یا کلیک کنید",
        dictRemoveFile: "حذف فایل",
        success: function(file, response) {
            if (response.success) {
                var uploadedFiles = JSON.parse($("#uploadedFiles").val() || '[]');
                uploadedFiles.push(response.filePath);
                $("#uploadedFiles").val(JSON.stringify(uploadedFiles));
            }
        },
        removedfile: function(file) {
            var uploadedFiles = JSON.parse($("#uploadedFiles").val() || '[]');
            if (file.status === 'success') {
                var response = JSON.parse(file.xhr.response);
                var index = uploadedFiles.indexOf(response.filePath);
                if (index !== -1) {
                    uploadedFiles.splice(index, 1);
                    $("#uploadedFiles").val(JSON.stringify(uploadedFiles));
                    
                    // حذف فایل از سرور
                    $.post('delete-image.php', {
                        filePath: response.filePath
                    });
                }
            }
            file.previewElement.remove();
        }
    });

    // نمایش تصاویر موجود در Dropzone
    var currentImages = JSON.parse($("#uploadedFiles").val() || '[]');
    currentImages.forEach(function(filePath) {
        var mockFile = { name: filePath.split('/').pop(), size: 12345 };
        myDropzone.emit("addedfile", mockFile);
        myDropzone.emit("thumbnail", mockFile, filePath);
        myDropzone.emit("complete", mockFile);
        myDropzone.files.push(mockFile);
    });
});
</script>
</body>
</html>