<?php
// بررسی دسترسی و متغیرهای پایه
$page = 'add-product';
$pageTitle = 'افزودن محصول';

// اضافه کردن استایل‌های مورد نیاز
add_header_styles([
    '<link href="assets/css/select2.min.css" rel="stylesheet">',
    '<link href="assets/css/dropzone.min.css" rel="stylesheet">',
    '<style>
        /* استایل‌های اختصاصی صفحه افزودن محصول */
        .price-card {
            background: #f8f9fa;
            border-radius: 0.75rem;
            padding: 1rem;
            height: 100%;
            transition: all 0.3s ease;
        }
        
        .profit-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .profit-badge.loss { background-color: #fee2e2; color: #dc2626; }
        .profit-badge.normal-profit { background-color: #ecfdf5; color: #059669; }
        
        .price-input {
            font-family: var(--bs-font-monospace);
            text-align: left;
            direction: ltr;
        }
        
        .dropzone {
            border: 2px dashed #dee2e6;
            border-radius: 0.5rem;
            min-height: 150px;
        }
        
        .stock-status {
            font-size: 0.9rem;
            font-weight: 500;
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
        }
    </style>'
]);

// توابع کمکی برای محاسبات مالی
function formatPrice($price) {
    return number_format($price, 0, '.', ',') . ' تومان';
}

function calculateProfit($purchase, $sell) {
    if ($purchase <= 0) return 0;
    return (($sell - $purchase) / $purchase) * 100;
}
?>

<div class="content-wrapper">
    <!-- هدر صفحه -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="page-title"><?php echo $pageTitle; ?></h1>
                </div>
                <div class="col-auto">
                    <button type="submit" form="productForm" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i>
                        ذخیره محصول
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- محتوای اصلی -->
    <div class="container-fluid">
        <form id="productForm" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="created_by" value="<?php echo $_SESSION['user_login']; ?>">
            <input type="hidden" name="created_at" value="<?php echo date('Y-m-d H:i:s'); ?>">
            
            <div class="row">
                <!-- ستون اصلی -->
                <div class="col-lg-8">
                    <!-- کارت اطلاعات اصلی -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">اطلاعات اصلی محصول</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">نام محصول</label>
                                        <input type="text" class="form-control" name="name" required
                                               data-validation="length" data-validation-length="min3">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">کد محصول</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="product_code" readonly>
                                            <button class="btn btn-outline-secondary" type="button" id="generateCode">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                            <div class="input-group-text">
                                                <input class="form-check-input mt-0" type="checkbox" id="customCode">
                                                <label class="form-check-label ms-2">کد سفارشی</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">برند</label>
                                        <select class="form-select select2" name="brand_id">
                                            <option value="">انتخاب کنید</option>
                                            <?php
                                            $brands = $pdo->query("SELECT * FROM brands ORDER BY name");
                                            while ($brand = $brands->fetch()) {
                                                echo "<option value='{$brand['id']}'>{$brand['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">بارکد</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="barcode">
                                            <button class="btn btn-outline-secondary" type="button" id="generateBarcode">
                                                <i class="bi bi-upc"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- کارت مدیا -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">تصاویر محصول</h3>
                        </div>
                        <div class="card-body">
                            <div class="dropzone" id="productImages">
                                <div class="dz-message">
                                    <i class="bi bi-cloud-arrow-up fs-3"></i>
                                    <p>تصاویر را اینجا رها کنید یا کلیک کنید</p>
                                    <small class="text-muted">حداکثر 5 تصویر | فرمت‌های مجاز: JPG, PNG</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ستون کناری -->
                <div class="col-lg-4">
                    <!-- کارت قیمت‌گذاری -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">قیمت‌گذاری</h3>
                            <div id="profitStatus" class="profit-badge">
                                سود: <span>0</span> درصد
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label required">قیمت خرید (تومان)</label>
                                <input type="text" class="form-control price-input" name="purchase_price" required
                                       data-price-type="purchase">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label required">قیمت فروش (تومان)</label>
                                <div class="input-group">
                                    <input type="text" class="form-control price-input" name="selling_price" required
                                           data-price-type="sell">
                                    <button class="btn btn-outline-secondary calculator-btn" type="button"
                                            data-bs-toggle="modal" data-bs-target="#priceCalculatorModal">
                                        <i class="bi bi-calculator"></i>
                                    </button>
                                </div>
                                <div class="price-preview mt-1" id="priceInWords"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">قیمت عمده (تومان)</label>
                                <input type="text" class="form-control price-input" name="wholesale_price"
                                       data-price-type="wholesale">
                            </div>

                            <hr>

                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="hasTax" name="has_tax">
                                        <label class="form-check-label">مالیات</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" name="tax_rate" value="9"
                                               min="0" max="100" step="0.1" disabled>
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- کارت موجودی -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">موجودی</h3>
                            <span class="stock-status" id="currentStockStatus">موجودی: 0</span>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label required">موجودی اولیه</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="initial_stock" required
                                           min="0" step="1" value="0">
                                    <span class="input-group-text">عدد</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">حداقل موجودی</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="minimum_stock"
                                           min="0" step="1" value="1">
                                    <span class="input-group-text">عدد</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">نقطه سفارش</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="reorder_point"
                                           min="0" step="1" value="2">
                                    <span class="input-group-text">عدد</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- مودال محاسبه‌گر قیمت -->
<div class="modal fade" id="priceCalculatorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">محاسبه‌گر قیمت</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">قیمت خرید (تومان)</label>
                    <input type="text" class="form-control price-input" id="calcPurchasePrice">
                </div>
                <div class="mb-3">
                    <label class="form-label">درصد سود</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="calcProfitPercent"
                               min="0" max="1000" step="0.1">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">هزینه‌های جانبی (تومان)</label>
                    <input type="text" class="form-control price-input" id="calcExtraCosts">
                </div>
                <hr>
                <div class="result-section">
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">قیمت نهایی:</label>
                            <div class="h5" id="calcFinalPrice">0 تومان</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label">سود خالص:</label>
                            <div class="h5" id="calcNetProfit">0 تومان</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                <button type="button" class="btn btn-primary" id="applyCalculatedPrice">
                    اعمال قیمت
                </button>
            </div>
        </div>
    </div>
</div>

<?php

// اضافه کردن اسکریپت‌ها
add_footer_scripts([
    '<script src="assets/js/select2.min.js"></script>',
    '<script src="assets/js/dropzone.min.js"></script>',
    '<script>
    document.addEventListener("DOMContentLoaded", function() {
        // راه‌اندازی Select2
        $(".select2").select2({
            dir: "rtl",
            language: "fa"
        });

        // راه‌اندازی Dropzone
        const myDropzone = new Dropzone("#productImages", {
            url: "ajax_handler.php",
            paramName: "file",
            maxFiles: 5,
            maxFilesize: 2,
            acceptedFiles: "image/*",
            addRemoveLinks: true,
            dictDefaultMessage: "تصاویر را اینجا رها کنید"
        });

        // مدیریت قیمت‌ها
        const priceInputs = document.querySelectorAll(".price-input");
        priceInputs.forEach(input => {
            input.addEventListener("input", function(e) {
                let value = this.value.replace(/[^\d]/g, "");
                this.value = new Intl.NumberFormat("fa-IR").format(value);
                
                if (this.name === "purchase_price" || this.name === "selling_price") {
                    updateProfit();
                }
            });
        });

        // محاسبه و نمایش سود
        function updateProfit() {
            const purchase = parseInt(document.querySelector("[name=purchase_price]").value.replace(/[^\d]/g, "")) || 0;
            const selling = parseInt(document.querySelector("[name=selling_price]").value.replace(/[^\d]/g, "")) || 0;
            
            if (purchase > 0 && selling > 0) {
                const profit = ((selling - purchase) / purchase) * 100;
                const profitStatus = document.getElementById("profitStatus");
                profitStatus.querySelector("span").textContent = profit.toFixed(1);
                
                if (profit < 0) {
                    profitStatus.className = "profit-badge loss";
                } else {
                    profitStatus.className = "profit-badge normal-profit";
                }
            }
        }

        // مدیریت محاسبه‌گر قیمت
        const calculator = {
            calculate: function() {
                const purchase = parseInt(document.getElementById("calcPurchasePrice").value.replace(/[^\d]/g, "")) || 0;
                const profit = parseFloat(document.getElementById("calcProfitPercent").value) || 0;
                const extra = parseInt(document.getElementById("calcExtraCosts").value.replace(/[^\d]/g, "")) || 0;

                const final = purchase * (1 + profit/100) + extra;
                const netProfit = final - purchase - extra;

                document.getElementById("calcFinalPrice").textContent = 
                    new Intl.NumberFormat("fa-IR").format(final) + " تومان";
                document.getElementById("calcNetProfit").textContent = 
                    new Intl.NumberFormat("fa-IR").format(netProfit) + " تومان";
            }
        };

        // رویدادهای محاسبه‌گر قیمت
        document.getElementById("calcProfitPercent").addEventListener("input", calculator.calculate);
        document.getElementById("calcExtraCosts").addEventListener("input", calculator.calculate);
        
        document.getElementById("applyCalculatedPrice").addEventListener("click", function() {
            const finalPrice = document.getElementById("calcFinalPrice").textContent
                .replace(/[^\d]/g, "");
            
            document.querySelector("[name=selling_price]").value = 
                new Intl.NumberFormat("fa-IR").format(finalPrice);
            
            updateProfit();
            bootstrap.Modal.getInstance(document.getElementById("priceCalculatorModal")).hide();
        });

        // مدیریت مالیات
        document.getElementById("hasTax").addEventListener("change", function() {
            document.querySelector("[name=tax_rate]").disabled = !this.checked;
        });

        // مدیریت کد سفارشی
        document.getElementById("customCode").addEventListener("change", function() {
            const codeInput = document.querySelector("[name=product_code]");
            codeInput.readOnly = !this.checked;
            if (this.checked) {
                codeInput.focus();
            }
        });
    });
    </script>'
]);
?>