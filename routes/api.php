<?php

use App\Http\Controllers\Api\AttributeController;
use App\Http\Controllers\Api\AttributeValueController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BasketController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\api\HomeController;
use App\Http\Controllers\Api\LandingController;
use App\Http\Controllers\Api\OptionController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SliderController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WarantyController;
use App\Http\Controllers\Api\VarietyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->name('register');
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::post('me', [AuthController::class, 'me'])->name('me');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });
    Route::prefix('category')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('category.index');
        Route::post('create', [CategoryController::class, 'create'])->name('category.create');
        Route::put('update/{category}', [CategoryController::class, 'update'])->name('category.update');
        Route::delete('delete/{category}', [CategoryController::class, 'delete'])->name('category.delete');
        Route::get('/{category}', [CategoryController::class, 'single'])->name('category.single');
        Route::get('/{category}/products', [CategoryController::class, 'products'])->name('category.products');
    });
    Route::prefix('brand')->group(function () {
        Route::get('/', [BrandController::class, 'index'])->name('brand.index');
        Route::post('create', [BrandController::class, 'create'])->name('brand.create');
        Route::put('update/{brand}', [BrandController::class, 'update'])->name('brand.update');
        Route::delete('delete/{brand}', [BrandController::class, 'delete'])->name('brand.delete');
        Route::get('/{brand}', [BrandController::class, 'single'])->name('brand.single');
        Route::get('/{brand}/products', [BrandController::class, 'products'])->name('brand.products');
    });
    Route::prefix('option')->group(function () {
        Route::get('/', [OptionController::class, 'index'])->name('option.index');
        Route::get('/optionValue/{option}', [OptionController::class, 'optionValue'])->name('option.OptionValue');
        Route::post('create', [OptionController::class, 'create'])->name('option.create');
        Route::put('update/{option}', [OptionController::class, 'update'])->name('option.update');
        Route::delete('delete/{option}', [OptionController::class, 'delete'])->name('option.delete');
    });
    Route::prefix('product')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('product.index');
        Route::post('create', [ProductController::class, 'create'])->name('product.create');
        Route::post('update/{product}', [ProductController::class, 'update'])->name('product.update');
        Route::delete('delete/{product}', [ProductController::class, 'delete'])->name('product.delete');
        Route::get('/{product}', [ProductController::class, 'single'])->name('product.single');
        Route::get('{product}/relateds', [ProductController::class, 'relateds'])->name('product.relateds');
        
        Route::post('{product}/image/create',[ProductController::class, 'imageCreate'])->name('product.image.create');
        Route::get('{product}/image/',[ProductController::class, 'productImages'])->name('product.image.list');
        // TODO bad code
        Route::delete('/image/delete/{image}',[ProductController::class, 'imageDelete'])->name('product.image.delete'); 

        Route::post('/{product}/variety/create',  [VarietyController::class, 'create'])->name('variety.create');
        Route::put('/variety/update/{variety}',  [VarietyController::class, 'update'])->name('variety.update');
        Route::delete('/variety/delete/{variety}',  [VarietyController::class, 'delete'])->name('variety.delete');
        Route::get('/{product}/variety/',  [VarietyController::class, 'index'])->name('variety.index');
    });
    Route::prefix('basket')->group(function () {

        Route::get('add/{product}', [BasketController::class, 'add'])->name('basket.add');
        Route::get('clear/', [BasketController::class, 'clear']);
        Route::get('/', [BasketController::class, 'index'])->name('basket.index');
        Route::put('update/{product}', [BasketController::class, 'update'])->name('basket.update');
        Route::get('checkout', [BasketController::class, 'checkoutForm'])->name('basket.checkout.form');
        Route::post('checkout', [BasketController::class, 'checkout'])->name('basket.checkout');

        Route::get('cost/check', [BasketController::class, 'checkCost'])->name('basket.cost.check');

    });
    Route::prefix('waranty')->group(function () {
        Route::get('/', [WarantyController::class, 'index'])->name('waranty.index');
        Route::post('create', [WarantyController::class, 'create'])->name('waranty.create');
        Route::put('update/{waranty}', [WarantyController::class, 'update'])->name('waranty.update');
        Route::delete('delete/{waranty}', [WarantyController::class, 'delete'])->name('waranty.delete');
        Route::get('/{waranty}', [WarantyController::class, 'single'])->name('waranty.single');
    });
    Route::prefix('optionValue')->group(function () {
        Route::get('/', [ColorController::class, 'index'])->name('option.value.index');
        Route::post('create', [ColorController::class, 'create'])->name('option.value.create');
        Route::put('update/{optionValue}', [ColorController::class, 'update'])->name('option.value.update');
        Route::delete('delete/{optionValue}', [ColorController::class, 'delete'])->name('option.value.delete');
        Route::get('/{optionValue}', [ColorController::class, 'single'])->name('option.value.single'); // TODO th not useful
    });
    Route::prefix('currency')->group(function () {
        Route::get('/', [CurrencyController::class, 'index'])->name('currency.index');
        Route::post('create', [CurrencyController::class, 'create'])->name('currency.create');
        Route::put('update/{currency}', [CurrencyController::class, 'update'])->name('currency.update');
        Route::delete('delete/{currency}', [CurrencyController::class, 'delete'])->name('currency.delete');
        Route::get('/{currency}', [CurrencyController::class, 'single'])->name('currency.single');
    });
    Route::get('search/', [HomeController::class, 'search'])->name('index.search');


    Route::prefix('landing')->group(function () {
        Route::get('/', [LandingController::class, 'index'])->name('landing.index');
        Route::post('create', [LandingController::class, 'create'])->name('landing.create');
    });
    Route::prefix('attribute')->group(function () {
        Route::get('/', [AttributeController::class, 'index'])->name('attribute.index');
        Route::post('create', [AttributeController::class, 'create'])->name('attribute.create');
        Route::get('category/{category}', [AttributeController::class, 'category'])->name('attribute.category');
        Route::put('update/{attribute}', [AttributeController::class, 'update'])->name('attribute.update');
        Route::delete('delete/{attribute}', [AttributeController::class, 'delete'])->name('attribute.delete');
    });
    Route::prefix('attributeValue')->group(function () {
        Route::get('/', [AttributeValueController::class, 'index'])->name('attribute.value.index');
        Route::get('/product/{product}', [AttributeValueController::class, 'product'])->name('attribute.value.product');
        Route::post('create', [AttributeValueController::class, 'create'])->name('attribute.value.create');
        Route::put('update/{attributeValue}', [AttributeValueController::class, 'update'])->name('attribute.value.update');
        Route::delete('delete/{attributeValue}', [AttributeValueController::class, 'delete'])->name('attribute.value.delete');
    });


    Route::prefix('slider')->group(function () {
        Route::get('/', [SliderController::class, 'index'])->name('slider.index');
        Route::post('create', [SliderController::class, 'create'])->name('slider.create');
        Route::get('type/{type}', [SliderController::class, 'type'])->name('slider.type');
        Route::post('update/{slider}', [SliderController::class, 'update'])->name('slider.update');
        Route::delete('delete/{slider}', [SliderController::class, 'delete'])->name('slider.delete');
    });
    Route::prefix('address')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('address.index');
        Route::post('create', [UserController::class, 'create'])->name('address.create');
        Route::get('/{address}', [UserController::class, 'single'])->name('address.single');
        Route::post('update/{address}', [UserController::class, 'update'])->name('address.update');
        Route::delete('delete/{address}', [UserController::class, 'delete'])->name('address.delete');
    });
    Route::get('province/', [UserController::class, 'province'])->name('address.province');
    Route::get('city/{province}', [UserController::class, 'city'])->name('address.city');
    Route::post('payment/{gateway}/callback', [PaymentController::class, 'verify'])->name('payment.verify');
    Route::get('payment/{gateway}/callback', [PaymentController::class, 'verify'])->name('payment.verify.get');


    Route::post('orders/', [OrderController::class, 'index'])->name('order.index');
    Route::post('order/{order}/change/status/', [OrderController::class, 'changeStatus'])->name('order.change.status');
    Route::post('order/{order}/change/trackingCode/', [OrderController::class, 'changeTrackingCode'])->name('order.change.tracking.code');
    // Route::put('update/{Order}', [OrderController::class, 'update'])->name('order.update');
    Route::post('{user}/orders', [OrderController::class, 'user'])->name('order.user');
});

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
