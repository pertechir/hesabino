<?php
$page = 'add-product';
$pageTitle = 'افزودن محصول جدید';
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// تنظیم متغیرهای پایه
$current_user = $_SESSION['user_id'] ?? null;
$current_date = date('Y-m-d H:i:s');

// بررسی دسترسی کاربر
if (!hasPermission('product_add')) {
    redirect('errors/403.php');
}

require_once '../../includes/header.php';
?>

<div class="product-page">
    <!-- نوار بالای صفحه -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        محصولات / افزودن محصول جدید
                    </div>
                    <h2 class="page-title">
                        افزودن محصول جدید
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="products/list.php" class="btn btn-secondary d-none d-sm-inline-block">
                            <i class="bi bi-arrow-right"></i>
                            بازگشت به لیست
                        </a>
                        <button type="button" class="btn btn-primary d-none d-sm-inline-block" form="productForm">
                            <i class="bi bi-save"></i>
                            ذخیره محصول
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- محتوای اصلی -->
    <div class="page-body">
        <div class="container-fluid">
            <form id="productForm" method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="created_by" value="<?php echo $current_user; ?>">
                <input type="hidden" name="created_at" value="<?php echo $current_date; ?>">
                
                <!-- نوار پیشرفت تکمیل اطلاعات -->
                <div class="progress-wrapper mb-4">
                    <div class="completion-status">
                        تکمیل اطلاعات: <span id="completionPercentage">0</span>%
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%" id="formProgress"></div>
                    </div>
                </div>

                <div class="row">
                    <!-- ستون اصلی -->
                    <div class="col-lg-8">
                        <!-- کارت اطلاعات اصلی -->
                        <?php include 'partials/basic-info.php'; ?>

                        <!-- کارت مدیا -->
                        <?php include 'partials/media.php'; ?>

                        <!-- کارت توضیحات -->
                        <?php include 'partials/descriptions.php'; ?>

                        <!-- کارت ویژگی‌ها -->
                        <?php include 'partials/attributes.php'; ?>

                        <!-- کارت SEO -->
                        <?php include 'partials/seo.php'; ?>
                    </div>

                    <!-- ستون کناری -->
                    <div class="col-lg-4">
                        <!-- کارت وضعیت -->
                        <?php include 'partials/status.php'; ?>

                        <!-- کارت قیمت‌گذاری -->
                        <?php include 'partials/pricing.php'; ?>

                        <!-- کارت موجودی -->
                        <?php include 'partials/inventory.php'; ?>

                        <!-- کارت دسته‌بندی -->
                        <?php include 'partials/categories.php'; ?>

                        <!-- کارت برچسب‌ها -->
                        <?php include 'partials/tags.php'; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- مودال انتخاب دسته‌بندی -->
<?php include 'modals/category-selector.php'; ?>

<!-- مودال لیست قیمت -->
<?php include 'modals/price-list.php'; ?>

<!-- مودال مدیریت ویژگی‌ها -->
<?php include 'modals/attributes-manager.php'; ?>

<?php require_once '../../includes/footer.php'; ?>