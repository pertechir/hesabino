    <!-- اسکریپت‌های اصلی -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <?php if (isset($page) && $page == 'add-product'): ?>
        <!-- اسکریپت‌های صفحه محصول -->
        <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
        <script src="assets/js/product.js"></script>
    <?php endif; ?>
</body>
</html>