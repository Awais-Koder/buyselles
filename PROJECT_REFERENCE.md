# Project Reference: BuySelles Multi-Vendor E-Commerce Platform

**Last Updated:** 2026-04-29  
**Project Type:** Multi-Vendor E-Commerce Marketplace  
**Laravel Version:** 12.x  
**PHP Version:** 8.3  

---

## Table of Contents

1. [Quick Start](#quick-start)
2. [Architecture Overview](#architecture-overview)
3. [Authentication & Authorization](#authentication--authorization)
4. [Database & Models](#database--models)
5. [Routing Structure](#routing-structure)
6. [Key Features](#key-features)
7. [Third-Party Integrations](#third-party-integrations)
8. [Modules System](#modules-system)
9. [Configuration Reference](#configuration-reference)
10. [Development Guidelines](#development-guidelines)

---

## Quick Start

### Prerequisites
- PHP 8.3+
- MySQL 8.0+ / MariaDB
- Redis (for cache/sessions)
- Node.js 18+ & NPM
- Composer

### Environment Setup
```bash
# Clone and install
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan passport:install

# Database setup
php artisan migrate --seed

# Build assets
npm run dev  # Development
npm run prod # Production

# Start development
php artisan serve
```

### Key Commands
```bash
# Testing
php artisan test --compact
php artisan test --filter=TestName

# Code Formatting
vendor/bin/pint --dirty --format agent

# Cache Management
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Queue Worker
php artisan queue:work --queue=default,order,email

# Scheduled Tasks (run in production)
php artisan schedule:work
```

---

## Architecture Overview

### Application Structure

```
buyselles/
├── app/
│   ├── Console/Commands/        # 9 custom Artisan commands
│   ├── Contracts/               # Interface contracts
│   ├── DTOs/                    # Data Transfer Objects
│   ├── Enums/                   # 12 type-safe enums
│   ├── Events/                  # 28 event classes
│   ├── Exceptions/              # Custom exceptions
│   ├── Exports/                 # 32 Excel/PDF export classes
│   ├── Http/
│   │   ├── Controllers/         # 15 controller directories
│   │   ├── Middleware/          # 27 middleware classes
│   │   ├── Requests/            # 18 Form Request classes
│   │   └── Resources/           # API resources (v1, v2, v3)
│   ├── Jobs/                    # 11 queued jobs
│   ├── Library/                 # Custom libraries (Constant.php, Responses.php)
│   ├── Listeners/               # 26 event listeners
│   ├── Mail/                    # 17 Mailable classes
│   ├── Models/                  # 132+ Eloquent models
│   ├── Notifications/           # Notification classes
│   ├── Observers/               # 5 model observers
│   ├── Providers/               # 15 service providers
│   ├── Repositories/            # 94 repository classes
│   ├── Rules/                   # Custom validation rules
│   ├── Services/                # 80 service classes
│   ├── Traits/                  # Reusable traits
│   └── Utils/                   # 20+ helper files
├── Modules/                     # Laravel Modules (AI, Blog, TaxModule)
├── routes/                      # Route files (web, admin, vendor, api)
├── resources/
│   ├── themes/                  # default, theme_aster
│   ├── views/                   # Admin, vendor, email templates
│   └── lang/                    # en, bd, ae, sa, es, in
├── database/
│   ├── migrations/              # 333+ migrations
│   ├── factories/               # Model factories
│   └── seeders/                 # Database seeders
└── public/                      # Web server root
```

### Service Providers

**Registered in `bootstrap/providers.php`:**

```php
App\Providers\AppServiceProvider::class
App\Providers\AuthServiceProvider::class
App\Providers\BroadcastServiceProvider::class
App\Providers\FirebaseServiceProvider::class
App\Providers\RouteServiceProvider::class
App\Providers\MailConfigServiceProvider::class
App\Providers\PaymentConfigProvider::class
App\Providers\ConfigServiceProvider::class
App\Providers\ThemeServiceProvider::class
App\Providers\SocialLoginServiceProvider::class
App\Providers\InterfaceServiceProvider::class
App\Providers\ObserverServiceProvider::class

// Third-party
Intervention\Image\ImageServiceProvider::class
Maatwebsite\Excel\ExcelServiceProvider::class
Madnest\Madzipper\MadzipperServiceProvider::class
```

### Middleware Configuration

**File:** `bootstrap/app.php`

**Global Middleware:**
- TrustProxies, CheckForMaintenanceMode, ValidatePostSize
- TrimStrings, ConvertEmptyStringsToNull
- DatabaseRefreshMiddleware, HandleCors

**Web Group:**
- EncryptCookies, AddQueuedCookiesToResponse, StartSession
- ShareErrorsFromSession, VerifyCsrfToken, SubstituteBindings
- Localization, DetectMobile

**API Group:**
- throttle:3000,1
- SubstituteBindings

**Route Middleware (Aliases):**
```php
'admin' => AdminMiddleware::class
'seller' => SellerMiddleware::class
'customer' => CustomerMiddleware::class
'delivery_man_auth' => DeliveryManAuth::class
'seller_api_auth' => SellerApiAuthMiddleware::class
'reseller_api_auth' => ResellerApiAuth::class
'module' => ModulePermissionMiddleware::class
'vendor_module' => VendorModulePermissionMiddleware::class
'installation-check' => InstallationMiddleware::class
'actch' => ActivationCheckMiddleware::class
'api_lang' => APILocalizationMiddleware::class
'maintenance_mode' => MaintenanceModeMiddleware::class
'guestCheck' => GuestMiddleware::class
'apiGuestCheck' => APIGuestMiddleware::class
'logUserBrowsingNavigation' => LogUserBrowsingNavigationMiddleware::class
```

---

## Authentication & Authorization

### Multi-Guard System

**Configuration:** `config/auth.php`

| Guard | Driver | Provider | Use Case |
|-------|--------|----------|----------|
| `web` | session | users (App\User) | Customer web sessions |
| `api` | passport | users | API authentication |
| `admin` | session | admins (App\Models\Admin) | Admin panel |
| `seller` | session | sellers (App\Models\Seller) | Vendor dashboard |
| `customer` | session | users | Customer area |

### User Providers

```php
'users' => ['driver' => 'eloquent', 'model' => App\User::class]
'admins' => ['driver' => 'eloquent', 'model' => App\Models\Admin::class]
'sellers' => ['driver' => 'eloquent', 'model' => App\Models\Seller::class]
```

### Password Resets

- **users, admins, sellers:** 60 minutes expiry, 60s throttle
- **Table:** `password_resets`

### Social Authentication

**Providers:** Google, Facebook, Twitter  
**Service:** `SocialLoginServiceProvider`  
**Config:** `config/services.php`

---

## Database & Models

### Key Model Categories

**User Types (6):**
- `User` (Customer)
- `Admin`
- `Seller`
- `Customer` (legacy/alias)
- `DeliveryMan`
- `GuestUser`

**Products (11):**
- `Product`, `ProductStock`, `ProductSeo`, `ProductTag`, `ProductCompare`
- `Category`, `Brand`, `Attribute`, `Color`
- `DigitalProductCode`, `DigitalProductVariation`

**Orders (8):**
- `Order`, `OrderDetail`, `OrderTransaction`, `OrderStatusHistory`
- `OrderEditHistory`, `OrderDeliveryVerification`
- `OrderExpectedDeliveryHistory`, `OrderDetailsRewards`

**Payments & Wallets (6):**
- `CustomerWallet`, `SellerWallet`, `DeliverymanWallet`, `AdminWallet`
- `WalletTransaction`, `Escrow`

**Promotions (5):**
- `FlashDeal`, `FlashDealProduct`, `DealOfTheDay`
- `Coupon`, `Banner`

**Support & Disputes (5):**
- `Dispute`, `DisputeEvidence`, `DisputeMessage`
- `DisputeReason`, `DisputeStatusLog`
- `RefundRequest`, `RefundStatus`, `RefundTransaction`

**Shipping & Location (6):**
- `ShippingAddress`, `BillingAddress`
- `LocationCountry`, `LocationCity`, `LocationArea`
- `DeliveryZipCode`, `DeliveryCountryCode`

**Notifications (4):**
- `Notification`, `NotificationMessage`, `NotificationSeen`
- `Chatting`, `DeliverymanNotification`

**Settings (5):**
- `BusinessSetting`, `Currency`, `Language`
- `SocialMedia`, `EmailTemplate`

### Total Models: 132+

### Migrations: 333+

**Started:** 2021-02-24  
**OAuth Tables:** 5 (Passport)

---

## Routing Structure

### Route Files

```
routes/
├── web/
│   └── routes.php           # Customer-facing routes
├── admin/
│   └── routes.php           # Admin panel (1354 lines)
├── vendor/
│   └── routes.php           # Vendor dashboard (464 lines)
├── rest_api/
│   ├── v1/
│   │   └── api.php          # API v1 endpoints
│   ├── v2/
│   │   └── api.php          # API v2 endpoints
│   └── v3/
│       └── seller.php       # Seller API v3
├── channels.php             # Broadcasting channels
├── console.php              # Scheduled tasks
├── shared.php               # Shared routes
├── install.php              # Installation routes
├── update.php               # Update routes
└── test.php                 # Test routes
```

### API Versioning

**V1 (26 controllers):**
- Auth: CustomerAPIAuthController, PassportAuthController, SocialAuthController
- Products: ProductController, CategoryController, BrandController
- Orders: OrderController, OrderEditController, CustomerController
- Promotions: CouponController, FlashDealController, DealOfTheDayController
- Features: CartController, ChatController, ReviewController
- Settings: ConfigController, GeneralController, MapApiController

**V2:** Additional API endpoints  
**V3:** Seller-specific APIs

### Controller Namespaces

```
app/Http/Controllers/
├── Admin/           # 39 controllers
├── Vendor/          # 26 controllers
├── Web/             # Customer web controllers
├── RestAPI/         # Versioned API controllers
├── Api/             # Legacy API controllers
├── Customer/        # Customer-specific controllers
├── Payment_Methods/ # 13 payment gateway controllers
├── Auth/            # Authentication controllers
└── SharedController.php
```

---

## Key Features

### Multi-Vendor Marketplace

- **Vendor Management:** Approval workflow, commission system
- **Vendor Wallet:** Withdrawals, transactions
- **Vendor Dashboard:** Products, orders, customers, reports
- **Module Permissions:** Granular access control

### Order Management

- **Multiple Payment Methods:** 13+ gateways
- **Order Editing:** Edit orders post-placement
- **Dispute System:** Arbitration, evidence, messages
- **Refund Management:** Status tracking, transactions
- **Escrow Protection:** Auto-release, dispute holds
  - **Important:** Escrow receipt confirmation is **ADMIN-ONLY**. Customers cannot release escrow funds.
  - Escrow status is visible to customers (read-only) for transparency
  - Admin can manually release escrow from admin panel
  - Auto-release occurs based on configured timeout (default: 48 hours)
- **Delivery Verification:** OTP/code verification

### Product Features

- **Product Types:** Physical, digital (PIN codes)
- **Variations:** SKU combinations, colors, attributes
- **Digital Products:** OTP verification, code delivery
- **Reviews & Ratings:** Review replies, seller responses
- **Deals:** Flash deals, daily deals, featured deals
- **Clearance Sales:** Discounted inventory
- **Wishlist & Compare:** Product comparison

### Customer Features

- **Wallet System:** Add funds, bonuses, transactions
- **Loyalty Points:** Point transactions, rewards
- **Referral Program:** Referral codes, customer tracking
- **Chat Support:** Real-time messaging
- **Order Tracking:** Status history, expected delivery
- **Support Tickets:** Help topics, ticket management
- **Restock Requests:** Notify when products available

### Admin Features

- **Dashboard:** Analytics, orders, revenue
- **Product Management:** Categories, brands, attributes
- **Order Management:** All orders, disputes, refunds
- **User Management:** Customers, vendors, delivery men
- **Promotions:** Banners, coupons, flash deals
- **Reports:** Earnings, products, orders, transactions
- **Settings:** Business, payment, shipping, theme
- **Module/Addon:** Enable/disable modules
- **File Manager:** Centralized file management
- **Notifications:** Push notification settings

### Technical Features

- **Multi-language:** English, Bengali, Arabic (UAE/SA), Spanish, Hindi
- **Multi-currency:** Exchange rate management
- **SEO:** Meta tags, sitemap generation
- **Firebase:** Push notifications
- **Real-time:** Laravel Reverb (WebSocket)
- **Queues:** Background job processing
- **Scheduling:** Automated tasks
- **Modules:** Extensible module system

---

## Third-Party Integrations

### Payment Gateways (13+)

| Gateway | Package | Config File |
|---------|---------|-------------|
| PayPal | paypal/rest-api-sdk-php ^1.6 | config/paypal.php |
| Stripe | stripe/stripe-php ^13.9 | - |
| Razorpay | razorpay/razorpay ^2.9 | config/razor.php |
| SSLCommerz | Custom | config/sslcommerz.php |
| Flutterwave | flamkeed/laravel-rave | config/flutterwave.php |
| PayTM | Custom | config/paytm.php |
| MercadoPago | mercadopago/dx-php 3.8.0 | - |
| PhonePe | phonepe/phonepe-pg-php-sdk ^1.0 | config/payuz.php |
| Xendit | xendit/xendit-php ^4.1 | - |
| Iyzico | iyzico/iyzipay-php ^2.0 | - |
| Paymob | Custom | - |
| Paystack | Custom | - |
| Paytabs | Custom | - |
| LiqPay | Custom | - |
| SenangPay | Custom | - |
| BKash | Custom | - |

### Cloud & Storage

- **AWS S3:** aws/aws-sdk-php ^3.209, league/flysystem-aws-s3-v3
- **Firebase:** kreait/firebase-php ^7.22

### AI & Communication

- **OpenAI:** openai-php/laravel ^0.11.0 (config/openai.php)
- **Twilio:** twilio/sdk ^7.14 (SMS)
- **Nexmo/Vonage:** SMS configuration

### E-commerce Tools

- **Excel:** maatwebsite/excel, phpoffice/phpspreadsheet, rap2hpoutre/fast-excel
- **PDF:** barryvdh/laravel-dompdf ^3.0, mpdf/mpdf ^8.2
- **Image:** intervention/image ^2.7, spatie/image-optimizer ^1.7
- **Barcode:** milon/barcode ^12.0
- **ZIP:** madnest/madzipper

### Modular Architecture

- **Laravel Modules:** nwidart/laravel-modules ^10.0

### Development & Debugging

- **Debug Bar:** barryvdh/laravel-debugbar ^3.14
- **Laravel Boost:** laravel/boost ^2.3
- **Laravel Pint:** laravel/pint ^1.0
- **Laravel Sail:** laravel/sail ^1.18

### Other Utilities

- **Captcha:** gregwar/captcha ^1.1
- **Geo/Location:** devrabiul/laravel-geo-genius
- **Sitemap:** spatie/laravel-sitemap ^7.1
- **Query Cache:** rennokki/laravel-eloquent-query-cache ^3.4
- **Notifications:** brian2694/laravel-toastr ^5.56, devrabiul/laravel-toaster-magic

---

## Modules System

### Active Modules

**Location:** `Modules/`

1. **AI Module** (`Modules/AI/`)
   - AIProviders, Addon
   - Routes: api.php, web.php
   - Config, database, resources, assets

2. **Blog Module** (`Modules/Blog/`)
   - Blog functionality
   - Routes: api.php, web.php
   - Config, database, resources

3. **TaxModule** (`Modules/TaxModule/`)
   - Tax calculation and reporting
   - Routes: api.php, web.php, vendor.php
   - Includes tests directory

### Module Status

**File:** `modules_statuses.json`

---

## Configuration Reference

### Core Configuration

**File:** `config/app.php`
```php
'name' => 'Laravel'
'timezone' => 'Asia/Dhaka'
'locale' => 'en'
'debug' => env('APP_DEBUG', true)
```

### Database & Cache

**File:** `config/database.php`
- **Default:** MySQL
- **Cache:** Redis
- **Session:** File (60min lifetime)

### Queue

**File:** `config/queue.php`
- **Default:** sync
- **Available:** Redis

### Broadcasting

**File:** `config/broadcasting.php`, `config/reverb.php`
- **Driver:** Laravel Reverb (WebSocket)

### CORS

**File:** `config/cors.php`
- **Paths:** api/*, sanctum/csrf-cookie
- **Allowed Origins:** * (permissive)
- **Methods:** All

---

## Development Guidelines

### Coding Standards

**PHP:**
- Use curly braces for all control structures
- PHP 8 constructor property promotion
- Explicit return type declarations
- PHPDoc blocks over inline comments
- Enums: TitleCase keys

**Laravel Conventions:**
- Use `php artisan make:` commands
- Form Requests for validation
- Eloquent relationships over raw queries
- Eager loading to prevent N+1
- Environment variables only in config files

### Testing

**Framework:** PHPUnit v11

```bash
# Run all tests
php artisan test --compact

# Run specific test
php artisan test --filter=TestName

# Run test file
php artisan test --compact tests/Feature/ExampleTest.php
```

**Test Creation:**
```bash
# Feature test
php artisan make:test FeatureNameTest

# Unit test
php artisan make:test UnitNameTest --unit
```

### Code Formatting

```bash
# Format PHP files
vendor/bin/pint --dirty --format agent
```

### Database Migrations

**Important:** When modifying columns, include ALL previous attributes or they will be lost.

```bash
php artisan make:migration add_column_to_table
```

### Models

**Casts:** Use `casts()` method rather than `$casts` property (Laravel 12 convention)

```php
protected function casts(): array
{
    return [
        'status' => 'boolean',
        'data' => 'array',
    ];
}
```

### Scheduled Tasks

**File:** `routes/console.php`

```php
// Daily at 03:00 - Mark expired digital codes
Schedule::command(MarkExpiredDigitalCodesCommand::class)->dailyAt('03:00');

// Every 15 minutes - Sync supplier stock
Schedule::job(new SupplierStockSyncJob)->everyFifteenMinutes();

// Every 5 minutes - Supplier health check
Schedule::job(new SupplierHealthCheckJob)->everyFiveMinutes();

// Hourly - Auto-release escrows
Schedule::job(new AutoReleaseEscrowJob)->hourly();
```

### Queued Jobs

**Location:** `app/Jobs/`

**Key Jobs:**
- `AutoReleaseEscrowJob` - Auto-release escrow payments
- `ProcessDigitalCodeImportJob` - Process digital code imports
- `ReleasePartnerEscrowJob` - Release partner escrow
- `SendEmailJob` - Queue email sending
- `SupplierCodeFetchJob` - Fetch supplier codes
- `SupplierHealthCheckJob` - Monitor supplier health
- `SupplierOrderPollJob` - Poll supplier orders
- `SupplierStockSyncJob` - Sync supplier stock
- `SupplierWebhookProcessJob` - Process supplier webhooks
- `SyncDenominationsJob` - Sync denominations
- `SyncSupplierCatalogJob` - Sync supplier catalogs

**Run Worker:**
```bash
php artisan queue:work --queue=default,order,email
```

### Events & Listeners

**Events:** 28 classes  
**Listeners:** 26 classes

**Key Events:**
- AddFundToWalletEvent, CashCollectEvent
- CustomerRegistrationEvent, CustomerRegisteredViaReferralEvent
- OrderPlacedEvent, OrderStatusEvent, OrderEditEvent
- ProductRequestStatusUpdateEvent, RefundEvent
- VendorRegistrationEvent, WithdrawStatusUpdateEvent

### Helper Files

**Autoloaded Files (25):**

```
app/Library/
├── Constant.php         # Country codes, telephone codes, constants
└── Responses.php        # API response helpers

app/Utils/
├── Helpers.php          # General helpers
├── BackEndHelper.php    # Backend utilities
├── BrandManager.php     # Brand operations
├── CategoryManager.php  # Category operations
├── CartManager.php      # Cart management
├── Convert.php          # Conversion utilities
├── CustomerManager.php  # Customer operations
├── FileManagerLogic.php # File management
├── ImageManager.php     # Image processing
├── OrderManager.php     # Order management
├── ProductManager.php   # Product management
├── SMSModule.php        # SMS gateway
├── constant.php         # Additional constants
├── currency.php         # Currency helpers
├── file_path.php        # File path helpers
├── language.php         # Language helpers
├── module-helper.php    # Module helpers
├── order.php            # Order helpers
├── product.php          # Product helpers
├── settings.php         # Settings helpers
├── panel-helpers.php    # Panel helpers
├── theme-helpers.php    # Theme helpers
└── vendor-helpers.php   # Vendor helpers
```

### Key Constants

**File:** `app/Library/Constant.php`

```php
SOFTWARE_VERSION = '16.1'
SOFTWARE_ID = 'MzE0NDg1OTc='

COUNTRIES = [...] // 249 countries
TELEPHONE_CODES = [...] // International dialing codes
THEME_RATIO = [...] // Image dimension guidelines
```

### Environment Variables

**Key Variables:**
```env
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=6valley
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# AWS S3
AWS_ENDPOINT=
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=

# Pusher/Reverb
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1

# SMS
NEXMO_KEY=
NEXMO_SECRET=

# License
PURCHASE_CODE=
BUYER_USERNAME=

# OpenAI
OPENAI_API_KEY=
OPENAI_ORGANIZATION=

# Docker (Sail)
NGINX_PORT=8302
FORCE_HTTPS=true
CONTAINER_NAME_PREFIX=6valley-demo
```

### Frontend Build

**Package Manager:** NPM  
**Bundler:** Laravel Mix v5.0.1 (Webpack)  
**Framework:** Vue.js v2.5.17 (Options API)  
**CSS Framework:** Bootstrap v4.0.0  
**Preprocessor:** Sass

```bash
# Development
npm run dev

# Watch mode
npm run watch

# Hot reload
npm run hot

# Production build
npm run prod
```

### Themes

**Location:** `resources/themes/`

- **default:** Default theme
- **theme_aster:** Alternative theme

**Image Ratios:**
- Product Image: 1:1 (500x500px)
- Category Image: 1:1 (500x500px)
- Brand Image: 1:1 (500x500px)
- Main Banner: 3:1
- Footer Banner: 2:1
- Popup Banner: 1:1 (1200x1200px)

---

## Important Notes

### Database Commission Calculation

```php
// Use CommissionService for accurate commission calculation
$commission = app(\App\Services\CommissionService::class)->calculate(
    $sellerIs,    // 'admin' or 'seller'
    $sellerId,    // Seller ID
    $orderTotal   // Order total amount
);
```

### Currency Handling

```php
// Load currency settings
Helpers::currency_load();

// Get currency code
$code = Helpers::currency_code();

// Convert to USD
$usdAmount = Helpers::convert_currency_to_usd($amount);
```

### Module Permission Check

```php
// Check if admin has module access
if (Helpers::module_permission_check('module_name')) {
    // Authorized
}
```

### Firebase Push Notifications

```php
// Send push notification
Helpers::send_push_notif_to_device($fcm_token, [
    'title' => 'Notification Title',
    'description' => 'Message body',
    'image' => 'https://...',
    'order_id' => $orderId,
    'type' => 'order_status',
]);
```

### Product Data Formatting

```php
// Format product data for frontend
$formatted = Helpers::product_data_formatting($product, $multi_data = false);

// For JSON data
$formatted = Helpers::product_data_formatting_for_json_data($product);
```

---

## Common Issues & Solutions

### Vite Manifest Error

**Error:** `Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest`

**Solution:**
```bash
npm run build
# OR ask user to run:
npm run dev
# OR
composer run dev
```

### Passport Keys Missing

**Solution:**
```bash
php artisan passport:install
```

### Cache Issues

**Solution:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Module Not Found

**Solution:**
```bash
php artisan module:status
php artisan module:enable ModuleName
```

---

## Testing Strategy

### Test Organization

- **Feature Tests:** Business logic, HTTP requests, integrations
- **Unit Tests:** Isolated class methods, utilities

### Test Conventions

- Use model factories for test data
- Check for custom factory states before manual setup
- Use `fake()` or `$this->faker` for fake data
- Cover happy paths, failure paths, edge cases

### Running Tests

```bash
# All tests
php artisan test --compact

# Filter by name
php artisan test --filter=UserRegistrationTest

# Specific file
php artisan test --compact tests/Feature/AuthTest.php
```

---

## Deployment Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate app key: `php artisan key:generate`
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Cache config: `php artisan config:cache`
- [ ] Cache routes: `php artisan route:cache`
- [ ] Cache views: `php artisan view:cache`
- [ ] Build assets: `npm run prod`
- [ ] Set up queue worker
- [ ] Configure scheduler (cron)
- [ ] Set up Passport keys
- [ ] Configure environment variables
- [ ] Set file permissions
- [ ] Enable HTTPS

---

## Support & Resources

### Documentation

- Laravel 12 Docs: https://laravel.com/docs/12.x
- Laravel Passport: https://laravel.com/docs/12.x/passport
- Laravel Sanctum: https://laravel.com/docs/12.x/sanctum
- Laravel Modules: https://nwidart.com/laravel-modules/

### Internal Helpers

- `app/Utils/Helpers.php` - General utilities
- `app/Library/Constant.php` - Constants and configurations
- `app/Utils/*.php` - Domain-specific helpers

### Key Services

- `App\Services\CommissionService` - Commission calculations
- `App\Services\PaymentService` - Payment processing
- `App\Services\OrderService` - Order management
- `App\Services\ProductService` - Product operations

---

**End of Project Reference**
