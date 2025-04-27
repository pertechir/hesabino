<?php
// دریافت آخرین کد حسابداری
function getLastAccountingCode($pdo) {
    $stmt = $pdo->query("SELECT accounting_code FROM products ORDER BY accounting_code DESC LIMIT 1");
    $last = $stmt->fetch();
    if ($last) {
        $num = intval(substr($last['accounting_code'], -4)) + 1;
        return sprintf("P%04d", $num);
    }
    return "P0001";
}

// بررسی درخواست افزودن محصول
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    try {
        $pdo->beginTransaction();
        
        // مدیریت آپلود تصویر با Dropzone.js
        $images = [];
        if (isset($_FILES['images'])) {
            $upload_dir = 'uploads/products/';
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] == 0) {
                    $file_name = time() . '_' . $_FILES['images']['name'][$key];
                    move_uploaded_file($tmp_name, $upload_dir . $file_name);
                    $images[] = $file_name;
                }
            }
        }

        // درج اطلاعات محصول
        $stmt = $pdo->prepare("
            INSERT INTO products (
                name, accounting_code, barcode, category_id,
                sales_price, sales_description, purchase_price, purchase_description,
                partner_price, wholesale_price, usd_price, staff_price, shop_price,
                main_unit, sub_unit, conversion_factor,
                inventory_control, reorder_point, minimum_order, wait_time,
                sales_tax, purchase_tax, tax_type, tax_code, tax_unit,
                is_sales_taxable, is_purchase_taxable,
                images, created_at
            ) VALUES (
                ?, ?, ?, ?, 
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?,
                ?, NOW()
            )
        ");

        $stmt->execute([
            $_POST['name'],
            $_POST['custom_code'] ? $_POST['custom_code'] : getLastAccountingCode($pdo),
            $_POST['barcode'],
            $_POST['category_id'],
            $_POST['sales_price'],
            $_POST['sales_description'],
            $_POST['purchase_price'],
            $_POST['purchase_description'],
            $_POST['partner_price'],
            $_POST['wholesale_price'],
            $_POST['usd_price'],
            $_POST['staff_price'],
            $_POST['shop_price'],
            $_POST['main_unit'],
            $_POST['sub_unit'],
            $_POST['conversion_factor'],
            isset($_POST['inventory_control']) ? 1 : 0,
            $_POST['reorder_point'],
            $_POST['minimum_order'],
            $_POST['wait_time'],
            $_POST['sales_tax'],
            $_POST['purchase_tax'],
            $_POST['tax_type'],
            $_POST['tax_code'],
            $_POST['tax_unit'],
            isset($_POST['is_sales_taxable']) ? 1 : 0,
            isset($_POST['is_purchase_taxable']) ? 1 : 0,
            json_encode($images)
        ]);
        
        $pdo->commit();
        $success_message = "محصول با موفقیت افزوده شد.";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "خطا در افزودن محصول: " . $e->getMessage();
    }
}
?>
<link rel="stylesheet" href="assets/css/product.css">
<!-- اضافه کردن CSS های مورد نیاز -->
<link href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">افزودن محصول جدید</h5>
                    <button type="button" class="btn btn-outline-light" onclick="history.back()">
                        <i class="bi bi-arrow-right"></i>
                        بازگشت
                    </button>
                </div>
                <div class="card-body">
                    <form method="POST" id="addProductForm" class="needs-validation" novalidate>
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
                        </ul>

                        <div class="tab-content">
                            <!-- تب اطلاعات اصلی -->
                            <div class="tab-pane fade show active" id="basicInfo">
                                <div class="row">
                                    <div class="col-md-6">
                                        <!-- آپلودر تصویر با Dropzone -->
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h6 class="mb-0">تصاویر محصول</h6>
                                            </div>
                                            <div class="card-body">
                                                <div id="productImages" class="dropzone"></div>
                                            </div>
                                        </div>
                                        
                                        <!-- اطلاعات پایه -->
                                        <div class="card mb-4">
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">نام محصول</label>
                                                    <input type="text" name="name" class="form-control" required>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">دسته‌بندی</label>
                                                    <select name="category_id" class="form-select select2" required>
                                                        <option value="">انتخاب دسته‌بندی</option>
                                                        <?php
                                                        $categories = $pdo->query("SELECT * FROM categories ORDER BY name");
                                                        while ($category = $categories->fetch()) {
                                                            echo "<option value='{$category['id']}'>{$category['name']}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" id="customCode">
                                                        <label class="form-check-label">کد حسابداری سفارشی</label>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">کد حسابداری</label>
                                                    <input type="text" name="custom_code" class="form-control" readonly>
                                                    <div class="form-text">کد پیش‌فرض: <?php echo getLastAccountingCode($pdo); ?></div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">بارکد</label>
                                                    <input type="text" name="barcode" class="form-control">
                                                    <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="generateBarcode()">
                                                        <i class="bi bi-upc"></i>
                                                        تولید بارکد
                                                    </button>
                                                </div>
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
                                                <h6 class="mb-0">قیمت فروش</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">قیمت فروش (ریال)</label>
                                                    <input type="number" name="sales_price" class="form-control" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">توضیحات فروش</label>
                                                    <textarea name="sales_description" class="form-control" rows="3"></textarea>
                                                </div>
                                                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#priceListModal">
                                                    <i class="bi bi-list-ul"></i>
                                                    لیست قیمت‌ها
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h6 class="mb-0">قیمت خرید</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">قیمت خرید (ریال)</label>
                                                    <input type="number" name="purchase_price" class="form-control" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">توضیحات خرید</label>
                                                    <textarea name="purchase_description" class="form-control" rows="3"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- تب واحدها -->
                            <div class="tab-pane fade" id="unitsTab">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">واحد اصلی</label>
                                                    <input type="text" name="main_unit" class="form-control" required>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="form-check mb-2">
                                                        <input type="checkbox" class="form-check-input" id="hasSubUnit">
                                                        <label class="form-check-label">بیش از یک واحد دارد</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div id="subUnitSection" style="display:none">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">واحد فرعی</label>
                                                        <input type="text" name="sub_unit" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">ضریب تبدیل</label>
                                                        <input type="number" name="conversion_factor" class="form-control" step="0.01">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- تب موجودی -->
                            <div class="tab-pane fade" id="inventoryTab">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="form-check mb-4">
                                            <input type="checkbox" name="inventory_control" class="form-check-input" id="inventoryControl">
                                            <label class="form-check-label">کنترل موجودی</label>
                                        </div>
                                        
                                        <div id="inventorySettings" style="display:none">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">نقطه سفارش</label>
                                                        <input type="number" name="reorder_point" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">حداقل سفارش</label>
                                                        <input type="number" name="minimum_order" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">زمان انتظار (روز)</label>
                                                        <input type="number" name="wait_time" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- تب مالیات -->
                            <div class="tab-pane fade" id="taxTab">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check mb-3">
                                                    <input type="checkbox" name="is_sales_taxable" class="form-check-input" id="salesTax">
                                                    <label class="form-check-label">مشمول مالیات فروش</label>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">درصد مالیات فروش</label>
                                                    <input type="number" name="sales_tax" class="form-control" step="0.01">
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="form-check mb-3">
                                                    <input type="checkbox" name="is_purchase_taxable" class="form-check-input" id="purchaseTax">
                                                    <label class="form-check-label">مشمول مالیات خرید</label>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">درصد مالیات خرید</label>
                                                    <input type="number" name="purchase_tax" class="form-control" step="0.01">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">نوع مالیات</label>
                                                    <input type="text" name="tax_type" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">کد مالیاتی</label>
                                                    <input type="text" name="tax_code" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">واحد مالیاتی</label>
                                                    <input type="text" name="tax_unit" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" name="add_product" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-lg"></i>
                                ذخیره محصول
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- مودال لیست قیمت‌ها -->
<div class="modal fade" id="priceListModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">لیست قیمت‌ها</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">قیمت همکار (ریال)</label>
                            <input type="number" name="partner_price" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">قیمت عمده (ریال)</label>
                            <input type="number" name="wholesale_price" class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">قیمت دلاری ($)</label>
                            <input type="number" name="usd_price" class="form-control" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">قیمت پرسنل (ریال)</label>
                            <input type="number" name="staff_price" class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">قیمت مغازه (ریال)</label>
                            <input type="number" name="shop_price" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                <button type="button" class="btn btn-primary" onclick="savePriceList()">ذخیره</button>
            </div>
        </div>
    </div>
</div>

<style>

</style>
<!-- اضافه کردن اسکریپت‌های مورد نیاز -->
<!-- ابتدا jQuery لود شود -->
 <script src="assets/js/product.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- سپس select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- بعد bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- در نهایت dropzone -->
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<!-- و در آخر اسکریپت خودمان -->

<script>

</script>