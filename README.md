# Laravel AdMob SSV
A Laravel package for verifying AdMob Server-Side Verification (SSV) callbacks.

## Requirements
- PHP ^8.2
- Laravel ^12.0

## Installation
composer require cuongnx/laravel-admob-ssv

## Usage
In controller:
```php
use CuongNX\LaravelAdMobSSV\AdMob;

public function handle(Request $request) {
    $admob = new AdMob($request);
    $result = $admob->validate();
    if ($result['status']) {
        // Handle reward
        return response('OK', 200);
    }
    return response($result['message'], 400);
}
```

---
<h2 id="donate">💖 Donate</h2>
If you find this package useful, feel free to support the development:

### ☕ Coffee & Support

* [https://coff.ee/xuancuong2f](https://coff.ee/xuancuong2f)
* [https://paypal.me/cuongnx91](https://paypal.me/cuongnx91)

### 🏦 Bank (VIETQR)

> ![QR Code Techcombank](https://img.vietqr.io/image/970407-1368686856-print.png?accountName=Nguyen%20Xuan%20Cuong)
>
> **Account Holder**: NGUYEN XUAN CUONG  
> **Account Number**: `1368686856`  
> **Bank**: Techcombank


---

<h2 id="contact">📬 Contact</h2>

* Email: [xuancuong220691@gmail.com](mailto:xuancuong220691@gmail.com)

---

<h2 id="license">📝 License</h2>

* MIT License © [Cuong Nguyen](mailto:xuancuong220691@gmail.com)