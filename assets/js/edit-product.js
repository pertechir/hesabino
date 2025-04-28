
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

$(document).ready(function() {
    // راه‌اندازی Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        language: {
            noResults: function() {
                return "نتیجه‌ای یافت نشد";
            }
        }
    });









    
    // پیکربندی Dropzone
    var uploadedFiles = JSON.parse($("#uploadedFiles").val() || '[]');
    var myDropzone = new Dropzone("#productImages", {
        url: "upload.php",
        paramName: "file",
        maxFilesize: 2,
        maxFiles: 5,
        acceptedFiles: "image/*",
        addRemoveLinks: true,
        dictDefaultMessage: '<div class="text-center"><i class="bi bi-cloud-upload display-4"></i><br>تصاویر را اینجا بکشید و رها کنید یا کلیک کنید</div>',
        dictFallbackMessage: "مرورگر شما از کشیدن و رها کردن فایل پشتیبانی نمی‌کند",
        dictFileTooBig: "حجم فایل بیش از حد مجاز است ({{filesize}}MB). حداکثر حجم مجاز: {{maxFilesize}}MB",
        dictInvalidFileType: "این نوع فایل مجاز نیست",
        dictResponseError: "خطا در آپلود فایل ({{statusCode}})",
        dictCancelUpload: "لغو آپلود",
        dictUploadCanceled: "آپلود لغو شد",
        dictCancelUploadConfirmation: "آیا از لغو آپلود اطمینان دارید؟",
        dictRemoveFile: "حذف فایل",
        dictMaxFilesExceeded: "نمی‌توانید فایل بیشتری آپلود کنید",
        
        init: function() {
            var dz = this;
            
            // نمایش تصاویر موجود در Dropzone
            if (uploadedFiles.length > 0) {
                uploadedFiles.forEach(function(filePath) {
                    var mockFile = {
                        name: filePath.split('/').pop(),
                        size: 12345,
                        accepted: true,
                        status: Dropzone.ADDED
                    };
                    
                    dz.emit("addedfile", mockFile);
                    dz.emit("thumbnail", mockFile, filePath);
                    dz.emit("complete", mockFile);
                    dz.files.push(mockFile);
                    
                    // افزودن مسیر فایل به mockFile
                    mockFile.filePath = filePath;
                });
                
                // به‌روزرسانی تعداد فایل‌های مجاز
                this.options.maxFiles = Math.max(0, this.options.maxFiles - uploadedFiles.length);
            }
        },

        success: function(file, response) {
            if (response.success) {
                // افزودن مسیر فایل به آرایه
                uploadedFiles.push(response.filePath);
                $("#uploadedFiles").val(JSON.stringify(uploadedFiles));
                
                // ذخیره مسیر فایل در آبجکت فایل
                file.filePath = response.filePath;
                
                Swal.fire({
                    icon: 'success',
                    title: 'موفق',
                    text: 'فایل با موفقیت آپلود شد',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        },

        removedfile: function(file) {
            var filePath = file.filePath;
            if (filePath) {
                Swal.fire({
                    title: 'آیا مطمئن هستید؟',
                    text: "این عملیات قابل بازگشت نیست!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'بله، حذف شود',
                    cancelButtonText: 'انصراف'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'delete-image.php',
                            method: 'POST',
                            contentType: 'application/json',
                            data: JSON.stringify({ filePath: filePath }),
                            success: function(response) {
                                if (response.success) {
                                    // حذف از آرایه
                                    var index = uploadedFiles.indexOf(filePath);
                                    if (index !== -1) {
                                        uploadedFiles.splice(index, 1);
                                        $("#uploadedFiles").val(JSON.stringify(uploadedFiles));
                                    }
                                    
                                    // حذف از DOM
                                    file.previewElement.remove();
                                    
                                    // افزایش تعداد فایل‌های مجاز
                                    myDropzone.options.maxFiles = Math.min(5, myDropzone.options.maxFiles + 1);
                                    
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'موفق',
                                        text: 'فایل با موفقیت حذف شد',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'خطا',
                                    text: 'خطا در حذف فایل'
                                });
                            }
                        });
                    } else {
                        // اگر کاربر انصراف داد، تصویر را به لیست برگردانیم
                        myDropzone.emit("addedfile", file);
                        myDropzone.emit("thumbnail", file, file.filePath);
                        myDropzone.emit("complete", file);
                        myDropzone.files.push(file);
                    }
                });
            } else {
                file.previewElement.remove();
            }
            return false;
        },
        
        error: function(file, message) {
            Swal.fire({
                icon: 'error',
                title: 'خطا',
                text: message
            });
        }
    });
});

// حذف تصویر موجود
function removeExistingImage(button) {
    var container = button.closest('.image-container');
    var filePath = container.dataset.image;
    var uploadedFiles = JSON.parse($("#uploadedFiles").val() || '[]');
    
    Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: "این عملیات قابل بازگشت نیست!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'بله، حذف شود',
        cancelButtonText: 'انصراف'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'delete-image.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ filePath: filePath }),
                success: function(response) {
                    if (response.success) {
                        // حذف از آرایه
                        var index = uploadedFiles.indexOf(filePath);
                        if (index !== -1) {
                            uploadedFiles.splice(index, 1);
                            $("#uploadedFiles").val(JSON.stringify(uploadedFiles));
                        }
                        
                        // حذف از DOM
                        container.remove();
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'موفق',
                            text: 'تصویر با موفقیت حذف شد',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا',
                        text: 'خطا در حذف تصویر'
                    });
                }
            });
        }
    });
}