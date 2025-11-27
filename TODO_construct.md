# TODO: Add __construct to Controllers Extending BaseController

## Overview
Add the following __construct method to all controllers extending BaseController:
```php
public function __construct(
    private readonly AuthService $authService,
    ApiResource $apiResource
) {
    parent::__construct($apiResource);
}
```
Ensure AuthService is imported if not already.

## Controllers to Update
- [x] HomeController.php (modified __construct, resolved analytics_service usage)
- [ ] UserController.php
- [ ] WalletController.php
- [ ] TransactionController.php
- [ ] UserActivityController.php
- [ ] TodoController.php
- [ ] TierController.php
- [ ] TerminalController.php
- [ ] TeamController.php
- [ ] SupplyController.php
- [ ] TaskController.php
- [ ] SupplierController.php
- [ ] SubscriptionController.php
- [ ] StockController.php
- [ ] SessionAccountController.php
- [ ] SaleTransactionController.php
- [ ] SalePaymentController.php
- [ ] SaleController.php
- [ ] RoleController.php
- [ ] RevenueController.php
- [ ] RetailOwnerController.php
- [ ] RetailItemController.php
- [ ] RetailController.php
- [ ] RequiredItemController.php
- [ ] ReceiptController.php
- [ ] ProjectController.php
- [ ] ProfitController.php
- [ ] ProfileController.php
- [ ] PaidItemController.php
- [ ] OrderPendingController.php
- [ ] OrderDeliveredController.php
- [ ] OrderController.php
- [ ] NotificationController.php
- [ ] OfficeController.php
- [ ] MessageController.php
- [ ] MediumController.php
- [ ] LoanApplicationController.php
- [ ] LeadController.php
- [ ] KanbanController.php
- [ ] EmployeeSaleController.php
- [ ] EmployeeController.php
- [ ] EcommerceSettingController.php
- [ ] EcommerceProductController.php
- [ ] EcommerceController.php
- [ ] CustomerCreditController.php
- [ ] CustomerController.php
- [ ] CreditItemController.php
- [ ] CommentController.php
- [ ] BillController.php
- [ ] CampaignController.php
- [ ] AuthenticatedSessionController.php
- [ ] AssignEmployeeRole.php
- [ ] AccountsController.php
- [ ] Auth/VerificationController.php
- [ ] Auth/RegisterController.php
- [ ] AccountController.php
- [ ] Auth/LoginController.php

## Steps
1. For each controller, read the file to check current __construct.
2. If __construct exists, modify it to match the new format.
3. If not, add the new __construct.
4. Add import for AuthService if missing.
5. Mark controller as done in this list.
6. After all, verify syntax.
