<?php
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\AssignEmployeeRole;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CreditItemController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerCreditController;
use App\Http\Controllers\EcommerceController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeSaleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\MediumController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MpesaResponseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderDeliveredController;
use App\Http\Controllers\OrderPendingController;
use App\Http\Controllers\PaidItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PermissionTierController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\RequiredItemController;
use App\Http\Controllers\RetailController;
use App\Http\Controllers\Retailer\EcommerceProductController;
use App\Http\Controllers\Retailer\EcommerceSettingController;
use App\Http\Controllers\Retailer\EcommerceVendorController;
use App\Http\Controllers\Retailer\MarketController;
use App\Http\Controllers\RetailItemController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SalePaymentController;
use App\Http\Controllers\SaleSettingController;
use App\Http\Controllers\SaleTerminalController;
use App\Http\Controllers\SaleTransactionController;
use App\Http\Controllers\SessionAccountController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskDependancyController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TierController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoCallController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|->middleware(['log-pageview'])
 */

Route::prefix('v1')->group(function () {

    Route::post('/login', [LoginController::class, 'login']);

    Route::post('/register', [RegisterController::class, 'register']);

    Route::post('/forgot-password', [ForgotPasswordController::class, 'forgotPassword'])->name('password.email');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
    Route::post('/reset-password-code', [ResetPasswordController::class, 'sendResetCode'])->name('password.sendResetCode');

    // Social Authentication Routes
    Route::middleware('throttle:10,1')->group(function () {
        Route::get('/auth/{provider}', [SocialAuthController::class, 'redirectToProvider']);
        Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback']);
    });

    Route::get('/register/roles/{type}', [RegisterController::class, 'fetchRegisterRoles']);
    Route::get('/register/roles', [RegisterController::class, 'fetchRegisterRoles']);

    // Route::get('/register/roles/{type}/levels/{level}', [RegisterController::class, 'fetchRegisterRoles']);

    Route::post('/login/validate-token', [UserController::class, 'loginUsingToken']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/platforms', [PlatformController::class, 'index']);
        Route::post('/platforms', [PlatformController::class, 'store']);
        Route::get('/platforms/{platform}', [PlatformController::class, 'show']);
        Route::post('/platforms/{platform}', [PlatformController::class, 'updatePlatform']);
        Route::post('/platforms/{platform}/users', [PlatformController::class, 'addUserToPlatform']);
        Route::post('/platforms/{platform}/users/{user}/role', [PlatformController::class, 'assignUserRoleToPlatform']);
        Route::post('/platforms/{platform}/users/{user}/unassign-role', [PlatformController::class, 'unAssignUserRoleToPlatform']);
        Route::get('/platforms/{platform}/users', [PlatformController::class, 'getUsersInPlatform']);
        Route::get('/platforms/{platform}/users/{user}', [PlatformController::class, 'getUserInPlatform']);
        Route::post('/platforms/{platform}/users/{user}/remove', [PlatformController::class, 'removeUserFromPlatform']);
        Route::get('/platforms/{platform}/users/{user}/role', [PlatformController::class, 'getUserRoleInPlatform']);
        Route::get('/platforms/{platform}/users/{user}/permissions', [PlatformController::class, 'getUserPermissionsInPlatform']);

    });

// Email verification route
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');

// Resend the verification email if the user hasn’t verified yet
    Route::post('/email/resend', [VerificationController::class, 'resend'])->middleware('auth:sanctum')->name('verification.resend');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/user/data', [HomeController::class, 'user']);

        Route::resource('profile',ProfileController::class);

        Route::post('/profile/update-picture',ProfileController::class,'updateProfilePicture');
        Route::post('/profile/{id}/documents',[ProfileController::class,'uploadUserDocuments']);
        Route::post('/profile/{id}/relevant-documents',[ProfileController::class,'uploadRelevantDocuments']);
        // generate login token for retailers
        Route::post('/login/generate-token', [UserController::class, 'generateLoginToken']);

        Route::post('/logout', [AuthenticatedSessionController::class, 'logout'])->name('user.logout');

        // Social Authentication Routes (Protected)
        Route::post('/auth/{provider}/link', [SocialAuthController::class, 'linkSocialAccount']);
        Route::delete('/auth/{provider}/unlink', [SocialAuthController::class, 'unlinkSocialAccount']);
        Route::get('/auth/linked-accounts', [SocialAuthController::class, 'getLinkedAccounts']);

        //office

        Route::resource('offices', OfficeController::class);
        Route::post('offices/simple', [OfficeController::class, 'createSimpleOffice']);
        Route::post('offices/{office}/payment-preference', [OfficeController::class, 'paymentPreference']);
        Route::post('/offices/documents/upload', [OfficeController::class, 'uploadOfficeDocuments']);
        Route::post('/offices/documents/relevant', [OfficeController::class, 'uploadRelevantDocuments']);
        Route::post('/offices/profile/upload', [OfficeController::class, 'updateOfficeProfile']);

        //retails

        Route::resource('retails', RetailController::class);
        Route::post('/retails/simple', [RetailController::class, "createSimpleRetail"]);
        Route::post('retails/{retail}/payment-preference', [RetailController::class, 'paymentPreference']);
        Route::post('/retails/documents/upload', [RetailController::class, 'uploadOfficeDocuments']);
        Route::post('/retails/documents/relevant', [RetailController::class, 'uploadRelevantDocuments']);
        Route::post('/retails/profile/upload', [RetailController::class, 'updateOfficeProfile']);

        // Route::middleware('hasretail')->group(function () {
        Route::middleware('hasaccount')->group(function () {
            Route::resource('retailsessions', SessionAccountController::class);
            // Route::middleware('hassessionretail')->group(function () {
            Route::middleware('hassessionaccount')->group(function () {
                Route::get('/user/data', [HomeController::class, 'user']);
                Route::get('/user/permissions', [HomeController::class, 'permissions']);

                // dashboard analytics
                Route::prefix('analytics')->group(function () {
                    Route::get('/dashboard', [HomeController::class, 'dashboardAnalytics']);
                    Route::get('/dashboard-projects', [HomeController::class, 'dashboardProjects']);
                    Route::get('/dashboard-ecommerce', [HomeController::class, 'dashboardEcommerce']);
                    Route::get('/crm', [HomeController::class, 'dashboardCRM']);
                    Route::get('/dashboard-wallet', [HomeController::class, 'dashboadWallet']);
                });

                Route::prefix('retail-items')->group(function () {
                    Route::resource('/', RetailItemController::class);
                    Route::resource('{retail-item}/stocks', StockController::class);
                    Route::post('/upload-excel', [RetailItemController::class, 'uploadExcel']);
                    Route::post('/upload-image', [RetailItemController::class, 'uploadImage']);
                });

                //Stock
                Route::post('/stocks/reports/generate-pdf', [StockController::class, 'generatePDF']);
                Route::resource('/stocks', StockController::class);

                //sales
                Route::prefix('sales')->group(function () {
                    Route::get('/get-promt-items/{key}', [SaleTerminalController::class, 'getPrompItems']);
                    Route::post('/get-sale-item/{item_id}', [SaleTerminalController::class, 'getSaleItem']);
                    Route::post('/get-sale-item', [SaleTerminalController::class, 'getSaleItem']);

                    Route::get('/{retail_item}/index', [SaleController::class, 'index']);
                    Route::resource('/', SaleController::class);
                });

                //sale transactions
                Route::prefix('sale-transactions')->group(function () {
                    Route::post('/generate-sale-transaction_id', [SaleTransactionController::class, 'generateSaleTransactionId']);
                    Route::get('/{sale-transaction}/{status}', [SaleTransactionController::class, 'edit']);
                    Route::get('/transaction-status/{status}', [SaleTransactionController::class, 'index']);
                    Route::get('/{sale-transaction}/transaction-status/{status}', [SaleTransactionController::class, 'getTransactionWithStatus']);
                    Route::post('/{status}', [SaleTransactionController::class, 'store']);
                    Route::put('/{saletransaction}/close-transaction', [SaleTransactionController::class, 'closeTransaction']);
                    Route::put('/{saletransaction}/close-transaction/{flag}', [SaleTransactionController::class, 'closeTransaction']);
                    Route::resource('/', SaleTransactionController::class);
                });

                //sale transaction payment
                Route::prefix('payments')->group(function () {
                    Route::resource('/sale-transactions/pay-transaction', SalePaymentController::class);
                    Route::post('/sale-transactions/check-for-payment', [SalePaymentController::class, 'checkForPayment']);
                });
                Route::post('/terminal/receipt/print', [ReceiptController::class, 'generateReceipt']);

                // Route::resource('/sale-transactions/payments/{payment-gateway}', SalePaymentController::class);

                ///{sale-transaction}
                //Employee
                Route::post('employees/validate/email', [EmployeeController::class, 'validateAccountExistence']);
                Route::resource('/employees', EmployeeController::class);

                Route::prefix('employees')->group(function () {
                    // employee sales

                    Route::delete('{employee}/sales/delete-sale-item/{sale_item_id}', [EmployeeSaleController::class, 'destroySaleItem']);
                    Route::resource('{employee}/sales', EmployeeSaleController::class);
                    Route::resource('sales/employee-sales', EmployeeSaleController::class);
                    Route::post("{employee_id}/edit/roles/assign", [EmployeeController::class, 'assignEmployeeRole']);
                    Route::post("{employee_id}/edit/roles/unassign/{role_id}", [EmployeeController::class, 'unAssignEmployeeRole']);

                    Route::resource('assign/roles', EmployeeController::class);

                });

                //items on credit
                Route::resource('/credit-items', CreditItemController::class);

                //customers
                Route::resource('/customers', CustomerController::class);
                Route::post('/customers/{customer}/credits/{credit}/invoice', [CustomerCreditController::class, 'invoice']);
                Route::resource('/customers/{transaction_id}/credits', CustomerCreditController::class);

                //paid items
                Route::resource('/paiditems-sales', PaidItemController::class);

                //orders
                Route::prefix('orders')->group(function () {
                    Route::resource('/', OrderController::class);
                    Route::resource('/delivered', OrderDeliveredController::class);
                    Route::resource('/pending', OrderPendingController::class);
                });

                Route::post('/required-items/order', [RequiredItemController::class, 'order']);
                Route::resource('/required-items', RequiredItemController::class);

                Route::resource('/supplies', SupplyController::class);

                Route::prefix('market')->group(function () {
                    // employee sales
                    Route::resource('/market-items', MarketController::class);
                    Route::resource('/checkouts', MarketController::class);
                });

                Route::resource('/notifications', NotificationController::class);

                //messages
                Route::resource('/messages', MessageController::class);
                Route::post('/messages/{message}', [MessageController::class, 'update']);
                Route::post('/messages/tenant', [MessageController::class, 'messageTenant']);

                Route::resource('/roles', RoleController::class);
                Route::resource('/roles', RoleController::class);
                Route::post('/roles/{role}/assign/employee/{employee}', [AssignEmployeeRole::class, 'assignEmployeeRole']);
                Route::post('/roles/{role}/unassign/employee/{employee}', [AssignEmployeeRole::class, 'unAssignEmployeeRole']);
                Route::prefix('account')->group(function () {
                    Route::get('/users', [AccountController::class, 'index']);
                    Route::post('/users/{user_id}', [MessageController::class, 'update']);
                });

                Route::prefix('users')->group(function () {
                    Route::post('profiles/profile-picture/update', [ProfileController::class, 'updateProfilePicture']);
                    Route::resource('profiles', ProfileController::class);
                    Route::post('account/password/update', [AccountController::class, 'updatePassword']);
                });

                Route::prefix('ecommerce')->group(function () {
                    Route::get('/data', [EcommerceController::class, 'ecommerceData']);
                    Route::post('/register/validate-user', [EcommerceController::class, 'validateUserRequest']);
                    Route::post('/register', [EcommerceController::class, 'registerEcommerceShop']);
                    Route::get('/products', [EcommerceProductController::class, 'getEcommerceProducts']);
                    Route::get('/payment/gateways/create', [EcommerceController::class, 'getCreatePaymentGatewaysData']);
                    Route::get('/payment/gateways', [EcommerceController::class, 'getPaymentGateways']);
                    Route::post('/payment/gateways', [EcommerceController::class, 'savePaymentGateways']);

                    Route::get('/payment/gateways/{payment_method_id}', [EcommerceController::class, 'getPaymentGateway']);
                    Route::get('/payment/gateways/{payment_method_id}/edit', [EcommerceController::class, 'editPaymentGateways']);
                    Route::put('/payment/gateways/update/{payment_method_id}', [EcommerceController::class, 'updatePaymentGateways']);
                    Route::post('/payment/gateways/update/{payment_method_id}', [EcommerceController::class, 'updatePaymentGateways']);

                    Route::delete('/payment/gateways/{payment_method_id}', [EcommerceController::class, 'deletePaymentGateways']);

                    Route::get('/products/{product}', [EcommerceProductController::class, 'getEcommerceProduct']);
                    Route::post('/settings/save', [EcommerceSettingController::class, 'saveEcommerceSettings']);
                });

            });

            Route::prefix('receipts')->group(function () {
                Route::get('/print-from-terminal', [ReceiptController::class, 'printReceiptFromTerminal']);
                Route::get('/pdf/{sale_transaction_id}', [ReceiptController::class, 'generateReceiptPDF']);
                Route::get('/print', [ReceiptController::class, 'printReceipt']);
            });

            //mpesa routes
            Route::prefix('mpesa')->group(function () {
                Route::post('/validation/{retail_id}', [MpesaResponseController::class, 'validation']);
                Route::post('/confirmation/{retail_id}', [MpesaResponseController::class, 'confirmation']);

                Route::post('/validation', [MpesaResponseController::class, 'validation']);

                Route::post('simulate', [MpesaResponseController::class, 'validation']);
                Route::post('/stkpush/{code}', [MpesaResponseController::class, 'stkPushResponse']);
                Route::post('/stkpush/{user_id}/{trans_id}', [MpesaResponseController::class, 'stkPushResponse']);
                Route::post('/stkpush', [MpesaResponseController::class, 'stkPushResponse']);
                Route::post('reverse', [MpesaResponseController::class, 'reversal']);

                Route::post('/query/result/{id}', [MpesaResponseController::class, 'queryResult']);
                Route::post('/query/confirmation/{id}', [MpesaResponseController::class, 'queryConfirmation']);

            });

            //comments
            Route::resource('comments', CommentController::class);

            //media

            Route::resource('media', MediumController::class);

            //project

            Route::resource('projects', ProjectController::class);
            Route::get('/projects/user/{user_id}', [ProjectController::class, 'projectsForUser']);
            Route::post('/projects/{project}/change-priority', [ProjectController::class, 'changePriority']);
            Route::post('/projects/{project}/comments', [ProjectController::class, 'addComment']);
            Route::put('/projects/{project}/comments/{comment}', [ProjectController::class, 'updateComment']);
            Route::delete('/projects/{project}/comments/{comment}', [ProjectController::class, 'deleteComment']);

            //campaigns
            Route::resource('campaigns', CampaignController::class);
            Route::post('/campaigns/{campaign_id}/teams/members', [CampaignController::class, "addMemberToCampaignTeam"]);
            Route::post('/campaigns/{campaign_id}/leads/add', [CampaignController::class, "addLeadsToCampaign"]);

            //teams
            Route::resource('teams', TeamController::class);

            //accounts
            Route::resource('accounts', AccountsController::class);

            //accounts
            Route::resource('calendars', CalendarController::class);

            // Enhanced Calendar Routes
            Route::post('calendars/create-from-task/{task_id}', [CalendarController::class, 'createFromTask']);
            Route::put('calendars/{calendar}/reschedule', [CalendarController::class, 'reschedule']);
            Route::put('calendars/{calendar}/resize', [CalendarController::class, 'resize']);
            Route::post('calendars/bulk-update', [CalendarController::class, 'bulkUpdate']);
            Route::post('calendars/bulk-delete', [CalendarController::class, 'bulkDelete']);
            Route::put('calendars/{calendar}/attendees/{user_id}/status', [CalendarController::class, 'updateAttendeeStatus']);
            Route::post('calendars/check-conflicts', [CalendarController::class, 'checkConflicts']);

            //leads
            Route::resource('leads', LeadController::class);
            //add leads to a campaign
            Route::post('/campaign/{campaign_id}/leads', [LeadController::class, "addLeadsToCampaign"]);

            //Sale Settings

            Route::resource('sale-settings', SaleSettingController::class);

            //session account

            Route::resource('session-accounts', SessionAccountController::class);

            //task dependancy
            Route::resource('task-dependencies', TaskDependancyController::class);

            // task
            Route::resource('tasks', TaskController::class);
            Route::post('tasks/{task_id}/change-status', [TaskController::class, 'changeStatus']);
            Route::post('tasks/{task_id}/comments', [TaskController::class, 'addComment']);
            Route::put('tasks/{task_id}/comments/{comment_id}', [TaskController::class, 'updateComment']);
            Route::delete('tasks/{task_id}/comments/{comment_id}', [TaskController::class, 'deleteComment']);
            Route::post('tasks/{taskId}/convert-to-todo', [TaskController::class, 'convertToTodo']);
            Route::post('tasks/{taskId}/break-into-todos', [TaskController::class, 'breakIntoTodos']);
            Route::post('tasks/{taskId}/create-calendar-from-task', [TaskController::class, 'createCalendarFromTask']);
            Route::post('tasks/{taskId}/convert-subtasks-to-todos', [TaskController::class, 'convertSubtasksToTodos']);

            // assign tasks
            Route::post('tasks/{task_id}/assign', [TaskController::class, 'assignTask']);

            // wallets
            Route::resource('wallets', WalletController::class);

            Route::prefix('kanban')->group(function () {
                Route::resource('projects/{project_id}/tasks', KanbanController::class);
                Route::put('projects/{project_id}/tasks/update-positions', [KanbanController::class, 'updateKanbanboard']);
                Route::put('projects/tasks/update-positions', [KanbanController::class, 'updateKanbanboard']);
            });

            //todo
            Route::resource('todos', TodoController::class);
            Route::delete('todos/delete/{all}', [TodoController::class, 'deleteAll']);
            Route::get('todos/{type}', [TodoController::class, 'index']);
            Route::get('todos/create/{type}', [TodoController::class, 'create']);
            Route::get('todos/{todo}/{type}', [TodoController::class, 'show']);
            Route::put('todos/{todo}/edit/{type}', [TodoController::class, 'edit']);
            Route::put('todos/update/{todo}/{type}', [TodoController::class, 'update']);

            // Video Calls
            Route::prefix('video-calls')->group(function () {
                Route::post('/', [VideoCallController::class, 'createRoom']);
                Route::post('/{roomId}/join', [VideoCallController::class, 'joinRoom']);
                Route::post('/{roomId}/leave', [VideoCallController::class, 'leaveRoom']);
                Route::get('/{roomId}', [VideoCallController::class, 'getRoom']);
                Route::get('/{roomId}/participants', [VideoCallController::class, 'getParticipants']);
                Route::post('/{roomId}/messages', [VideoCallController::class, 'sendMessage']);
                Route::get('/{roomId}/messages', [VideoCallController::class, 'getMessages']);
            });

            // Jobs and Applications
            Route::resource('jobs', JobController::class);
            Route::post('/jobs/{job}/apply', [JobController::class, 'apply']);
            Route::get('/jobs/{job}/applications', [JobController::class, 'getApplications']);
            Route::put('/jobs/applications/{application}/status', [JobController::class, 'updateApplicationStatus']);
            Route::post('/jobs/applications/{application}/select-interview-date', [JobController::class, 'selectInterviewDate']);
            Route::get('/my-job-applications', [JobController::class, 'myApplications']);

            // /vendors/456/ecommerce/products   ecommerce-vendor-middleware

            Route::prefix('vendors')->middleware('ecommerce-vendor-middleware')->group(function () {
                Route::prefix('/{vendor_id}/ecommerce')->group(function () {
                    Route::get('/data', [EcommerceVendorController::class, 'ecommerceData']);
                    Route::post('/register/validate-user', [EcommerceController::class, 'validateUserRequest']);
                    Route::post('/register', [EcommerceController::class, 'registerEcommerceShop']);
                    Route::get('/products', [EcommerceProductController::class, 'getEcommerceProducts']);
                    Route::get('/products/{product}', [EcommerceProductController::class, 'getEcommerceProduct']);
                });
            });

            Route::prefix('ecommerce')->group(
                function () {
                    Route::get('/data', [EcommerceController::class, 'ecommerceData']);
                    Route::post('/register/validate-user', [EcommerceController::class, 'validateUserRequest']);
                    Route::post('/register', [EcommerceController::class, 'registerEcommerceShop']);
                    Route::get('/products', [EcommerceProductController::class, 'getEcommerceProducts']);
                    Route::get('/products/{product}', [EcommerceProductController::class, 'getEcommerceProduct']);
                    Route::get('/payment/gateways/create', [EcommerceController::class, 'getCreatePaymentGatewaysData']);
                    Route::get('/payment/gateways', [EcommerceController::class, 'getPaymentGateways']);
                    Route::post('/payment/gateways', [EcommerceController::class, 'savePaymentGateways']);
                    Route::get('/payment/gateways/{payment_method_id}', [EcommerceController::class, 'getPaymentGateway']);
                    Route::get('/payment/gateways/{payment_method_id}/edit', [EcommerceController::class, 'editPaymentGateways']);
                    Route::put('/payment/gateways/update/{payment_method_id}', [EcommerceController::class, 'updatePaymentGateways']);
                    Route::post('/payment/gateways/update/{payment_method_id}', [EcommerceController::class, 'updatePaymentGateways']);
                    Route::delete('/payment/gateways/{payment_method_id}', [EcommerceController::class, 'deletePaymentGateways']);
                }
            );

            //mpesa callback routes

            Route::prefix('mpesa')->group(function () {
                Route::post('/stkpush/{code}', [MpesaResponseController::class, 'stkPushResponse']);
                Route::post('/stkpush/{user_id}/{trans_id}', [MpesaResponseController::class, 'stkPushResponse']);
                Route::post('/stkpush/retail/{user_id}/{trans_id}', [MpesaResponseController::class, 'retailSTKPushResponse']);

                Route::post('/stkpush', [MpesaResponseController::class, 'stkPushResponse']);
                Route::post('reverse', [MpesaResponseController::class, 'reversal']);

                Route::post('/query/confirmation/{id}', [MpesaResponseController::class, 'queryConfirmation']);

            });

            //admin

            Route::prefix('admin')->group(function () {

                //tiers
                Route::prefix('tiers')->group(function () {
                    Route::resource('/', TierController::class);
                    Route::resource('/{tier}/permissions', [PermissionTierController::class]);
                });

                //email management
                Route::prefix('emails')->group(function () {
                    Route::get('/configs', [EmailController::class, 'getConfigs']);
                    Route::post('/configs', [EmailController::class, 'storeConfig']);
                    Route::put('/configs/{config}', [EmailController::class, 'updateConfig']);
                    Route::get('/notifications', [EmailController::class, 'getNotifications']);
                    Route::post('/send', [EmailController::class, 'sendEmail']);
                    Route::put('/notifications/{notification}/processed', [EmailController::class, 'markProcessed']);
                });

            });

        });

// Route::post('send-fcm-token', [FcmCloudMessagingController::class, 'firebaseTokenStorage']);
// Route::post('get-fcm-token', [FcmCloudMessagingController::class, 'firebaseTokenRetrieve']);
// Route::post('make-notification', [FcmCloudMessagingController::class, 'makeNotification']);
// Route::get('curl_download', [FcmCloudMessagingController::class, 'curldownload']);
// Route::post('make-updateToken', [FcmCloudMessagingController::class, 'updateToken']);
// Route::post('sendNotification', [FcmCloudMessagingController::class, 'sendNotification']);
// Route::post('delete-tokendata', [FcmCloudMessagingController::class, 'deleterecords']);

// payments
        Route::prefix('payments')->group(function () {
            Route::get('/gateways', [PaymentController::class, 'ecommercePaymentGatewaysAvailable']);
            Route::post('/gateways', [PaymentController::class, 'setEcommercePaymentGateways']);
            Route::delete('/gateways', [PaymentController::class, 'removeEcommercePaymentGateways']);
            Route::delete('/gateways/clear', [PaymentController::class, 'clearEcommercePaymentGateways']);
            Route::post('/google-pay', [PaymentController::class, 'processGooglePay']);
            Route::post('/paypal', [PaymentController::class, 'processPayPalPayment']);
            Route::post('/paypal/capture', [PaymentController::class, 'capturePayPalPayment']);
            Route::post('/stripe', [PaymentController::class, 'createStripePaymentIntent']);
            Route::post('/stripe/confirm', [PaymentController::class, 'confirmStripePayment']);
            Route::post('/stripe/webhook', [PaymentController::class, 'handleStripeWebhook']);
        });

        Route::middleware('auth:sanctum')->group(function () {

            // Projects Routes
            Route::resource('projects', ProjectController::class);
            Route::post('projects/{project}/change-priority', [ProjectController::class, 'changePriority']);
            Route::post('projects/{project}/comments', [ProjectController::class, 'addComment']);
            Route::put('projects/{project}/comments/{comment}', [ProjectController::class, 'updateComment']);
            Route::delete('projects/{project}/comments/{comment}', [ProjectController::class, 'deleteComment']);

            // Campaigns Routes
            Route::resource('campaigns', CampaignController::class);
            Route::post('campaigns/{campaign_id}/teams/members', [CampaignController::class, 'addMemberToCampaignTeam']);

            // Tasks Routes
            Route::resource('tasks', TaskController::class);
            Route::post('tasks/{task_id}/assign', [TaskController::class, 'assignTask']);
            Route::post('tasks/{taskId}/convert-to-todo', [TaskController::class, 'convertTaskToTodo']);
            Route::post('tasks/{taskId}/break-into-todos', [TaskController::class, 'breakTaskIntoTodos']);
            Route::post('tasks/{taskId}/convert-subtasks-to-todos', [TaskController::class, 'convertSubtasksToTodos']);

            // Task Dependencies Routes
            Route::resource('task-dependencies', TaskDependancyController::class);

            // Todos Routes
            Route::resource('todos', TodoController::class);
            Route::get('todos/{type}', [TodoController::class, 'index']);
            Route::get('todos/create/{type}', [TodoController::class, 'create']);
            Route::get('todos/{todo}/{type}', [TodoController::class, 'show']);
            Route::put('todos/{todo}/edit/{type}', [TodoController::class, 'edit']);
            Route::put('todos/update/{todo}/{type}', [TodoController::class, 'update']);
            Route::delete('todos/{todo}/{type}', [TodoController::class, 'destroy']);
            Route::delete('todos/delete/{all}', [TodoController::class, 'deleteAll']);

            // Kanban Routes
            Route::resource('kanban/projects/{project_id}/tasks', KanbanController::class);
            Route::get('kanban/{project_id}', [KanbanController::class, 'kanban']);
            Route::put('kanban/{project_id}/update', [KanbanController::class, 'update']);
            Route::put('kanban/projects/{project_id}/tasks/update-positions', [KanbanController::class, 'updateKanbanboard']);
            Route::put('kanban/projects/tasks/update-positions', [KanbanController::class, 'updateKanbanboard']);

            // Calendar Routes
            Route::resource('calendars', CalendarController::class);
            Route::post('calendars/create-from-task/{task_id}', [CalendarController::class, 'createFromTask']);
            Route::put('calendars/{calendar}/reschedule', [CalendarController::class, 'reschedule']);
            Route::put('calendars/{calendar}/resize', [CalendarController::class, 'resize']);
            Route::post('calendars/bulk-update', [CalendarController::class, 'bulkUpdate']);
            Route::post('calendars/bulk-delete', [CalendarController::class, 'bulkDelete']);
            Route::put('calendars/{calendar}/attendees/{user_id}/status', [CalendarController::class, 'updateAttendeeStatus']);
            Route::post('calendars/check-conflicts', [CalendarController::class, 'checkConflicts']);

            // Leads Routes
            Route::resource('leads', LeadController::class);
            Route::post('campaign/{campaign_id}/leads', [LeadController::class, 'addLeadsToCampaign']);

            // Subscriptions Routes
            Route::resource('subscriptions', SubscriptionController::class);

            // Coupons Routes
            Route::resource('coupons', CouponController::class);

        });
    });
});
