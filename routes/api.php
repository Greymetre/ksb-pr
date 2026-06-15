<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\BeatController;
use App\Http\Controllers\Api\CallLogController;
use App\Http\Controllers\Api\CheckinController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\CustomController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DealerAppointmentController;
use App\Http\Controllers\Api\ExpensesTypeController;
use App\Http\Controllers\Api\GiftController;
use App\Http\Controllers\Api\LeaveController;
// use App\Http\Controllers\Api\LeaveControllerApi;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\MarketIntelligenceController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PrimarySchemeReportController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SalesController;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VisitReportController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\ReportingActivityController;
use App\Http\Controllers\Api\TourPlanController;
use App\Http\Controllers\Api\TransactionHistoryController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SapStockController;
use App\Http\Controllers\Api\MspActivityController;
use App\Http\Controllers\Api\ComplaintApiController;
use App\Http\Controllers\Api\ServiceBillController;
use App\Http\Controllers\Api\ComplaintAPICustomerController;
use App\Http\Controllers\Api\ExotelApiController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\ServiceBillCustController;
use App\Http\Controllers\Api\UserLatLongController;
use App\Http\Controllers\Api\SecondaryCustomerController; // We'll create this
use App\Http\Controllers\Api\MasterDistributorApiController;
use App\Http\Controllers\Api\TourProgrammeApiController;
use App\Http\Controllers\Api\CustomerApiController;
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

/*================= Auth Routes ============================*/

Route::post('login', [LoginController::class, 'login']);
Route::post('signup', [LoginController::class, 'signup']);
Route::post('customerLogin', [LoginController::class, 'customerLogin']);
Route::post('customer/email-login', [LoginController::class, 'customerEmailLogin']);
Route::post('customer/email-signup', [LoginController::class, 'customerEmailSignup']);
Route::post('verifyotp', [LoginController::class, 'verifyotp']);
Route::post('customerSignup', [LoginController::class, 'customerSignup']);
Route::any('getCategoryList', [ProductController::class, 'getCategoryList']);
Route::any('getSubCategoryList', [ProductController::class, 'getSubCategoryList']);
Route::any('getProductList', [ProductController::class, 'getProductList']);
Route::any('getProductDetails', [ProductController::class, 'getProductDetails']);
Route::any('getOrderBuyers', [OrderController::class, 'getOrderBuyers']);
Route::any('getOrderSellers', [OrderController::class, 'getOrderSellers']);
Route::any('getGiftList', [ProductController::class, 'getGiftList']);
Route::any('getCategoryData', [ProductController::class, 'getCategoryData']);
Route::any('getSubCategoryData', [ProductController::class, 'getSubCategoryData']);
Route::any('getStateList', [AddressController::class, 'getStateList']);
Route::any('getDistrictList', [AddressController::class, 'getDistrictList']);
Route::any('getCityList', [AddressController::class, 'getCityList']);
Route::any('getPincodeList', [AddressController::class, 'getPincodeList']);
Route::any('getCustomerTypeList', [CustomController::class, 'getCustomerTypeList']);
Route::any('getPincodeInfo', [AddressController::class, 'getPincodeInfo']);
Route::any('getReportType', [CustomController::class, 'getReportType']);
Route::any('getWorkType', [CustomController::class, 'getWorkType']);
Route::any('getDevision', [CustomController::class, 'getDevision']);
Route::any('mobileNumberExists', [CustomController::class, 'mobileNumberExists']);
Route::any('gstNumberExists', [CustomController::class, 'gstNumberExists']);
Route::any('getRetailerList', [CustomController::class, 'getRetailerList']);
Route::any('getslider', [CustomController::class, 'getslider']);
Route::get('getsettings', [DashboardController::class, 'getsettings']);
Route::get('get-field-connet-version', [DashboardController::class, 'getVersion']);
Route::any('insert_sap_stock', [SapStockController::class, 'insertSapStock']);
Route::any('insert_sap_sell', [SapStockController::class, 'insertSapSell']);
Route::get('master-distributors/supervisors', [MasterDistributorApiController::class, 'getSupervisors']);
Route::get('master-distributors/contact-personss', [MasterDistributorApiController::class, 'contactPersonList']);
Route::get('/master-distributors/cities', [MasterDistributorApiController::class, 'distributorCities']);
Route::post('/delete-user', [UserController::class, 'deleteUser']);
Route::any('emailExists', [CustomController::class, 'emailExists']);

Route::post('/exotel/make-call', [ExotelApiController::class, 'makeCall']);
Route::get('/exotel/call-details', [ExotelApiController::class, 'getCallDetails']);
Route::get('/exotel/get-recording', [ExotelApiController::class, 'getRecording']);

Route::post('/get-location-by-pincode', [CustomerApiController::class,'getLocationByPincode']);
Route::get('/getAppVersion', [CustomerApiController::class,'getAppVersion']);

/*================= Customer Routes ============================*/
Route::group(['middleware' => ['auth:customers']], function () {
    // Route::any('customer/getProfile', [LoginController::class, 'getCustomerProfile']);
    // Route::any('customer/updateProfile', [LoginController::class, 'updateCustomerProfile']);
    // Dashboard
    Route::any('customer/logout', [LoginController::class, 'customerlogout']);
    Route::any('customer/dashboard', [DashboardController::class, 'customerDashboard']);
    Route::any('customer/getKyc', [DashboardController::class, 'getKyc']);
    Route::post('customer/addKyc', [DashboardController::class, 'addKyc']);
    Route::post('customer/updateprofile', [DashboardController::class, 'updateprofile']);
    Route::get('customer/getsettings', [DashboardController::class, 'getsettings']);
    Route::get('customer/getpoints', [DashboardController::class, 'getpoints']);
    // Secondry Sales
    Route::post('customer/insertSales', [SalesController::class, 'customerInsertSales']);
    Route::any('customer/getSales', [SalesController::class, 'customerGetSales']);
    Route::any('customer/getSalesDetails', [SalesController::class, 'customerSalesDetails']);
    Route::any('customer/approveSales', [SalesController::class, 'customerApproveSales']);
    Route::any('customer/rejectSales', [SalesController::class, 'customerRejectSales']);
    // Get Customer list
    Route::any('customer/parentCustomers', [CustomerController::class, 'customerParentCustomers']);
    Route::any('customer/getRetailers', [CustomerController::class, 'customerRetailers']);
    Route::post('customers-active', [CustomerController::class, 'active']);
    // Order Master
    Route::post('customer/insertOrder', [OrderController::class, 'customerInsertOrder']);
    Route::any('customer/getOrderList', [OrderController::class, 'customerOrderList']);
    Route::any('customer/getOrderDetails', [OrderController::class, 'customerOrderDetails']);
    //Coupon Scan
    Route::post('customer/couponScans', [CouponController::class, 'customerCouponScans']);
    Route::post('customer/getScanedCoupons', [CouponController::class, 'customerScanedCouponList']);
    Route::post('customer/pointRedemption', [WalletController::class, 'customerpointRedemption']);
    Route::any('customer/getProductByCoupon', [CouponController::class, 'getProductByCoupon']);
    Route::any('customer/getEndUserData', [CouponController::class, 'getEndUserData']);
    Route::post('customer/warrantyActivation', [CouponController::class, 'warrantyActivation']);
    Route::any('customer/getwarranty', [CouponController::class, 'getwarranty']);
    // Gift Catalogue
    Route::any('customer/getgiftcatalogue', [GiftController::class, 'getgiftcatalogue']);
    Route::any('customer/getgiftcategories', [GiftController::class, 'getgiftcategories']);
    Route::any('customer/getgiftsubcategories', [GiftController::class, 'getgiftsubcategories']);
    Route::any('customer/getgiftdetails', [GiftController::class, 'getgiftdetails']);
    // Transacation Coupon History
    Route::any('customer/getcouponhistory', [TransactionHistoryController::class, 'getcouponhistory']);
    Route::any('customer/getredemptionhistory', [TransactionHistoryController::class, 'getredemptionhistory']);
    Route::any('customer/getBankDetails', [TransactionHistoryController::class, 'getBankDetails']);
    Route::post('customer/addNeftRedemption', [TransactionHistoryController::class, 'addNeftRedemption']);
    Route::post('customer/addGiftRedemption', [TransactionHistoryController::class, 'addGiftRedemption']);
    Route::post('customer/addSerialNumber', [TransactionHistoryController::class, 'addSerialNumber']);
    Route::get('customer/getDamageEntry', [TransactionHistoryController::class, 'getDamageEntry']);
    Route::post('customer/addDamageEntry', [TransactionHistoryController::class, 'addDamageEntry']);
    //Complaints Route
    Route::any('customer/getComplaintType', [ComplaintController::class, 'getComplaintType']);
    Route::any('customer/getComplaints', [ComplaintController::class, 'getComplaints']);
    Route::post('customer/addComplaint', [ComplaintController::class, 'addComplaint']);
    Route::any('customer/getComplaintCounts', [ComplaintController::class, 'getComplaintCounts']);

    // // Additional helpers from your code
    // Route::get('cities', [SecondaryCustomerController::class, 'getCities']); // Get cities by state_id
    // Route::get('/secondary-customers/download', [SecondaryCustomerController::class, 'downloadExcel']); // Excel download (returns file)
    // Route::get('/secondary-customers/template', [SecondaryCustomerController::class, 'downloadTemplate']); // Template download
   Route::prefix('customer')->group(function () {
        // Complaint API routes
        Route::apiResource('complaints', ComplaintAPICustomerController::class)->only(['index', 'show', 'update']);
        Route::post('complaint/work-done/{id}', [ComplaintAPICustomerController::class, 'work_done_submit']);
        Route::get('complaint/select-option', [ComplaintAPICustomerController::class, 'select_option']);
        Route::get('complaint/complaint-type-count', [ComplaintAPICustomerController::class, 'complaint_type_count']);
        Route::get('complaint/filter-option', [ComplaintAPICustomerController::class, 'filter_option']);
        Route::get('complaint/{id}/get-notes', [ComplaintAPICustomerController::class, 'getNotes']);
        
        // Service Bill API routes
        Route::get('service-bill-list', [ServiceBillCustController::class, 'index']);
        Route::get('complaint/{id}/service-bill', [ServiceBillCustController::class, 'create']);
        Route::get('service-bill/reasons', [ServiceBillCustController::class, 'serviceBillComplaintReasons']);
        Route::post('complaint/{id}/service-bill/create', [ServiceBillCustController::class, 'store']);
        Route::get('complaint/{id}/service-bill/select-option', [ServiceBillCustController::class, 'select_option']);
        Route::get('service-bill/{service_id}/details', [ServiceBillCustController::class, 'show']);
        Route::post('service-bill/{service_id}/update', [ServiceBillCustController::class, 'update']);
        Route::post('service-bill/{service_id}/update-status', [ServiceBillCustController::class, 'change_status']);
        Route::get('complaint/{id}/service-charge-product', [ServiceBillCustController::class, 'getServiceChargeProduct']);
        Route::get('service-charge-product/{id}', [ServiceBillCustController::class, 'getServiceProductDetails']);
    });
});

Route::group(['middleware' => ['auth:users,customers']], function () {
    // Dashboard
    Route::any('dashboard', [DashboardController::class, 'dashboard']);
    Route::any('getLeaveBalance', [DashboardController::class, 'getLeaveBalance']);
    Route::any('getUserSataus', [DashboardController::class, 'getUserSataus']);
    Route::any('pendingCounts', [DashboardController::class, 'pendingCounts']);
    Route::any('getUserDashboardData', [DashboardController::class, 'getUserDashboardData']);
    Route::any('getSarthiPoints', [DashboardController::class, 'getSarthiPoints']);

    Route::any('getMarketIntelligencesField', [MarketIntelligenceController::class, 'getFields']);
    Route::any('MarketIntelligenceStore', [MarketIntelligenceController::class, 'MarketIntelligenceStore']);

    Route::any('getProfile', [LoginController::class, 'getProfile']);
    Route::post('updateProfile', [LoginController::class, 'updateProfile']);
    Route::any('logout', [LoginController::class, 'logout']);
    Route::any('getOrderDiscountLimit', [LoginController::class, 'getOrderDiscountLimit']);

    Route::get('secondary-customers', [SecondaryCustomerController::class, 'index']); // List with filters
    Route::get('secondary-customers/{id}', [SecondaryCustomerController::class, 'show']); // Show one
    Route::post('secondary-customers', [SecondaryCustomerController::class, 'store']); // Create
    Route::post('secondary-customers/{id}', [SecondaryCustomerController::class, 'update']); // Update (using POST for file uploads simplicity; or use PUT/PATCH)
    Route::delete('secondary-customers/{id}', [SecondaryCustomerController::class, 'destroy']); // Delete
    Route::put('secondary-customers/{id}/status', [SecondaryCustomerController::class, 'changeStatus']);
    Route::get('secondary-customer/cities', [SecondaryCustomerController::class, 'getUsedCities']);
    Route::get('getMyHierarchyUsers', [SecondaryCustomerController::class, 'getMyHierarchyUsers']);
    //master distributor routes
    // Master Distributor API Routes (full CRUD + helpers)
    Route::get('master-distributors', [MasterDistributorApiController::class, 'index']);          // List with filters & pagination
    Route::get('master-distributors/{id}', [MasterDistributorApiController::class, 'show']);      // Show single distributor
    Route::post('master-distributors', [MasterDistributorApiController::class, 'store']);         // Create new distributor
    Route::post('master-distributors/{id}', [MasterDistributorApiController::class, 'update']);   // Update (POST used for file uploads)
    Route::delete('master-distributors/{id}', [MasterDistributorApiController::class, 'destroy']); // Delete
    // Additional helpers from your code
    // Contact Person List
    // Route::get('master-distributors/contact-persons', [MasterDistributorApiController::class, 'contactPersonList']);

    // All Cities used in Distributors
    Route::get('/order/secondary-customers', [CustomerApiController::class, 'secondaryCustomers']);

    Route::get('/master-distributors/cities', [MasterDistributorApiController::class, 'distributorCities']);
    Route::get('cities', [SecondaryCustomerController::class, 'getCities']); // Get cities by state_id
    Route::prefix('tour-plans')->group(function () {
        Route::get('/',        [TourProgrammeApiController::class, 'index']);     // list
        Route::post('/',       [TourProgrammeApiController::class, 'store']);    // create (one or many)
        Route::get('/{id}',    [TourProgrammeApiController::class, 'show']);     // view one
        Route::put('/{id}',    [TourProgrammeApiController::class, 'update']);   // or patch
        Route::delete('/{id}', [TourProgrammeApiController::class, 'destroy']);  // delete
    });

    Route::get('tour-plan/global', [TourProgrammeApiController::class, 'globalList']);
    Route::post('tour-plan/changeStatus', [TourPlanController::class, 'changeStatus']);
    Route::get('/order/distributors', [CustomerApiController::class, 'distributors']);
    Route::get('/attendance/today-summary', [AttendanceController::class, 'getTodayMyTeamAttendanceSummary']);
    Route::get('/sales/sales-summary', [AttendanceController::class, 'getTodayTeamSalesList']);
    Route::get('/sales/retailer-sales-summary', [AttendanceController::class, 'getRetailerSalesSummary']);
    Route::get('/today-attendance-zone', [AttendanceController::class, 'getTodayTeamAttendanceList']);
    Route::get('/user-attendance-zone-branch', [AttendanceController::class, 'getAssignedUsersBasicList']);
    // Customer
    Route::post('storeCustomer', [CustomerController::class, 'storeCustomer']);
    Route::post('updateCustomerLocation', [CustomerController::class, 'updateCustomerLocation']);
    Route::post('updateCustomerProfile', [CustomerController::class, 'updateCustomerProfile']);
    Route::any('getRetailers', [CustomerController::class, 'getRetailers']);
    Route::any('getDistributors', [CustomerController::class, 'getDistributors']);
    Route::any('getCustomerList', [CustomerController::class, 'getCustomerList']);
    Route::any('getCustomerInfo', [CustomerController::class, 'getCustomerInfo']);
    Route::post('leadToCustomer', [CustomerController::class, 'leadToCustomer']);
    // Get Order List
    Route::post('insertOrder', [OrderController::class, 'insertOrder']);
    Route::any('getOrderList', [OrderController::class, 'getOrderList']);
    Route::get('getHierarchyOrderStats', [OrderController::class, 'getHierarchyOrderStats']);
    Route::any('getClusterOrderList', [OrderController::class, 'getClusterOrderList']);
    Route::any('getSpecialOrderList', [OrderController::class, 'getSpecialOrderList']);
    Route::post('updateClusterOrder', [OrderController::class, 'updateClusterOrder']);
    Route::any('getOrderDetails', [OrderController::class, 'getOrderDetails']);
    Route::post('addCartItems', [OrderController::class, 'addCartItems']);
    Route::get('getCartItems', [OrderController::class, 'getCartItems']);
    Route::any('getOrderPfd', [OrderController::class, 'getOrderPfd']);
    Route::post('customer/deleteOrder', [OrderController::class, 'deleteOrder']);
    Route::post('submitFullyDispatched', [OrderController::class, 'submitFullyDispatched']);
    Route::post('submitPartiallyDispatched', [OrderController::class, 'submitPartiallyDispatched']);

    //Leave
    Route::any('addLeaves', [LeaveController::class, 'addLeaves']);
    Route::any('getLeaves', [LeaveController::class, 'getLeaves']);
    Route::get('leaves/balance', [LeaveController::class, 'getMyBalances']);
    // Route::any('leaves', [LeaveControllerApi::class, 'store']);

    Route::any('getBeatList', [BeatController::class, 'getBeatList']);
    Route::any('getBeatDropdownList', [BeatController::class, 'getBeatDropdownList']);
    Route::any('getBeatCustomers', [BeatController::class, 'getBeatCustomers']);
    Route::any('getTodaySchedul', [BeatController::class, 'getTodaySchedul']);
    Route::post('userPunchin', [AttendanceController::class, 'userPunchin']);
    Route::post('userPunchout', [AttendanceController::class, 'userPunchout']);
    Route::any('getPunchin', [AttendanceController::class, 'getPunchin']);
    Route::get('getAllAttendance', [AttendanceController::class, 'getAllUserPunchInOut']);
    Route::any('getAllUserPunchInOut', [AttendanceController::class, 'getAllUserPunchInOut']);
    Route::any('attendance/changeStatus', [AttendanceController::class, 'changeStatus']);
    Route::any('showAttendance', [AttendanceController::class, 'showAttendance']);
    Route::any('lastPunchin', [AttendanceController::class, 'lastPunchin']);
    Route::post('submitCheckin', [CheckinController::class, 'submitCheckin']);
    Route::get('getCurrentOpenCheckin', [CheckInController::class, 'getCurrentOpenCheckin']);
    Route::post('submitCheckout', [CheckinController::class, 'submitCheckout']);
    Route::any('getCheckin', [CheckinController::class, 'getCheckin']);
    Route::any('addCheckinDraft', [CheckinController::class, 'addCheckinDraft']);
    Route::any('getCheckinDraft', [CheckinController::class, 'getCheckinDraft']);
    Route::get('getCheckinByEntity', [CheckinController::class, 'getCheckinByEntity']);
    Route::post('submitVisitReports', [VisitReportController::class, 'submitVisitReports']);
    Route::any('getVisitTypes', [VisitReportController::class, 'getVisitTypes']);
    Route::any('getVisitReports', [VisitReportController::class, 'getVisitReports']);

    // Get Sales
    Route::post('insertSales', [SalesController::class, 'insertSales']);
    Route::post('couponScans', [CouponController::class, 'couponScans']);
    Route::any('getSales', [SalesController::class, 'getSales']);
    Route::any('getSalesDetails', [SalesController::class, 'getSalesDetails']);
    Route::any('getSurveyQuestions', [SurveyController::class, 'getSurveyQuestions']);
    Route::any('getUnpaidInvoice', [PaymentController::class, 'getUnpaidInvoice']);
    Route::post('paymentReceived', [PaymentController::class, 'paymentReceived']);
    Route::any('getPaymentList', [PaymentController::class, 'getPaymentList']);
    Route::any('getPaymentInfo', [PaymentController::class, 'getPaymentInfo']);
    Route::post('createNewTask', [UserController::class, 'createNewTask']);
    Route::post('taskMarkComplite', [UserController::class, 'taskMarkComplite']);
    Route::post('getTaskInfo', [UserController::class, 'getTaskInfo']);
    Route::any('getUpcomingTasks', [UserController::class, 'getUpcomingTasks']);
    Route::any('updateLiveLocation', [UserController::class, 'updateLiveLocation']);
    Route::any('addTourProgramme', [UserController::class, 'addTourProgramme']);
    Route::any('upcommingTourProgramme', [UserController::class, 'upcommingTourProgramme']);
    Route::any('userCityList', [UserController::class, 'userCityList']);
    Route::post('userScheduleBeat', [BeatController::class, 'userScheduleBeat']);
    Route::post('pointsCollection', [WalletController::class, 'pointsCollection']);
    Route::any('getCollectedPoints', [WalletController::class, 'getCollectedPoints']);
    Route::any('getUserActivity', [UserController::class, 'getUserActivity']);
    Route::any('requestReport', [UserController::class, 'requestReport']);
    Route::any('getNotification', [UserController::class, 'getNotification']);
    Route::any('masterStateCity', [UserController::class, 'masterStateCity']);
    Route::any('getPunchinMasterData', [UserController::class, 'getPunchinMasterData']);
    Route::any('userDistrictList', [UserController::class, 'userDistrictList']);
    Route::any('userCitiesByDistrict', [UserController::class, 'userCitiesByDistrict']);
    //Reporting Activity
    Route::post('reporting/users', [ReportingActivityController::class, 'allReportingUsers'])
     ->middleware('auth:users');   // or 'auth:sanctum'
    // Route::get('user/activity', [ReportingActivityController::class, 'userActivity']);
    Route::post('user/activity', [ReportingActivityController::class, 'userActivity'])
     ->middleware('auth:users');
    // Route::get('reporting/users', [ReportingActivityController::class, 'allReportingUsers']);
    // Route::get('user/activity', [ReportingActivityController::class, 'userActivity']);
    Route::get('customer/activity', [ReportingActivityController::class, 'customerActivity']);
    Route::get('designations', [ReportingActivityController::class, 'getDesignations']);
    //Tour Plan
    Route::get('tour/userlist', [TourPlanController::class, 'user_list']);
    Route::get('tour/show', [TourPlanController::class, 'show']);
    Route::post('tour/add', [TourPlanController::class, 'add']);
    Route::post('tour/edit', [TourPlanController::class, 'edit']);
    Route::get('tour-plan/global', [TourProgrammeApiController::class, 'globalList']);
    Route::get('tour/global', [TourPlanController::class, 'global']);
    //Expenses Type
    Route::post('/getExpensesType', [ExpensesTypeController::class, 'getExpensesType']);
    Route::post('createExpense', [ExpensesTypeController::class, 'createExpense']);
    Route::any('expenseListing', [ExpensesTypeController::class, 'expenseListing']);
    Route::any('allExpenseListing', [ExpensesTypeController::class, 'allExpenseListing']);
    Route::post('expenseDetails', [ExpensesTypeController::class, 'expenseDetails']);
    Route::post('updateExpense', [ExpensesTypeController::class, 'updateExpense']);
    Route::post('approveExpense', [ExpensesTypeController::class, 'approveExpense']);
    Route::post('rejectExpense', [ExpensesTypeController::class, 'rejectExpense']);
    //Dealer Appointment
    Route::get('getappointments', [DealerAppointmentController::class, 'getappointments']);
    Route::get('getappointmentsDetails', [DealerAppointmentController::class, 'getappointmentsDetails']);
    Route::get('getappointmentsPDF', [DealerAppointmentController::class, 'getappointmentsPDF']);
    Route::post('approveAppointment', [DealerAppointmentController::class, 'approveAppointment']);
    Route::post('addbmremark', [DealerAppointmentController::class, 'addbmremark']);

    //Report 
    Route::get('primary-sales', [ReportController::class, 'primarySales']);
    Route::get('monthly-sales', [ReportController::class, 'monthlySales']);
    Route::get('getDealerGrowth', [ReportController::class, 'getDealerGrowth']);

    //Primary Scheme report
    Route::get('getprimary-scheme-filter', [PrimarySchemeReportController::class, 'getPrimarySchemeFilter']);
    Route::get('getPrimarySchemes', [PrimarySchemeReportController::class, 'getPrimarySchemes']);
    Route::get('getPrimarySchemeData', [PrimarySchemeReportController::class, 'getPrimarySchemeData']);

    // msp activity
    Route::apiResource('user/msp_activity', MspActivityController::class)->only(['index', 'store']);
    Route::get('user/msp-activity-counts', [MspActivityController::class, 'getMspActivityCount']);
    Route::get('user/msp-activity-filter', [MspActivityController::class, 'getMspActivityFilter']);

    // Complaint Api's routes 
    Route::apiResource('complaints', ComplaintApiController::class)->only(['index', 'show', 'update']);
    Route::post('complaint/work-done/{id}', [ComplaintApiController::class, 'work_done_submit']);
    Route::get('complaint/select-option', [ComplaintApiController::class, 'select_option']);
    Route::get('complaint/complaint-type-count', [ComplaintApiController::class, 'complaint_type_count']);
    Route::get('complaint/filter-option', [ComplaintApiController::class, 'filter_option']);
    Route::get('complaint/{id}/get-notes', [ComplaintApiController::class, 'getNotes']);

    // Service bill Api's routes
    Route::get('service-bill-list', [ServiceBillController::class, 'index']);
    Route::get('complaint/{id}/service-bill', [ServiceBillController::class, 'create']);
    Route::get('service-bill/reasons', [ServiceBillController::class, 'serviceBillComplaintReasons']);
    Route::post('complaint/{id}/service-bill/create', [ServiceBillController::class, 'store']);
    Route::get('complaint/{id}/service-bill/select-option', [ServiceBillController::class, 'select_option']);
    Route::get('service-bill/{service_id}/details', [ServiceBillController::class, 'show']);
    Route::post('service-bill/{service_id}/update', [ServiceBillController::class, 'update']);
    Route::post('service-bill/{service_id}/update-status', [ServiceBillController::class, 'edit']);
    Route::get('complaint/{id}/service-charge-product', [ServiceBillController::class, 'getServiceChargeProduct']);
    Route::get('service-charge-product/{id}', [ServiceBillController::class, 'getServiceProductDetails']);
    Route::post('service-bill/{service_id}/update-status', [ServiceBillController::class, 'change_status']);

    // User Latitude Longitude routes
    Route::post('multi-latitude-longitude', [UserLatLongController::class, 'store']);

    //Lead Management
    Route::get('leads', [LeadController::class, 'getLeads']);
    Route::get('getLeadStatusSource', [LeadController::class, 'leadStatusSource']);
    Route::post('leadCreate', [LeadController::class, 'leadCreate']);
    Route::get('leadDetails', [LeadController::class, 'leadDetails']);
    Route::post('addNote', [LeadController::class, 'addNote']);
    Route::get('getTaskDropdowns', [LeadController::class, 'getTaskDropdowns']);
    Route::post('addleadTask', [LeadController::class, 'addleadTask']);
    Route::get('getLeadContacts', [LeadController::class, 'getLeadContacts']);
    Route::post('addLeadopportunity', [LeadController::class, 'addLeadopportunity']);
    Route::get('getAllOpportunities', [LeadController::class, 'getAllOpportunities']);
    Route::post('deleteOpportunity', [LeadController::class, 'deleteOpportunity']);
    Route::post('updateLeadStatus', [LeadController::class, 'updateLeadStatus']);
    Route::get('getLeadTasks', [LeadController::class, 'getLeadTasks']);
    Route::get('getOtherTasks', [LeadController::class, 'getOtherTasks']);
    Route::post('changeTaskStatus', [LeadController::class, 'change_task_status']);
    Route::post('changeOtherTaskStatus', [LeadController::class, 'change_other_task_status']);
    Route::get('getAllLeadNotifications', [LeadController::class, 'getAllLeadNotifications']);
    Route::post('readNotification', [LeadController::class, 'readNotification']);

    Route::post('leadSubmitCheckin', [LeadController::class, 'submitCheckin']);
    Route::post('leadSubmitCheckout', [LeadController::class, 'submitCheckout']);
    Route::any('leadGetCheckin', [LeadController::class, 'getCheckin']);

    // Call logs routes
    Route::post('add-call-logs', [CallLogController::class, 'store']);
    Route::get('get-call-logs', [CallLogController::class, 'index']);
    Route::get('get-last-call', [CallLogController::class, 'last_call']);
    Route::post('update-call-remark', [CallLogController::class, 'update_remark']);
});

