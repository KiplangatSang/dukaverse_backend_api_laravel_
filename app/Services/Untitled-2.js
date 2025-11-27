  Route::prefix('mobile')->middleware('auth.api')->group(function () {
            Route::post('/logout', [AuthenticatedSessionController::class, 'logout']);
            Route::get('/auth/user', [HomeController::class, 'show']);

            Route::post('/retails/simple', [RetailController::class, "createSimpleRetail"]);
            Route::resource('retails', RetailController::class);

            Route::middleware('auth.api', 'hasretail')->group(function () {
                Route::resource('retailsessions', SessionAccountController::class);
                Route::middleware('hassessionretail')->group(function () {
                    Route::get('/user/data', [HomeController::class, 'index']);

                    Route::prefix('retail-items')->group(function () {
                        Route::resource('/', RetailItemController::class);
                        Route::resource('{retail-item}/stocks', StockController::class);
                        Route::post('/upload-excel', [RetailItemController::class,'uploadExcel']);
                        Route::post('/upload-image', [RetailItemController::class,'uploadImage']);
                    });

                    //Stock
                    // Route::resource('/stocks', StockController::class);

                    //sales
                    Route::get('/sales/get-promt-items/{key}', [SaleTerminalController::class, 'getPrompItems']);
                    Route::post('/sales/get-sale-item/{item_id}', [SaleTerminalController::class, 'getSaleItem']);
                    Route::post('/sales/get-sale-item', [SaleTerminalController::class, 'getSaleItem']);

                    Route::get('/sales/{retail_item}/index', [SaleController::class, 'index']);
                    Route::resource('/sales', SaleController::class);

                    //sale transactions
                    Route::post('sale-transactions/generate-sale-transaction_id', [SaleTransactionController::class, 'generateSaleTransactionId']);
                    Route::get('sale-transactions/{sale-transaction}/{status}', [SaleTransactionController::class, 'edit']);
                    Route::get('sale-transactions/transaction-status/{status}', [SaleTransactionController::class, 'index']);
                    Route::get('sale-transactions/{sale-transaction}/transaction-status/{status}', [SaleTransactionController::class, 'getTransactionWithStatus']);
                    Route::post('sale-transactions/{status}', [SaleTransactionController::class, 'store']);
                    Route::put('sale-transactions/{saletransaction}/close-transaction', [SaleTransactionController::class, 'closeTransaction']);
                    Route::put('sale-transactions/{saletransaction}/close-transaction/{flag}', [SaleTransactionController::class, 'closeTransaction']);
                    Route::resource('sale-transactions', SaleTransactionController::class);

                    //sale transaction payment
                    Route::resource('payments/sale-transactions/pay-transaction', SalePaymentController::class);
                    Route::post('payments/sale-transactions/check-for-payment', [SalePaymentController::class, 'checkForPayment']);

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
                    Route::resource('/orders', OrderController::class);

                    Route::post('/required-items/order', [RequiredItemController::class, 'order']);
                    Route::resource('/required-items', RequiredItemController::class);

                    Route::resource('/supplies', SupplyController::class);

                    Route::prefix('market')->group(function () {
                        // employee sales
                        Route::resource('/market-items', MarketController::class);
                        Route::resource('/checkouts', MarketController::class);
                    });

                    Route::resource('/notifications', NotificationController::class);
                    Route::resource('/messages', MessageController::class);
                    Route::post('/messages/{message}', [MessageController::class, 'update']);

                    Route::resource('/roles', RoleController::class);
                     Route::post('/roles/{role}/assign/employee/{employee}', [AssignEmployeeRole::class, 'assignEmployeeRole']);
                    Route::post('/roles/{role}/unassign/employee/{employee}', [AssignEmployeeRole::class, 'unAssignEmployeeRole']);
                });
            });

            //mpesa routes
            Route::prefix('mpesa')->group(function () {
                Route::post('/validation/{retail_id}', [MpesaResponseController::class, 'validation']);
                Route::post('/confirmation/{retail_id}', [MpesaResponseController::class, 'confirmation']);

                Route::post('/validation', [MpesaResponseController::class, 'validation']);

                Route::post('simulate', [MpesaResponseController::class, 'validation']);
                Route::post('/stkpush/{code}', [MpesaResponseController::class, 'stkPushResponse']);
                Route::post('/stkpush/{user_id}/{trans_id}', [MpesaResponseController::class, 'retailSTKPushResponse']);
                Route::post('/stkpush/{user_id}/{trans_id}', [MpesaResponseController::class, 'retail']);
                Route::post('/stkpush', [MpesaResponseController::class, 'stkPushResponse']);
                Route::post('reverse', [MpesaResponseController::class, 'reversal']);

                Route::post('/query/result/{id}', [MpesaResponseController::class, 'queryResult']);
                Route::post('/query/confirmation/{id}', [MpesaResponseController::class, 'queryConfirmation']);

            });
        });