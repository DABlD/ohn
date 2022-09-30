<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('forgotPassword', 'UserController@forgotPassword')->name('forgotPassword');
Route::get('resetPassword', 'UserController@resetPassword')->name('resetPassword');

Route::group([
        'middleware' => 'auth',
    ], function() {

        Route::get('dashboard', 'DashboardController@index')
            ->defaults('sidebar', 1)
            ->defaults('icon', 'fas fa-list')
            ->defaults('name', 'Dashboard')
            ->defaults('roles', array('Admin', 'RHU', 'Super Admin'))
            ->name('dashboard')
            ->defaults('href', 'dashboard');

        // REQUEST ROUTES
        $cname = "request";
        Route::group([
                'as' => "$cname.",
                'prefix' => "$cname/"
            ], function () use($cname){
                Route::get("/", ucfirst($cname) . "Controller@index")
                    ->defaults("sidebar", 1)
                    ->defaults("icon", "fa-light fa-keyboard")
                    ->defaults("name", "Requesition Entry")
                    ->defaults("roles", array("Admin", "RHU", "Approver"))
                    ->name($cname)
                    ->defaults("href", "/$cname");

                Route::get("get/", ucfirst($cname) . "Controller@get")->name('get');
                Route::get("getPendingRequests/", ucfirst($cname) . "Controller@getPendingRequests")->name('getPendingRequests');
                Route::get("create/", ucfirst($cname) . "Controller@create")->name('create');
                Route::post("store/", ucfirst($cname) . "Controller@store")->name('store');
                Route::post("update/", ucfirst($cname) . "Controller@update")->name('update');
                Route::post("delete/", ucfirst($cname) . "Controller@delete")->name('delete');

                Route::get("inputInfo", ucfirst($cname) . "Controller@inputInfo")->name('inputInfo');
                Route::get("getNewAlerts", ucfirst($cname) . "Controller@getNewAlerts")->name('getNewAlerts');
                Route::get("seenNewAlerts", ucfirst($cname) . "Controller@seenNewAlerts")->name('seenNewAlerts');
                Route::get("getAdminAlert/", ucfirst($cname) . "Controller@getAdminAlert")->name('getAdminAlert');
            }
        );

        // DATA ROUTES
        $cname = "data";
        Route::group([
                'as' => "$cname.",
                'prefix' => "$cname/"
            ], function () use($cname){
                Route::get("/", ucfirst($cname) . "Controller@index")
                    ->defaults("sidebar", 1)
                    ->defaults("icon", "fa-solid fa-keyboard")
                    ->defaults("name", "Data Entry")
                    ->defaults("roles", array("Admin", "RHU"))
                    ->name($cname)
                    ->defaults("href", "/$cname");

                Route::get("get/", ucfirst($cname) . "Controller@get")->name('get');
                Route::post("store/", ucfirst($cname) . "Controller@store")->name('store');
                Route::post("update/", ucfirst($cname) . "Controller@update")->name('update');
                Route::post("delete/", ucfirst($cname) . "Controller@delete")->name('delete');
            }
        );

        // RECEIVE ROUTE
        $cname = "request";
        Route::group([
                'as' => "$cname.",
                'prefix' => "$cname/"
            ], function () use($cname){
                Route::get("receive/", ucfirst($cname) . "Controller@receive")
                    ->defaults("sidebar", 1)
                    ->defaults("icon", "fa-solid fa-handshake-simple")
                    ->defaults("name", "Receive")
                    ->defaults("roles", array("RHU"))
                    ->name('receive')
                    ->defaults("href", "/$cname/receive");
            }
        );

        // RX ROUTES
        $cname = "rx";
        Route::group([
                'as' => "$cname.",
                'prefix' => "$cname/"
            ], function () use($cname){
                Route::get("/", ucfirst($cname) . "Controller@index")
                    ->defaults("sidebar", 1)
                    ->defaults("icon", "fa-light fa-capsules")
                    ->defaults("name", "RX")
                    ->defaults("roles", array("Admin"))
                    ->name($cname)
                    ->defaults("href", "/$cname");

                Route::get("get/", ucfirst($cname) . "Controller@get")->name('get');
                Route::post("store/", ucfirst($cname) . "Controller@store")->name('store');
                Route::post("update/", ucfirst($cname) . "Controller@update")->name('update');
                Route::post("delete/", ucfirst($cname) . "Controller@delete")->name('delete');
            }
        );

        // USER ROUTES
        $cname = "user";
        Route::group([
                'as' => "$cname.",
                'prefix' => "$cname/"
            ], function () use($cname){
                Route::get("get/", ucfirst($cname) . "Controller@get")->name('get');
                Route::post("store/", ucfirst($cname) . "Controller@store")->name('store');
                Route::post("update/", ucfirst($cname) . "Controller@update")->name('update');
                Route::post("delete/", ucfirst($cname) . "Controller@delete")->name('delete');
                Route::post("restore/", ucfirst($cname) . "Controller@restore")->name('restore');
                Route::post("updatePassword/", ucfirst($cname) . "Controller@updatePassword")->name('updatePassword');
            }
        );

        // ADMIN ROUTES
        $cname = "admin";
        Route::group([
                'as' => "$cname.",
                'prefix' => "$cname/"
            ], function () use($cname){

                Route::get("/", ucfirst($cname) . "Controller@index")
                    ->defaults("sidebar", 1)
                    ->defaults("icon", "fas fa-users")
                    ->defaults("name", "Admin Management")
                    ->defaults("roles", array("Super Admin"))
                    ->name($cname)
                    ->defaults("href", "/$cname");

                Route::get("get/", ucfirst($cname) . "Controller@get")->name('get');
                Route::post("store/", ucfirst($cname) . "Controller@store")->name('store');
                Route::post("update/", ucfirst($cname) . "Controller@update")->name('update');
                Route::post("delete/", ucfirst($cname) . "Controller@delete")->name('delete');
            }
        );

        // RHU ROUTES
        $cname = "rhu";
        Route::group([
                'as' => "$cname.",
                'prefix' => "$cname/"
            ], function () use($cname){

                Route::get("/", ucfirst($cname) . "Controller@index")
                    ->defaults("sidebar", 1)
                    ->defaults("icon", "fas fa-hospital-user")
                    ->defaults("name", "Franchise")
                    ->defaults("roles", array("Admin"))
                    ->defaults("group", "Settings")
                    ->name($cname)
                    ->defaults("href", "/$cname");

                Route::get("get/", ucfirst($cname) . "Controller@get")->name('get');
                Route::post("store/", ucfirst($cname) . "Controller@store")->name('store');
                Route::post("update/", ucfirst($cname) . "Controller@update")->name('update');
                Route::post("delete/", ucfirst($cname) . "Controller@delete")->name('delete');
            }
        );

        // BHC ROUTES
        $cname = "bhc";
        Route::group([
                'as' => "$cname.",
                'prefix' => "$cname/"
            ], function () use($cname){

                Route::get("/", ucfirst($cname) . "Controller@index")
                    ->defaults("sidebar", 1)
                    ->defaults("icon", "fas fa-clinic-medical")
                    ->defaults("name", "Branch")
                    ->defaults("roles", array("Admin", "RHU"))
                    ->defaults("group", "Settings")
                    ->name($cname)
                    ->defaults("href", "/$cname");

                Route::get("get/", ucfirst($cname) . "Controller@get")->name('get');
                Route::get("get2/", ucfirst($cname) . "Controller@get2")->name('get2');
                Route::post("store/", ucfirst($cname) . "Controller@store")->name('store');
                Route::post("update/", ucfirst($cname) . "Controller@update")->name('update');
                Route::post("delete/", ucfirst($cname) . "Controller@delete")->name('delete');
            }
        );

        // APPROVER ROUTES
        $cname = "approver";
        Route::group([
                'as' => "$cname.",
                'prefix' => "$cname/"
            ], function () use($cname){
                Route::get("/", ucfirst($cname) . "Controller@index")
                    ->defaults("sidebar", 1)
                    ->defaults("icon", "fa-solid fa-user-group")
                    ->defaults("name", "Approver")
                    ->defaults("roles", array("Admin"))
                    ->defaults("group", "Settings")
                    ->name($cname)
                    ->defaults("href", "/$cname");

                Route::get("get/", ucfirst($cname) . "Controller@get")->name('get');
                Route::post("store/", ucfirst($cname) . "Controller@store")->name('store');
                Route::post("update/", ucfirst($cname) . "Controller@update")->name('update');
                Route::post("delete/", ucfirst($cname) . "Controller@delete")->name('delete');
            }
        );

        // SKU ROUTES
        $cname = "medicine";
        Route::group([
                'as' => "$cname.",
                'prefix' => "$cname/"
            ], function () use($cname){

                Route::get("/", ucfirst($cname) . "Controller@index")
                    ->defaults("sidebar", 1)
                    ->defaults("icon", "fa-solid fa-capsules")
                    ->defaults("name", "SKU")
                    ->defaults("roles", array("Admin", "RHU"))
                    ->defaults("group", "Settings")
                    ->name($cname)
                    ->defaults("href", "/$cname");

                Route::get("get/", ucfirst($cname) . "Controller@get")->name('get');
                Route::get("getCategories/", ucfirst($cname) . "Controller@getCategories")->name('getCategories');
                Route::get("getReorder/", ucfirst($cname) . "Controller@getReorder")->name('getReorder');
                Route::post("store/", ucfirst($cname) . "Controller@store")->name('store');
                Route::post("storeCategory/", ucfirst($cname) . "Controller@storeCategory")->name('storeCategory');
                Route::post("update/", ucfirst($cname) . "Controller@update")->name('update');
                Route::post("updateCategory/", ucfirst($cname) . "Controller@updateCategory")->name('updateCategory');
                Route::post("updateReorder/", ucfirst($cname) . "Controller@updateReorder")->name('updateReorder');
                Route::post("delete/", ucfirst($cname) . "Controller@delete")->name('delete');
                Route::post("deleteReorder/", ucfirst($cname) . "Controller@deleteReorder")->name('deleteReorder');
                Route::post("deleteCategory/", ucfirst($cname) . "Controller@deleteCategory")->name('deleteCategory');

                Route::get("assign/", ucfirst($cname) . "Controller@assign")->name('assign');
            }
        );

        // STOCK ROUTES
        $cname = "stock";
        Route::group([
                'as' => "$cname.",
                'prefix' => "$cname/"
            ], function () use($cname){

                Route::get("get/", ucfirst($cname) . "Controller@get")->name('get');
            }
        );

        // TRANSACTION TYPE ROUTES
        $cname = "transactionType";
        Route::group([
                'as' => "$cname.",
                'prefix' => "$cname/"
            ], function () use($cname){
                Route::get("/", ucfirst($cname) . "Controller@index")
                    ->defaults("sidebar", 1)
                    ->defaults("icon", "fa-solid fa-tags")
                    ->defaults("name", "Transaction Type")
                    ->defaults("roles", array("Admin"))
                    ->defaults("group", "Settings")
                    ->name($cname)
                    ->defaults("href", "/$cname");

                Route::get("get/", ucfirst($cname) . "Controller@get")->name('get');
                Route::post("store/", ucfirst($cname) . "Controller@store")->name('store');
                Route::post("update/", ucfirst($cname) . "Controller@update")->name('update');
                Route::post("delete/", ucfirst($cname) . "Controller@delete")->name('delete');
            }
        );

        // LOCATION ROUTES
        $cname = "location";
        Route::group([
                'as' => "$cname.",
                'prefix' => "$cname/"
            ], function () use($cname){
                Route::get("/", ucfirst($cname) . "Controller@index")
                    ->defaults("sidebar", 1)
                    ->defaults("icon", "fa-light fa-location-dot")
                    ->defaults("name", "Locations")
                    ->defaults("roles", array("Admin"))
                    ->defaults("group", "Settings")
                    ->name($cname)
                    ->defaults("href", "/$cname");

                Route::get("get/", ucfirst($cname) . "Controller@get")->name('get');
                Route::post("store/", ucfirst($cname) . "Controller@store")->name('store');
                Route::post("update/", ucfirst($cname) . "Controller@update")->name('update');
                Route::post("delete/", ucfirst($cname) . "Controller@delete")->name('delete');
            }
        );

        // REPORT ROUTES
        $cname = "report";
        Route::group([
                'as' => "$cname.",
                'prefix' => "$cname/"
            ], function () use($cname){

                // INVENTORY REPORT
                // INVENTORY REPORT
                // INVENTORY REPORT
                Route::get("inventory/", ucfirst($cname) . "Controller@inventory")
                    ->defaults("sidebar", 1)
                    ->defaults("icon", "fa-solid fa-box-circle-check")
                    ->defaults("name", "Inventory")
                    ->defaults("roles", array("Admin", "RHU"))
                    ->defaults("group", "Reports")
                    ->name('inventory')
                    ->defaults("href", "/$cname/inventory");

                Route::get("getInventory/", ucfirst($cname) . "Controller@getInventory")->name('getInventory');

                // SALES REPORT
                // SALES REPORT
                // SALES REPORT
                Route::get("sales/", ucfirst($cname) . "Controller@sales")
                    ->defaults("sidebar", 1)
                    ->defaults("icon", "fa-solid fa-dollar-sign")
                    ->defaults("name", "Sales")
                    ->defaults("roles", array("Admin", "RHU"))
                    ->defaults("group", "Reports")
                    ->name('sales')
                    ->defaults("href", "/$cname/sales");

                Route::get("getSales/", ucfirst($cname) . "Controller@getSales")->name('getSales');

                // PURCHASE ORDER REPORT
                // PURCHASE ORDER REPORT
                // PURCHASE ORDER REPORT
                Route::get("purchaseOrder/", ucfirst($cname) . "Controller@purchaseOrder")
                    ->defaults("sidebar", 1)
                    ->defaults("icon", "fa-solid fa-hand-holding-dollar")
                    ->defaults("name", "Purchase Order")
                    ->defaults("roles", array("Admin", "RHU"))
                    ->defaults("group", "Reports")
                    ->name('purchaseOrder')
                    ->defaults("href", "/$cname/purchaseOrder");

                Route::get("getPurchaseOrder/", ucfirst($cname) . "Controller@getPurchaseOrder")->name('getPurchaseOrder');

                // DAILY SHEETS REPORT
                // DAILY SHEETS REPORT
                // DAILY SHEETS REPORT
                Route::get("dailySheet/", ucfirst($cname) . "Controller@dailySheet")
                    ->defaults("sidebar", 1)
                    ->defaults("icon", "fa-solid fa-files")
                    ->defaults("name", "Daily Sheets")
                    ->defaults("roles", array("Admin", "RHU"))
                    ->defaults("group", "Reports")
                    ->name('dailySheet')
                    ->defaults("href", "/$cname/dailySheet");

                Route::get("getDailySheet/", ucfirst($cname) . "Controller@getDailySheet")->name('getDailySheet');

                // BIN CARD REPORT
                // BIN CARD REPORT
                // BIN CARD REPORT
                Route::get("binCard/", ucfirst($cname) . "Controller@binCard")
                    ->defaults("sidebar", 1)
                    ->defaults("icon", "fa-solid fa-cards-blank")
                    ->defaults("name", "Bin Card")
                    ->defaults("roles", array("Admin", "RHU"))
                    ->defaults("group", "Reports")
                    ->name('binCard')
                    ->defaults("href", "/$cname/binCard");

                Route::get("getBinCard/", ucfirst($cname) . "Controller@getBinCard")->name('getBinCard');

                // DISPOSED TO RHU
                // DISPOSED TO RHU
                // DISPOSED TO RHU
                Route::get("toRhu/", ucfirst($cname) . "Controller@toRhu")
                    ->defaults("sidebar", 1)
                    ->defaults("icon", "fa-solid fa-right-left")
                    ->defaults("name", "Transferred to Franchise")
                    ->defaults("roles", array("Admin"))
                    ->defaults("group", "Reports")
                    ->name('toRhu')
                    ->defaults("href", "/$cname/toRhu");

                Route::get("getToRhu/", ucfirst($cname) . "Controller@getToRhu")->name('getToRhu');

                // DISPOSED TO BARANGAY
                // DISPOSED TO BARANGAY
                // DISPOSED TO BARANGAY
                Route::get("toBarangay/", ucfirst($cname) . "Controller@toBarangay")
                    ->defaults("sidebar", 1)
                    ->defaults("icon", "fa-solid fa-right-left")
                    ->defaults("name", "Transferred to Branch")
                    ->defaults("roles", array("Admin", "RHU"))
                    ->defaults("group", "Reports")
                    ->name('toBarangay')
                    ->defaults("href", "/$cname/toBarangay");

                Route::get("getToBarangay/", ucfirst($cname) . "Controller@getToBarangay")->name('getToBarangay');

                // WASTED MEDICINES
                Route::get("wastedMedicine/", ucfirst($cname) . "Controller@wastedMedicine")
                    ->defaults("sidebar", 1)
                    ->defaults("icon", "fa-solid fa-dumpster")
                    ->defaults("name", "Wasted Medicinces")
                    ->defaults("roles", array("Admin", "RHU"))
                    ->defaults("group", "Reports")
                    ->name('wastedMedicine')
                    ->defaults("href", "/$cname/wastedMedicine");

                Route::get("getWastedMedicine/", ucfirst($cname) . "Controller@getWastedMedicine")->name('getWastedMedicine');

                // ALERT REPORT
                // ALERT REPORT
                // ALERT REPORT
                Route::get("alert/", ucfirst($cname) . "Controller@alert")
                    ->defaults("sidebar", 1)
                    ->defaults("icon", "fa-solid fa-bell-exclamation")
                    ->defaults("name", "Alerts")
                    ->defaults("roles", array("Admin"))
                    ->defaults("group", "Reports")
                    ->name('alert')
                    ->defaults("href", "/$cname/alert");

                Route::get("getAlert/", ucfirst($cname) . "Controller@getAlert")->name('getAlert');

                // DASHBOARD CHARTS
                Route::get("salesPerRhu/", ucfirst($cname) . "Controller@salesPerRhu")->name('salesPerRhu');
                Route::get("deliveredRequests/", ucfirst($cname) . "Controller@deliveredRequests")->name('deliveredRequests');
            }
        );


        // EXPORT
        // EXPORT
        // EXPORT
        $cname = "export";
        Route::group([
                'as' => "$cname.",
                'prefix' => "$cname/"
            ], function () use($cname){
                Route::get($cname . "BinCard/", ucfirst($cname) . "Controller@$cname" . "BinCard")->name($cname . "BinCard");
                Route::get($cname . "Inventory/", ucfirst($cname) . "Controller@$cname" . "Inventory")->name($cname . "Inventory");
                Route::get($cname . "Sales/", ucfirst($cname) . "Controller@$cname" . "Sales")->name($cname . "Sales");
                Route::get($cname . "PurchaseOrder/", ucfirst($cname) . "Controller@$cname" . "PurchaseOrder")->name($cname . "PurchaseOrder");
                Route::get($cname . "DailySheet/", ucfirst($cname) . "Controller@$cname" . "DailySheet")->name($cname . "DailySheet");
                Route::get($cname . "Requests/", ucfirst($cname) . "Controller@$cname" . "Requests")->name($cname . "Requests");
                Route::get($cname . "Sku/", ucfirst($cname) . "Controller@$cname" . "Sku")->name($cname . "Sku");
            }
        );

        // LOCATION ROUTES
        $cname = "theme";
        Route::group([
                'as' => "$cname.",
                'prefix' => "$cname/"
            ], function () use($cname){
                Route::get("get/", ucfirst($cname) . "Controller@get")->name('get');
                Route::post("update/", ucfirst($cname) . "Controller@update")->name('update');
            }
        );

        // DATATABLES
        $cname = "datatable";
        Route::group([
                'as' => "$cname.",
                'prefix' => "$cname/"
            ], function () use($cname){

                Route::get("admin", ucfirst($cname) . "Controller@admin")->name('admin');
                Route::get("rhu", ucfirst($cname) . "Controller@rhu")->name('rhu');
                Route::get("bhc", ucfirst($cname) . "Controller@bhc")->name('bhc');
                Route::get("medicine", ucfirst($cname) . "Controller@medicine")->name('medicine');
                Route::get("medicine2", ucfirst($cname) . "Controller@medicine2")->name('medicine2');
                Route::get("transactionType", ucfirst($cname) . "Controller@transactionType")->name('transactionType');
                Route::get("approver", ucfirst($cname) . "Controller@approver")->name('approver');
                Route::get("requests", ucfirst($cname) . "Controller@requests")->name('requests');
                Route::get("receive", ucfirst($cname) . "Controller@receive")->name('receive');
                Route::get("rx", ucfirst($cname) . "Controller@rx")->name('rx');
                Route::get("location", ucfirst($cname) . "Controller@location")->name('location');
                Route::get("data", ucfirst($cname) . "Controller@data")->name('data');
            }
        );
    }
);

require __DIR__.'/auth.php';