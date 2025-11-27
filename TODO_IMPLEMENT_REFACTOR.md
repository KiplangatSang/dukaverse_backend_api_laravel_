# TODO: Implement Refactoring for Easy Account Injection

## Overview
Implement the refactoring plan from TODO_REFORMAT_INJECTION.md to allow proper dependency injection of Account into services and repositories. This will replace method-level service instantiation with constructor injection.

## Information Gathered
- Services like TeamService, AccountService, etc., currently instantiated in controller methods with Account passed.
- BaseController has methods like getAccount() to resolve Account.
- Controllers extend BaseController and need AuthService and ApiResource in __construct.
- Repositories like TeamsRepository, AccountsRepository need Account.

## Plan
1. [ ] Analyze services/repositories needing Account (e.g., TeamService, AccountService).
2. [ ] Update service constructors to accept Account parameter.
3. [ ] Modify BaseController to provide a getAccount() method if not already.
4. [ ] Refactor controllers to inject services in __construct, passing Account.
5. [ ] Update controller methods to use injected services instead of getTeamService().
6. [ ] Test refactored controllers (e.g., TeamController, AccountController).
7. [ ] Document changes.

## Dependent Files to be edited
- app/Http/Controllers/BaseController.php
- app/Services/TeamService.php
- app/Services/AccountService.php
- app/Http/Controllers/TeamController.php
- app/Http/Controllers/AccountController.php
- app/Http/Controllers/AccountsController.php
- Other services/repositories as needed.

## Followup steps
- [ ] Run php artisan route:list to verify routes work.
- [ ] Test specific endpoints for Account injection.
- [ ] Check for syntax errors.

<ask_followup_question>
<question>Do you confirm this implementation plan? Let me know if you have any changes.</question>
</ask_followup_question>
