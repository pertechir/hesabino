// فقط یکبار اجرا شود
let dropzoneInitialized = false;

$(document).ready(function() {
    initializeSelect2();
    initializeDropzone();
    initializeFormHandlers();
});

function initializeSelect2() {
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
    }
}

function initializeDropzone() {
    if (typeof Dropzone !== 'undefined' && !dropzoneInitialized) {
        // غیرفعال کردن auto discover
        Dropzone.autoDiscover = false;
        
        // اطمینان از وجود المان
        const dropzoneElement = document.getElementById('productImages');
        if (dropzoneElement) {
            const myDropzone = new Dropzone("#productImages", {
                url: "upload.php", // آدرس آپلود فایل
                paramName: "file",
                maxFiles: 5,
                maxFilesize: 2,
                acceptedFiles: "image/*",
                addRemoveLinks: true,
                dictDefaultMessage: '<div class="text-center"><i class="bi bi-cloud-upload display-4"></i><br>تصاویر را اینجا رها کنید یا کلیک کنید</div>',
                dictRemoveFile: "حذف",
                dictCancelUpload: "لغو",
                init: function() {
                    this.on("success", function(file, response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.success) {
                                file.serverFileName = data.filename;
                                const input = $('<input>', {
                                    type: 'hidden',
                                    name: 'uploaded_files[]',
                                    value: data.filename
                                });
                                $('#addProductForm').append(input);
                            }
                        } catch (e) {
                            console.error('Error parsing upload response:', e);
                        }
                    });
                }
            });
            dropzoneInitialized = true;
        }
    }
}

function initializeFormHandlers() {
    // مدیریت واحد فرعی
    $('#hasSubUnit').on('change', function() {
        const subUnitSection = $('#subUnitSection');
        if (this.checked) {
            subUnitSection.slideDown(300);
            // فعال کردن فیلدهای اجباری
            $('input[name="sub_unit"], input[name="conversion_factor"]').prop('required', true);
        } else {
            subUnitSection.slideUp(300);
            // غیرفعال کردن فیلدهای اجباری
            $('input[name="sub_unit"], input[name="conversion_factor"]').prop('required', false);
        }
    });

    // مدیریت کنترل موجودی - همیشه فعال
    $('#inventorySettings').show();
    $('#inventoryControl')
        .prop('checked', true)
        .prop('disabled', true);

    // مدیریت کد حسابداری سفارشی
    $('#customCode').on('change', function() {
        const codeInput = $('input[name="custom_code"]');
        if (this.checked) {
            codeInput.prop('readonly', false).val('');
        } else {
            codeInput.prop('readonly', true);
            $.get('ajax/get_last_code.php')
                .done(function(data) {
                    codeInput.val(data.code);
                })
                .fail(function(error) {
                    console.error('Error fetching accounting code:', error);
                });
        }
    });

    // فعال کردن/غیرفعال کردن فیلدهای مالیات فروش
    $('#salesTax').on('change', function() {
        $('input[name="sales_tax"]').prop('disabled', !this.checked);
        if (!this.checked) {
            $('input[name="sales_tax"]').val('');
        }
    });

    // فعال کردن/غیرفعال کردن فیلدهای مالیات خرید
    $('#purchaseTax').on('change', function() {
        $('input[name="purchase_tax"]').prop('disabled', !this.checked);
        if (!this.checked) {
            $('input[name="purchase_tax"]').val('');
        }
    });

    // اعتبارسنجی فرم
    setupFormValidation();
}

// تولید بارکد
function generateBarcode() {
    const timestamp = new Date().getTime().toString().slice(-12);
    $('input[name="barcode"]').val(timestamp);
}

// ذخیره لیست قیمت
function savePriceList() {
    $('#priceListModal input[type="number"]').each(function() {
        const mainInput = $(`input[name="${$(this).attr('name')}"]`);
        if (mainInput.length) {
            mainInput.val($(this).val());
        }
    });
    $('#priceListModal').modal('hide');
}

// اعتبارسنجی فرم
function setupFormValidation() {
    const form = document.getElementById('addProductForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    }
}

// مدیریت فرمت اعداد
$(document).on('input', 'input[type="number"]', function() {
    if ($(this).hasClass('price-input')) {
        let value = this.value.replace(/[^\d]/g, '');
        if (value) {
            value = parseInt(value).toLocaleString('fa-IR');
            $(this).val(value);
        }
    }
});