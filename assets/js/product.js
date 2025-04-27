
$(document).ready(function () {
    // تنظیمات Select2
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

    // تنظیمات Dropzone
    if (typeof Dropzone !== 'undefined') {
        Dropzone.autoDiscover = false;
        new Dropzone("#productImages", {
            url: "upload.php",
            paramName: "file",
            maxFiles: 5,
            maxFilesize: 2,
            acceptedFiles: "image/*",
            dictDefaultMessage: '<div class="text-center"><i class="bi bi-cloud-upload display-4"></i><br>تصاویر را اینجا رها کنید یا کلیک کنید</div>',
            addRemoveLinks: true,
            dictRemoveFile: "حذف",
            dictCancelUpload: "لغو",
            init: function() {
                this.on("success", function(file, response) {
                    try {
                        const data = JSON.parse(response);
                        if (data.success) {
                            file.serverFileName = data.filename;
                            // اضافه کردن نام فایل به فرم
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
    }

    // مدیریت واحد فرعی
    $('#hasSubUnit').on('change', function() {
        $('#subUnitSection').slideToggle(300);
    });

    // مدیریت کنترل موجودی - همیشه فعال
    $('#inventorySettings').show();
    $('#inventoryControl').prop('checked', true).prop('disabled', true);

    // مدیریت کد حسابداری سفارشی
    $('#customCode').on('change', function() {
        const codeInput = $('input[name="custom_code"]');
        codeInput.prop('readonly', !this.checked);
        if (!this.checked) {
            $.get('ajax/get_last_code.php', function(data) {
                codeInput.val(data.code);
            });
        } else {
            codeInput.val('');
        }
    });

    // اعتبارسنجی فرم
    setupFormValidation();
});

// تولید بارکد
function generateBarcode() {
    const timestamp = new Date().getTime().toString().slice(-12);
    $('input[name="barcode"]').val(timestamp);
}

// ذخیره لیست قیمت
function savePriceList() {
    $('#priceListModal input[type="number"]').each(function() {
        const mainInput = $(`input[name="${$(this).attr('name')}"]`);
        if (mainInput.length) mainInput.val($(this).val());
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