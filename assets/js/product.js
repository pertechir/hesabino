// منتظر بمانید تا jQuery کاملاً لود شود
$(document).ready(function() {
    // اطمینان از وجود jQuery و select2
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
        $('.select2').select2({
            theme: 'bootstrap-5',
            dir: 'rtl',
            language: {
                noResults: function() {
                    return "نتیجه‌ای یافت نشد";
                }
            }
        });
    } else {
        console.error('jQuery یا Select2 به درستی لود نشده است');
    }





    // تنظیمات Dropzone
Dropzone.autoDiscover = false;
new Dropzone("#productImages", {
    url: "upload.php",
    acceptedFiles: "image/*",
    maxFiles: 5,
    maxFilesize: 2,
    dictDefaultMessage: "تصاویر را اینجا رها کنید یا کلیک کنید",
    addRemoveLinks: true
});

// فعال‌سازی Select2
$(document).ready(function() {
    $('.select2').select2({
        theme: "bootstrap-5",
        dir: "rtl"
    });
});

// مدیریت کد حسابداری سفارشی
document.getElementById('customCode').addEventListener('change', function() {
    const codeInput = document.querySelector('input[name="custom_code"]');
    codeInput.readOnly = !this.checked;
    if (!this.checked) {
        codeInput.value = '<?php echo getLastAccountingCode($pdo); ?>';
    } else {
        codeInput.value = '';
    }
});

// مدیریت واحد فرعی
document.getElementById('hasSubUnit').addEventListener('change', function() {
    document.getElementById('subUnitSection').style.display = this.checked ? 'block' : 'none';
});

// مدیریت کنترل موجودی
document.getElementById('inventoryControl').addEventListener('change', function() {
    document.getElementById('inventorySettings').style.display = this.checked ? 'block' : 'none';
});

// تولید بارکد
function generateBarcode() {
    const timestamp = new Date().getTime().toString().slice(-12);
    document.querySelector('input[name="barcode"]').value = timestamp;
}

// ذخیره لیست قیمت
function savePriceList() {
    // انتقال مقادیر به فرم اصلی
    const modal = document.getElementById('priceListModal');
    const inputs = modal.querySelectorAll('input[type="number"]');
    inputs.forEach(input => {
        const mainInput = document.querySelector(`input[name="${input.name}"]`);
        if (mainInput) mainInput.value = input.value;
    });
    
    // بستن مودال
    bootstrap.Modal.getInstance(modal).hide();
}

// اعتبارسنجی فرم
(function () {
    'use strict'
    const form = document.getElementById('addProductForm');
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);
    })();
});