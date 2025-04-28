<?php
/**
 * توابع کمکی حسابداری
 * @package Hesabino
 */

/**
 * تبدیل عدد به حروف فارسی
 */
function numberToWords($number) {
    $ones = [
        0 => '', 1 => 'یک', 2 => 'دو', 3 => 'سه', 4 => 'چهار', 5 => 'پنج',
        6 => 'شش', 7 => 'هفت', 8 => 'هشت', 9 => 'نه'
    ];
    
    $tens = [
        1 => 'ده', 2 => 'بیست', 3 => 'سی', 4 => 'چهل', 5 => 'پنجاه',
        6 => 'شصت', 7 => 'هفتاد', 8 => 'هشتاد', 9 => 'نود'
    ];
    
    $teens = [
        11 => 'یازده', 12 => 'دوازده', 13 => 'سیزده', 14 => 'چهارده', 
        15 => 'پانزده', 16 => 'شانزده', 17 => 'هفده', 18 => 'هجده', 19 => 'نوزده'
    ];
    
    $hundreds = [
        1 => 'صد', 2 => 'دویست', 3 => 'سیصد', 4 => 'چهارصد', 5 => 'پانصد',
        6 => 'ششصد', 7 => 'هفتصد', 8 => 'هشتصد', 9 => 'نهصد'
    ];
    
    $classes = ['', 'هزار', 'میلیون', 'میلیارد', 'تریلیون'];
    
    if ($number == 0) return 'صفر';
    
    $words = [];
    $splits = str_split(strrev((string)$number), 3);
    
    foreach ($splits as $i => $split) {
        $split = strrev($split);
        if ($split != '000') {
            $group = [];
            
            // صدگان
            if (strlen($split) == 3) {
                $group[] = $hundreds[$split[0]];
                $split = substr($split, 1);
            }
            
            // دهگان و یکان
            if (strlen($split) == 2) {
                if ($split >= 11 && $split <= 19) {
                    $group[] = $teens[$split];
                } else {
                    if ($split[0] != '0') $group[] = $tens[$split[0]];
                    if ($split[1] != '0') $group[] = $ones[$split[1]];
                }
            } elseif (strlen($split) == 1 && $split != '0') {
                $group[] = $ones[$split];
            }
            
            $words[] = implode(' و ', $group) . ($i > 0 ? ' ' . $classes[$i] : '');
        }
    }
    
    return implode(' و ', array_reverse($words));
}

/**
 * محاسبه درصد سود
 */
function calculateProfitMargin($purchase, $sell) {
    if ($purchase <= 0) return 0;
    return (($sell - $purchase) / $purchase) * 100;
}

/**
 * دریافت کد حسابداری جدید
 */
function getNextAccountingCode($pdo, $prefix = 'P') {
    $stmt = $pdo->prepare("
        SELECT accounting_code 
        FROM products 
        WHERE accounting_code LIKE :prefix
        ORDER BY accounting_code DESC 
        LIMIT 1
    ");
    $stmt->execute(['prefix' => $prefix . '%']);
    
    if ($last = $stmt->fetch()) {
        $number = intval(substr($last['accounting_code'], strlen($prefix))) + 1;
    } else {
        $number = 1;
    }
    
    return $prefix . str_pad($number, 5, '0', STR_PAD_LEFT);
}

/**
 * فرمت‌بندی قیمت
 */
function formatPrice($price, $includeSymbol = true) {
    $formatted = number_format($price, 0, '.', ',');
    return $includeSymbol ? $formatted . ' تومان' : $formatted;
}

/**
 * محاسبه قیمت با مالیات
 */
function calculatePriceWithTax($price, $taxRate = 9) {
    return $price * (1 + ($taxRate / 100));
}

/**
 * ثبت تاریخچه قیمت
 */
function logPriceHistory($pdo, $productId, $price, $type, $userId, $description = null) {
    $stmt = $pdo->prepare("
        INSERT INTO price_history (
            product_id, price, price_type, user_id, description, created_at
        ) VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    return $stmt->execute([
        $productId, $price, $type, $userId, $description
    ]);
}