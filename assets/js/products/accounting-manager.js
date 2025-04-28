class ProductAccountingManager {
    constructor() {
        this.priceInputs = {};
        this.currentPrices = {
            purchase: 0,
            sell: 0,
            wholesale: 0,
            partner: 0,
            agency: 0
        };
        this.init();
    }

    init() {
        this.initializePriceInputs();
        this.initializeCalculator();
        this.initializeTaxSettings();
        this.bindEvents();
    }

    initializePriceInputs() {
        document.querySelectorAll('.price-input').forEach(input => {
            const type = input.dataset.priceType;
            this.priceInputs[type] = input;
            
            input.addEventListener('input', (e) => {
                this.formatPrice(e.target);
                this.updatePrices();
            });
        });
    }

    formatPrice(input) {
        let value = input.value.replace(/[^\d]/g, '');
        input.value = new Intl.NumberFormat('fa-IR').format(value);
        
        if (input.dataset.priceType === 'sell') {
            this.updatePriceInWords(value);
        }
    }

    updatePriceInWords(price) {
        const words = this.numberToWords(price);
        document.getElementById('priceInWords').textContent = words + ' تومان';
    }

    updatePrices() {
        Object.keys(this.priceInputs).forEach(type => {
            const value = parseInt(this.priceInputs[type].value.replace(/[^\d]/g, '')) || 0;
            this.currentPrices[type] = value;
        });

        this.calculateProfit();
    }

    calculateProfit() {
        const purchase = this.currentPrices.purchase;
        const sell = this.currentPrices.sell;
        
        if (purchase > 0 && sell > 0) {
            const profit = sell - purchase;
            const margin = (profit / purchase) * 100;
            
            const profitStatus = document.getElementById('profitStatus');
            profitStatus.querySelector('span').textContent = 
                new Intl.NumberFormat('fa-IR').format(profit);
            
            this.updateProfitStyle(margin);
        }
    }

    updateProfitStyle(margin) {
        const profitStatus = document.getElementById('profitStatus');
        profitStatus.className = 'profit-badge';
        
        if (margin < 0) {
            profitStatus.classList.add('loss');
        } else if (margin < 10) {
            profitStatus.classList.add('low-profit');
        } else if (margin > 50) {
            profitStatus.classList.add('high-profit');
        } else {
            profitStatus.classList.add('normal-profit');
        }
    }

    initializeCalculator() {
        const modal = document.getElementById('priceCalculatorModal');
        if (modal) {
            modal.addEventListener('shown.bs.modal', () => {
                document.getElementById('calcPurchasePrice').value = 
                    this.priceInputs.purchase.value;
            });

            document.getElementById('calcProfitPercent').addEventListener('input', () => {
                this.calculateFinalPrice();
            });

            document.getElementById('calcExtraCosts').addEventListener('input', (e) => {
                this.formatPrice(e.target);
                this.calculateFinalPrice();
            });

            document.getElementById('applyCalculatedPrice').addEventListener('click', () => {
                this.applyCalculatedPrice();
            });
        }
    }

    calculateFinalPrice() {
        const purchase = parseInt(document.getElementById('calcPurchasePrice').value.replace(/[^\d]/g, '')) || 0;
        const profit = parseFloat(document.getElementById('calcProfitPercent').value) || 0;
        const extra = parseInt(document.getElementById('calcExtraCosts').value.replace(/[^\d]/g, '')) || 0;

        const finalPrice = purchase * (1 + profit/100) + extra;
        const netProfit = finalPrice - purchase - extra;

        document.getElementById('calcFinalPrice').textContent = 
            new Intl.NumberFormat('fa-IR').format(finalPrice) + ' تومان';
        document.getElementById('calcNetProfit').textContent = 
            new Intl.NumberFormat('fa-IR').format(netProfit) + ' تومان';
    }

    applyCalculatedPrice() {
        const finalPrice = document.getElementById('calcFinalPrice').textContent
            .replace(/[^\d]/g, '');
        
        this.priceInputs.sell.value = new Intl.NumberFormat('fa-IR').format(finalPrice);
        this.updatePrices();
        
        bootstrap.Modal.getInstance(document.getElementById('priceCalculatorModal')).hide();
        showToast('قیمت جدید اعمال شد', 'success');
    }

    initializeTaxSettings() {
        ['Tax', 'Discount', 'Commission'].forEach(type => {
            const checkbox = document.getElementById(`has${type}`);
            const input = document.querySelector(`[name="${type.toLowerCase()}_rate"]`);
            
            if (checkbox && input) {
                checkbox.addEventListener('change', () => {
                    input.disabled = !checkbox.checked;
                    if (checkbox.checked) {
                        input.focus();
                    }
                });
            }
        });
    }

    async loadPriceHistory(type) {
        try {
            const response = await fetch(`/api/products/price-history.php?type=${type}`);
            const data = await response.json();
            
            const tbody = document.getElementById('priceHistoryBody');
            tbody.innerHTML = data.map(item => `
                <tr>
                    <td>${this.formatDate(item.date)}</td>
                    <td>${new Intl.NumberFormat('fa-IR').format(item.price)}</td>
                    <td>${item.type}</td>
                    <td>${item.user}</td>
                    <td>${item.description || '-'}</td>
                </tr>
            `).join('');
        } catch (error) {
            console.error('Error loading price history:', error);
            showToast('خطا در بارگذاری تاریخچه قیمت', 'error');
        }
    }

    formatDate(date) {
        return new Date(date).toLocaleDateString('fa-IR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // ... سایر متدها
}

// راه‌اندازی
document.addEventListener('DOMContentLoaded', () => {
    window.productAccountingManager = new ProductAccountingManager();
});