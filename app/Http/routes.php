<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::auth();

// Route::get('/home', 'HomeController@index');

use App\Http\Controllers\UserController;

Route::get('login', 'Auth\\AuthController@showLoginForm')->name('login');
Route::post('login', 'Auth\\AuthController@login');
Route::post('logout', 'Auth\\AuthController@logout')->name('logout');
Route::get('register', 'Auth\\AuthController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\\AuthController@register');
Route::get('password/reset', 'Auth\\PasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\\PasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\\PasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\\PasswordController@reset')->name('password.update');
Route::get('/', function () {
	return view('auth.login');
});
Route::get('/', function () {
	if( Auth::check() ){
		return redirect(url('setlocation'));
	}

	    return view('auth.login');
});

// Route::get('logout', 'Auth\AuthController@getLogout');

// Retrieve a piece of data from the session...


// if((session('location_id')) && (session('location_id') == 0))
// {
// 	return redirect()->action('SetlocationController@index');
// }

// echo session('location_id');
// die();


// Route::get('setlocation', function () {

//     // Store a piece of data in the session...
//     session(['location_id' => 2, 'location' => 2]);



//     // print_r(session()->all());
//     // die();

//     return view('setlocations.index');
//     // return redirect()->action('SetlocationController@create');
// });



//	dd(Auth::check());
// Route::get('/dashboard', function () {
//
// 	if( Auth::check() ){
// 		return view('home');
// 	}
//
// 	return view('auth.login');
// });
Route::group(['middleware' => ['auth']], function () {
Route::get('/dashboard', 'MenuController@dashboard');
Route::post('uploads/daily-entry-pdf', 'SalesReportPdfController@dailyEntryPdf');
Route::get('uploads/daily-entry-pdf/preview', 'SalesReportPdfController@dailyEntryPdfPreview')->name('sales-pdf.preview');
Route::get('uploads/daily-entry-pdf/file/{token}', 'SalesReportPdfController@dailyEntryPdfFile')->name('sales-pdf.file');
Route::post('uploads/daily-entry-pdf/import', 'SalesReportPdfController@dailyEntryPdfImport')->name('sales-pdf.import');
Route::post('uploads/daily-entry-pdf/cancel', 'SalesReportPdfController@dailyEntryPdfCancel')->name('sales-pdf.cancel');
Route::post('uploads/labor-report-excel', 'LaborReportExcelController@upload')->name('labor-excel.upload');
Route::get('uploads/labor-report-excel/preview', 'LaborReportExcelController@preview')->name('labor-excel.preview');
Route::post('uploads/labor-report-excel/import', 'LaborReportExcelController@import')->name('labor-excel.import');
Route::post('uploads/labor-report-excel/cancel', 'LaborReportExcelController@cancel')->name('labor-excel.cancel');
Route::get('setlocation', 'SetlocationController@index')->name('setlocation');
Route::resource('setlocation', 'SetlocationController');
// Route::resource('user', 'UserController');

Route::resource('user', UserController::class);
Route::resource('vendor', 'VendorController');
Route::resource('department', 'DepartmentController');
Route::resource('employee', 'EmployeeController');
Route::resource('payment', 'PaymentController');
Route::resource('subtraction', 'SubtractionController');
Route::resource('seller', 'SellerController');
Route::resource('product', 'ProductController');
Route::resource('category', 'CategoryController');
Route::resource('order', 'OrderController');
Route::resource('location', 'LocationController');
Route::resource('report', 'Customer_problemController');
Route::resource('service', 'Customer_serviceController');
Route::resource('menu', 'MenuController');
Route::resource('reimbursement', 'ReimbursementController');
Route::resource('sale', 'SaleController');
Route::resource('discount', 'DiscountController');
Route::resource('adjustment', 'AdjustmentController');
Route::resource('receipt', 'ReceiptController');
Route::resource('salesection', 'SalesectionController');
Route::resource('laboursection', 'LaboursectionController');
Route::resource('salessetting', 'SaleSettingController');
Route::resource('payrollsetting', 'PayrollSettingController');
Route::resource('reportingsection', 'ReportingsectionController');

/* Route::post('reportingsection', 'ReportingsectionController@index');
 */

//Route::get('reportingsection', 'ReportingsectionController@create');
//Route::resource('reportingsection', 'ReportingsectionController', ['except' => ['store']]);

// Route::get('salespage/test_data', 'SalesectionController@testindex');
Route::get('salesection/filter/data/{count?}', ['as' => 'salesection.filter.data', 'uses' => 'SalesectionController@filterdata']);



Route::get('products/{id?}', 'ProductController@index');

Route::post('reimbursement/filter', ['as' => 'reimbursement/filter', 'uses' => 'ReimbursementController@index']);
Route::get('reimbursement/filter', ['as' => 'reimbursement/filter', 'uses' => 'ReimbursementController@index']);
Route::get('history', ['as' => 'history', 'uses' => 'LaboursectionController@history']);
Route::get('vArchive/{id}', ['as' => 'seller.vArchive', 'uses' => 'SellerController@vArchive']);

Route::get('catArchive/{id}', ['as' => 'category.catArchive', 'uses' => 'CategoryController@catArchive']);

Route::get('proArchive/{id}', ['as' => 'product.proArchive', 'uses' => 'ProductController@proArchive']);
Route::post('update-product-price', ['as' => 'update-product-price', 'uses' => 'ProductController@updateProductPrice']);

Route::get('archive/{id}', ['as' => 'location.archive', 'uses' => 'LocationController@archive']);
Route::get('userarchive/{id}', ['as' => 'user.archive', 'uses' => 'UserController@userarchive']);
Route::get('departmentarchive/{id}', ['as' => 'department.depArchive', 'uses' => 'DepartmentController@depArchive']);
Route::get('employeearchive/{id}', ['as' => 'employee.empArchive', 'uses' => 'EmployeeController@empArchive']);
Route::get('paymentarchive/{id}', ['as' => 'payment.payArchive', 'uses' => 'PaymentController@payArchive']);
Route::get('subtractionarchive/{id}', ['as' => 'subtraction.subtArchive', 'uses' => 'SubtractionController@subtArchive']);

Route::any('asign_loaction/{id?}', ['as' => 'location.asign_loaction', 'uses' => 'LocationController@asign_loaction']);
Route::post('asign_loaction_save', ['as' => 'location.asign_loaction_save', 'uses' => 'LocationController@asign_loaction_save']);
Route::get('asign_loaction_status/{id}', ['as' => 'location.asign_loaction_status', 'uses' => 'LocationController@asign_loaction_status']);

Route::get('salearchive/{id}', ['as' => 'sale.archive', 'uses' => 'SaleController@salearchive']);
Route::get('discountarchive/{id}', ['as' => 'discount.archive', 'uses' => 'DiscountController@discountarchive']);
Route::get('adjustmentarchive/{id}', ['as' => 'adjustment.archive', 'uses' => 'AdjustmentController@adjustmentarchive']);
Route::get('receiptarchive/{id}', ['as' => 'receipt.archive', 'uses' => 'ReceiptController@receiptarchive']);
Route::get('resetlocationsale/{id}', ['as' => 'resetlocationsale', 'uses' => 'SaleController@removeSelectedLocation']);
Route::get('resetlocationdiscount/{id}', ['as' => 'resetlocationdiscount', 'uses' => 'DiscountController@removeSelectedLocation']);
Route::get('resetlocationadjustment/{id}', ['as' => 'resetlocationadjustment', 'uses' => 'AdjustmentController@removeSelectedLocation']);
Route::get('resetlocationreceipt/{id}', ['as' => 'resetlocationreceipt', 'uses' => 'ReceiptController@removeSelectedLocation']);

Route::get('resetlocation/{id}', ['as' => 'resetlocation', 'uses' => 'UserController@removeSelectedLocation']);

Route::post('showSeller', ['as' => 'showSeller', 'uses' => 'OrderController@showSeller']);
Route::post('searchOrder', ['as' => 'searchOrder', 'uses' => 'OrderController@searchOrder']);
Route::get('searchOrder', ['as' => 'searchOrder', 'uses' => 'OrderController@index']);
Route::post('changeSessionValue', ['as' => 'changeSessionValue', 'uses' => 'SetlocationController@changeSessionValue']);
Route::post('changeVendor', ['as' => 'changeVendor', 'uses' => 'OrderController@changeVendor']);
Route::post('addToCart', ['as' => 'addToCart', 'uses' => 'OrderController@addToCart']);
Route::post('changeCart', ['as' => 'changeCart', 'uses' => 'OrderController@changeCart']);
Route::post('showOrder', ['as' => 'showOrder', 'uses' => 'OrderController@showOrder']);
Route::get('showOrder', ['as' => 'showOrder', 'uses' => 'OrderController@showOrder']);
Route::post('saveOrder', ['as' => 'saveOrder', 'uses' => 'OrderController@saveOrder']);
Route::get('emptyCart', ['as' => 'emptyCart', 'uses' => 'OrderController@emptyCart']);
Route::post('sendOrderAgain', ['as' => 'sendOrderAgain', 'uses' => 'OrderController@sendOrderAgain']);
Route::get('showcompleteorder', ['as' => 'showcompleteorder', 'uses' => 'OrderController@saveOrder']);
Route::post('resendOrder', ['as' => 'resendOrder', 'uses' => 'OrderController@resendOrder']);
Route::post('showCategory', ['as' => 'showCategory', 'uses' => 'OrderController@showCategory']);


Route::post('removeProblemSub', ['as' => 'removeProblemSub', 'uses' => 'Customer_problemController@removeProblemSub']);
Route::post('searchGarments', ['as' => 'searchGarments', 'uses' => 'Customer_problemController@searchGarments']);
Route::post('searchReport', ['as' => 'searchReport', 'uses' => 'Customer_problemController@searchReport']);
Route::get('searchReport', ['as' => 'searchReport', 'uses' => 'Customer_problemController@index']);
Route::post('changeCartUpdate', ['as' => 'changeCartUpdate', 'uses' => 'OrderController@changeCartUpdate']);
Route::post('removeSub', ['as' => 'removeSub', 'uses' => 'ReimbursementController@removeSub']);
Route::post('reportData', ['as' => 'reportData', 'uses' => 'ReimbursementController@reportData']);
Route::post('reportDetails', ['as' => 'reportDetails', 'uses' => 'Customer_serviceController@reportDetails']);
Route::post('setCart', ['as' => 'setCart', 'uses' => 'OrderController@setCart']);
// Add More Problem Report
Route::post('add_more', ['as' => 'add_more', 'uses' => 'Customer_problemController@add_more']);
Route::post('addNew', ['as' => 'addNew', 'uses' => 'Customer_problemController@addNew']);
Route::get('/cprpdf/{id}','Customer_problemController@cprpdf');
Route::get('pdf/{pdf}', ['as' => 'service.pdf', 'uses' => 'Customer_serviceController@generate_pdf'] );
Route::get('/rembursementPdf/{id}','ReimbursementController@rembursementPdf');
Route::post('approved_amount', ['as' => 'approved_amount', 'uses' => 'ReimbursementController@approved_amount']);


Route::post('weekData', ['as' => 'weekData', 'uses' => 'SalesectionController@weekData']);
//Route::post('weekDataFetch', ['as' => 'weekDataFetch', 'uses' => 'SalesectionController@weekDataFetch']);
Route::post('dataSaveSales', ['as' => 'dataSaveSales', 'uses' => 'SalesectionController@dataSaveSales']);
Route::post('commentSave', ['as' => 'commentSave', 'uses' => 'SalesectionController@commentSave']);
Route::post('getComment', ['as' => 'getComment', 'uses' => 'SalesectionController@getComment']);
Route::post('newFieldSaveSales', ['as' => 'newFieldSaveSales', 'uses' => 'SalesectionController@newFieldSaveSales']);
Route::get('/salesection/{id}/printData', ['as' => 'salesection.printData', 'uses' => 'SalesectionController@printData']);
Route::get('/reportingsections/printReportData/{start_date}/{end_date}/{sortBy}/{sortBy1}/{location?}/{sale?}/{discount?}/{adjustment?}/{receipt?}', ['as' => 'reportingsections.printReportData', 'uses' => 'ReportingsectionController@printReportData']);
//Route::post('getValue', ['as' => 'getValue', 'uses' => 'SalesectionController@getValue']);

Route::resource('payrollcategory', 'PayrollCategoryController');
Route::get('resetlocationpayrollcat/{id}', ['as' => 'resetlocationpayrollcat', 'uses' => 'PayrollCategoryController@removeSelectedLocation']);
Route::get('payrollcategoryarchive/{id}', ['as' => 'payrollcategory.archive', 'uses' => 'PayrollCategoryController@payrollcategoryarchive']);

Route::resource('payrolldepartment', 'PayrollDepartmentController');
Route::get('resetlocationpayrolldep/{id}', ['as' => 'resetlocationpayrolldep', 'uses' => 'PayrollDepartmentController@removeSelectedLocation']);
Route::get('payrolldepartmentarchive/{id}', ['as' => 'payrolldepartment.archive', 'uses' => 'PayrollDepartmentController@payrolldepartmentarchive']);

Route::resource('payrollemployee', 'PayrollEmployeeController');
Route::get('resetlocationpayrollemp/{id}', ['as' => 'resetlocationpayrollemp', 'uses' => 'PayrollEmployeeController@removeSelectedLocation']);
Route::get('payrollemployeearchive/{id}', ['as' => 'payrollemployee.archive', 'uses' => 'PayrollEmployeeController@payrollemployeearchive']);

Route::post('viewAllData', ['as' => 'payrollemployee.viewAllData', 'uses' => 'PayrollEmployeeController@viewAllData']);

Route::post('create_date_range', ['as' => 'create_date_range', 'uses' => 'LaboursectionController@create_date_range']);

Route::post('getWeekRangeByDate', ['as' => 'getWeekRangeByDate', 'uses' => 'LaboursectionController@getWeekRangeByDate']);

Route::get('getWeekRange/{year}', ['as' => 'getWeekRange', 'uses' => 'LaboursectionController@getWeekRange']);
Route::post('weekNumber', ['as' => 'weekNumber', 'uses' => 'LaboursectionController@weekNumber']);
Route::post('dataSave', ['as' => 'dataSave', 'uses' => 'LaboursectionController@dataSave']);
Route::post('newFieldSave', ['as' => 'newFieldSave', 'uses' => 'LaboursectionController@newFieldSave']);
Route::get('hideEmployee', ['as' => 'hideEmployee', 'uses' => 'LaboursectionController@hideEmployee']);
Route::get('removeEmployee', ['as' => 'removeEmployee', 'uses' => 'LaboursectionController@removeEmployee']);
Route::post('editEmployee', ['as' => 'editEmployee', 'uses' => 'LaboursectionController@editEmployee']);
Route::post('updateEmp', ['as' => 'updateEmp', 'uses' => 'LaboursectionController@updateEmp']);
Route::get('labor_report/{id}', ['as' => 'labor_report', 'uses' => 'LaboursectionController@labor_report']);


Route::post('removeSelectedDepartment', ['as' => 'removeSelectedDepartment.submit', 'uses' => 'LaboursectionController@removeSelectedDepartment']);

Route::post('checkDataFrCode', ['as' => 'checkDataFrCode', 'uses' => 'OrderController@checkDataFrCode']);


Route::get('removeSelectedDepartment/{id}', ['as' => 'removeSelectedDepartment', 'uses' => 'PayrollEmployeeController@removeSelectedDepartment']);
Route::get('removeEmployeeLocation/{id}', ['as' => 'removeEmployeeLocation', 'uses' => 'PayrollEmployeeController@removeEmployeeLocation']);

Route::post('chartData', ['as' => 'chartData', 'uses' => 'ReportingsectionController@chartData']);
Route::post('viewReportingData', ['as' => 'viewReportingData', 'uses' => 'ReportingsectionController@viewReportingData']);
Route::post('linechartData', ['as' => 'linechartData', 'uses' => 'ReportingsectionController@linechartData']);
Route::post('linechartUnit', ['as' => 'linechartUnit', 'uses' => 'ReportingsectionController@linechartUnit']);
// Route::post('printDataCopy', ['as' => 'printData', 'uses' => 'ReportingsectionController@printDataCopy']);
// Route::get('printReportData/{start_date}/{end_date}/{sortBy}/{location?}/{sale?}/{discount?}/{adjustment?}/{receipt?}', ['as' => 'printReportData/start_date/end_date/sortBy/{{ url('/') }}/reportingsections/printReportData/"+strDate+"/"+endDate+"/"+sortBy+"/"+location+"/"+sale+"/"+discount+"/"+adjustment+"/"+receiptlocation/sale/discount/adjustment/receipt/', 'uses' => 'ReportingsectionController@printReportData']);


// Route::get('https://kvmgmt.com/reportingsectionsprintReportData/{start_date}/{end_date}/{sortBy}/{location?}/{sale?}/{discount?}/{adjustment?}/{receipt?}', ['as' => 'printReportData/start_date/end_date/sortBy/location/sale/discount/adjustment/receipt/',
 // 'uses' => 'ReportingsectionController@printReportData']);

Route::post('addAllToCart', ['as' => 'addAllToCart', 'uses' => 'OrderController@addAllToCart']);
Route::get('printReportData/{start_date}/{end_date}/{sortBy}/{sortBy1}/{location?}/{sale?}/{discount?}/{adjustment?}/{receipt?}', ['as' => 'printReportData/start_date/end_date/sortBy/sortBy1/location/sale/discount/adjustment/receipt/', 'uses' => 'ReportingsectionController@printReportData']);
});
?>
