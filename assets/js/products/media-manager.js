class ProductMediaManager {
    constructor() {
        this.imageDropzone = null;
        this.videoDropzone = null;
        this.maxImages = 10;
        this.maxVideoSize = 50; // MB
        this.imageCounter = 0;
        this.init();
    }

    init() {
        this.initImageDropzone();
        this.initVideoDropzone();
        this.initSortable();
        this.bindEvents();
    }

    initImageDropzone() {
        this.imageDropzone = new Dropzone("#productImageDropzone", {
            url: "/api/products/upload-media.php",
            paramName: "file",
            maxFiles: this.maxImages,
            maxFilesize: 2,
            acceptedFiles: "image/jpeg,image/png,image/webp",
            addRemoveLinks: true,
            previewTemplate: this.getPreviewTemplate(),
            init: () => {
                this.handleDropzoneEvents();
            }
        });
    }

    initVideoDropzone() {
        this.videoDropzone = new Dropzone("#videoDropzone", {
            url: "/api/products/upload-media.php",
            paramName: "file",
            maxFiles: 1,
            maxFilesize: this.maxVideoSize,
            acceptedFiles: "video/mp4,video/webm",
            addRemoveLinks: true,
            init: () => {
                this.handleVideoDropzoneEvents();
            }
        });
    }

    handleDropzoneEvents() {
        this.imageDropzone.on("success", (file, response) => {
            const data = JSON.parse(response);
            if (data.success) {
                this.addImagePreview(data.url, data.id);
                showToast("تصویر با موفقیت آپلود شد", "success");
            }
        });

        this.imageDropzone.on("error", (file, message) => {
            showToast("خطا در آپلود تصویر: " + message, "error");
        });

        this.imageDropzone.on("removedfile", (file) => {
            this.removeImage(file);
        });
    }

    handleVideoDropzoneEvents() {
        this.videoDropzone.on("success", (file, response) => {
            const data = JSON.parse(response);
            if (data.success) {
                this.updateVideoPreview(data.url);
                showToast("ویدیو با موفقیت آپلود شد", "success");
            }
        });

        this.videoDropzone.on("error", (file, message) => {
            showToast("خطا در آپلود ویدیو: " + message, "error");
        });
    }

    // ... ادامه متدها
}

// راه‌اندازی
document.addEventListener('DOMContentLoaded', () => {
    window.productMediaManager = new ProductMediaManager();
});