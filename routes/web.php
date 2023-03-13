<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use \Statickidz\GoogleTranslate;


Auth::routes();
Route::get('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout']);

// Privacy policy
Route::get('/privacy-policy', function() {
	return view('privacy-policy');
})->name('privacy.policy');

// Get label
Route::get('/archive/{dir}/{file}', function() {
	if (!Request::segment(2) && !Request::segment(3))
		abort(404);
	$url = 'archive/#'. Request::segment(2) .'/'. Request::segment(3);
	return response()->file(public_path($url));
})->name('get.pdf');

// Home page
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['middleware' => 'checkPermission'], function () {
	Route::group(['middleware' => 'checkPlan'], function () {
		// Orders
		Route::get('/orders/{type}/{where?}', [App\Http\Controllers\OrdersController::class, 'index'])->name('orders');
		Route::get('/ajaxorders/{type}/{where?}', [App\Http\Controllers\OrdersController::class, 'ajaxorders'])->name('ajaxorders');	

		// SKU
		Route::get('/sku', [App\Http\Controllers\SkuController::class, 'index'])->name('sku');

		// Reports
		Route::get('/report/{type}', [App\Http\Controllers\ReportController::class, 'reports'])->name('reports');
		Route::any('/get-report', [App\Http\Controllers\ReportController::class, 'index'])->name('get.reports');

		// Downloads
		Route::any('/downloads', [App\Http\Controllers\DownloadsController::class, 'index'])->name('get.downloads');

		// Search
		Route::any('/search', [App\Http\Controllers\SearchController::class, 'index'])->name('search');
		Route::get('/ajaxsearch', [App\Http\Controllers\SearchController::class, 'ajaxsearch'])->name('ajaxsearch');

		// Ajax
		Route::post('/move-to', 			[App\Http\Controllers\AjaxController::class, 'moveTo'])->name('move.to');
		Route::post('/remove-order', 		[App\Http\Controllers\AjaxController::class, 'removeOrder'])->name('remove.order');
		Route::post('/download', 			[App\Http\Controllers\AjaxController::class, 'downloadOrders'])->name('download.orders');
		Route::post('/call-dhl', 			[App\Http\Controllers\AjaxController::class, 'callDHL'])->name('call.dhl');
		Route::post('/get-order-data', 	[App\Http\Controllers\AjaxController::class, 'getOrderData'])->name('get.order.data');
		Route::post('/save-order-data', 	[App\Http\Controllers\AjaxController::class, 'saveOrderData'])->name('save.order.data');
		Route::get('/get-report', 			[App\Http\Controllers\AjaxController::class, 'getReportData'])->name('get.report');
		Route::post('/validate-address', [App\Http\Controllers\AjaxController::class, 'validateAddress'])->name('validate.address');
		Route::post('/get-hold-image-data', [App\Http\Controllers\AjaxController::class, 'getHoldImageData'])->name('get.image.data');
		Route::post('/get-product-image-data', [App\Http\Controllers\AjaxController::class, 'getProductImageData'])->name('get.product.data');
		Route::post('/upload-hold-image', [App\Http\Controllers\AjaxController::class, 'uploadImage'])->name('upload.image');
		Route::post('/get-sku-data', 		[App\Http\Controllers\AjaxController::class, 'getSkuData'])->name('get.sku.data');
		Route::post('/save-sku', 			[App\Http\Controllers\AjaxController::class, 'saveSku'])->name('getsave.sku');
		Route::post('/delete-sku', 		[App\Http\Controllers\AjaxController::class, 'deleteSku'])->name('delete.sku');
		Route::post('/save-product-name', [App\Http\Controllers\AjaxController::class, 'saveProductName'])->name('save.product.name');
		Route::post('/upload-product-image', [App\Http\Controllers\AjaxController::class, 'uploadProductImage'])->name('upload.product.image');
		Route::post('/clear-Dhl-error', 	[App\Http\Controllers\AjaxController::class, 'clearDhlError'])->name('clear.dhl.error');
		Route::get('/generate-invoice', 	[App\Http\Controllers\AjaxController::class, 'generateInvoice'])->name('generate.invoice');
		Route::post('/toggle-user-status', [App\Http\Controllers\AjaxController::class, 'toggleUserStatus'])->name('toggle.user.status');
		Route::post('/track-package', 	[App\Http\Controllers\AjaxController::class, 'trackPackage'])->name('track.package');
		Route::post('/get-order-details', [App\Http\Controllers\AjaxController::class, 'getOrderDetails'])->name('get.order.details');
		Route::post('/get-replace-image', [App\Http\Controllers\AjaxController::class, 'getReplaceImage'])->name('get.replace.image');
		Route::post('/upload-replace-image', [App\Http\Controllers\AjaxController::class, 'uploadReplaceImage'])->name('upload.replace.image');
		Route::get('/get-shop-name', 		[App\Http\Controllers\AjaxController::class, 'getShopName'])->name('get.shop.name');
		Route::post('/save-shop-name', 	[App\Http\Controllers\AjaxController::class, 'saveShopName'])->name('save.shop.name');
		Route::post('/delete-order', 	[App\Http\Controllers\AjaxController::class, 'deleteOrder'])->name('delete.order');
		Route::post('/send-fix-request', [App\Http\Controllers\AjaxController::class, 'fixOrder'])->name('fix.order');

		//Home simpleUpload
		Route::get('/simpleupload', [App\Http\Controllers\HomeController::class, 'simpleUpload']);
		Route::post('/processupload', [App\Http\Controllers\HomeController::class, 'processUpload'])->name('processupload');
		Route::get('/shipment-archive', [App\Http\Controllers\HomeController::class, 'shipmentArchive'])->name('shipment.archive');
		Route::get('/downloadExcel/{type}', [App\Http\Controllers\HomeController::class, 'downloadExcel'])->name('download.excel');
		Route::post('/pay-printfee', [App\Http\Controllers\HomeController::class, 'payPrintfee'])->name('pay.printfee');
		
		Route::get('/', [App\Http\Controllers\DashboardController::class, 'getData'])->name('dashboard');
		Route::get('/ajax-dashboard', [App\Http\Controllers\DashboardController::class, 'ajaxGetData']);
		
	});
	Route::get('/app-fees', [App\Http\Controllers\HomeController::class, 'appFees'])->name('app.fees');
	Route::get('/validate-transaction/{cid}', [App\Http\Controllers\HomeController::class, 'validateTransaction'])->name('validate.transaction');
	Route::get('/validate-ps-transaction/{transId}', [App\Http\Controllers\HomeController::class, 'validatePsTransaction'])->name('validate.ps.transaction');
	Route::get('/product-catalogue', [App\Http\Controllers\HomeController::class, 'bestsubProducts'])->name('beststub.products');
	
	//User
	Route::get('/profile', [App\Http\Controllers\UserController::class, 'index'])->name('profile');
	Route::post('/change-password', [App\Http\Controllers\UserController::class, 'store'])->name('update.password');
	Route::post('/set-plan', [App\Http\Controllers\UserController::class, 'setPlan'])->name('set.plan');

	//Paypal
	Route::get('create-transaction',  [App\Http\Controllers\PayPalController::class, 'createTransaction'])->name('createTransaction');
	Route::get('process-transaction', [App\Http\Controllers\PayPalController::class, 'processTransaction'])->name('processTransaction');
	Route::get('success-transaction', [App\Http\Controllers\PayPalController::class, 'successTransaction'])->name('successTransaction');
	Route::get('cancel-transaction',  [App\Http\Controllers\PayPalController::class, 'cancelTransaction'])->name('cancelTransaction');
});

Route::group(['middleware' => ['checkPermission', 'checkRole']], function () {
	//Users
	Route::get('/users', [App\Http\Controllers\UsersController::class, 'listUsers'])->name('users');
	Route::get('/get-sku-prices', [App\Http\Controllers\AjaxController::class, 'getSkuPrices'])->name('get.sku.prices');
	Route::post('/update-custom-sku', [App\Http\Controllers\AjaxController::class, 'updateCustomSku'])->name('update.custom.sku');
	Route::post('/delete-custom-sku', [App\Http\Controllers\AjaxController::class, 'deleteCustomSku'])->name('delete.custom.sku');	
	Route::post('/toggle-custom-sku', [App\Http\Controllers\AjaxController::class, 'toggleCustomSku'])->name('toggle.custom.sku');	
});
