<?php

use Illuminate\Support\Facades\Route;

// AUTH
use App\Http\Controllers\API\V1\AuthController;

// USER
use App\Http\Controllers\API\V1\ProductController;
use App\Http\Controllers\API\V1\CategoryController;
use App\Http\Controllers\API\V1\CartController;
use App\Http\Controllers\API\V1\OrderController;
use App\Http\Controllers\API\V1\PaymentController;
use App\Http\Controllers\API\V1\ProductVariantController;
use App\Http\Controllers\API\V1\PromoController;
use App\Http\Controllers\API\V1\ReviewController;
use App\Http\Controllers\API\V1\WishlistController;
use App\Http\Controllers\API\V1\BlogController;
use App\Http\Controllers\API\V1\ContactMessageController;
use App\Http\Controllers\API\V1\SiteSettingController;
use App\Http\Controllers\API\V1\ShippingController;
use App\Http\Controllers\API\V1\ChatController;
use App\Http\Controllers\API\V1\AddressController;
use App\Http\Controllers\API\V1\MidtransWebhookController;
use App\Http\Controllers\API\V1\StoreStatsController;

// ADMIN
use App\Http\Controllers\API\V1\Admin\DashboardController;
use App\Http\Controllers\API\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\API\V1\Admin\ProductController as AdminProductController;
use App\Http\Controllers\API\V1\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\API\V1\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\API\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\API\V1\Admin\SiteSettingController as AdminSiteSettingController;
use App\Http\Controllers\API\V1\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\API\V1\Admin\ChatController as AdminChatController;
use App\Http\Controllers\API\V1\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\API\V1\Admin\PromoController as AdminPromoController;
use App\Http\Controllers\API\V1\Admin\ContactMessageController as AdminContactMessageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | PUBLIC
    |--------------------------------------------------------------------------
    */

    Route::prefix('auth')->group(function () {
        Route::post('/send-register-code', [AuthController::class, 'sendRegisterCode']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/email/verification-notification', [AuthController::class, 'resendVerification']);
    });

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('promos', [PromoController::class, 'index']);
    Route::get('variants/{id}', [ProductVariantController::class, 'show']);
    Route::get('blogs', [BlogController::class, 'index']);
    Route::get('blogs/{id}', [BlogController::class, 'show']);
    Route::get('settings', [SiteSettingController::class, 'show']);
    Route::get('store-stats', StoreStatsController::class);
    Route::post('contact', [ContactMessageController::class, 'store']);
    Route::post('payment/midtrans/notification', MidtransWebhookController::class);

    /*
    |--------------------------------------------------------------------------
    | USER
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index']);
            Route::post('/add', [CartController::class, 'add']);
            Route::put('/update/{id}', [CartController::class, 'update']);
            Route::delete('/remove/{id}', [CartController::class, 'remove']);
        });

        Route::post('checkout', [OrderController::class, 'checkout']);
        Route::post('promos/validate', [PromoController::class, 'validateCode']);
        Route::get('addresses', [AddressController::class, 'index']);
        Route::post('addresses', [AddressController::class, 'store']);
        Route::delete('addresses/{address}', [AddressController::class, 'destroy']);
        Route::get('orders', [OrderController::class, 'index']);
        Route::get('orders/{id}', [OrderController::class, 'show']);
        Route::patch('orders/{id}/cancel', [OrderController::class, 'cancel']);
        Route::patch('orders/{id}/complete', [OrderController::class, 'complete']);

        Route::post('payment/{orderId}', [PaymentController::class, 'upload']);
        Route::post('reviews', [ReviewController::class, 'store']);
        Route::get('chat/unread-count', [ChatController::class, 'unreadCount']);
        Route::get('chat', [ChatController::class, 'show']);
        Route::post('chat/messages', [ChatController::class, 'storeMessage']);
        Route::post('wishlist/toggle', [WishlistController::class, 'toggle']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth:sanctum', 'admin', 'throttle:60,1'])
        ->prefix('admin')
        ->group(function () {
            Route::get('dashboard', [DashboardController::class, 'index']);
            Route::get('notifications', [AdminNotificationController::class, 'index']);
            Route::apiResource('categories', AdminCategoryController::class);
            Route::patch('variants/{variant}/stock', [AdminProductController::class, 'updateVariantStock']);
            Route::apiResource('products', AdminProductController::class);
            Route::apiResource('promos', AdminPromoController::class)->except(['show']);
            Route::get('contact-messages', [AdminContactMessageController::class, 'index']);
            Route::delete('contact-messages/{contactMessage}', [AdminContactMessageController::class, 'destroy']);
            Route::post('orders/bulk-ship', [AdminOrderController::class, 'bulkShip']);
            Route::apiResource('orders', AdminOrderController::class)->except(['store']);
            Route::apiResource('users', AdminUserController::class);
            Route::apiResource('blogs', AdminBlogController::class);
            Route::get('reviews', [AdminReviewController::class, 'index']);
            Route::patch('reviews/{review}/reply', [AdminReviewController::class, 'reply']);
            Route::get('chats', [AdminChatController::class, 'index']);
            Route::post('chats/start', [AdminChatController::class, 'startMessage']);
            Route::get('chats/{chat}', [AdminChatController::class, 'show']);
            Route::post('chats/{chat}/messages', [AdminChatController::class, 'storeMessage']);
            Route::get('settings', [AdminSiteSettingController::class, 'show']);
            Route::put('settings', [AdminSiteSettingController::class, 'update']);
        });
});
