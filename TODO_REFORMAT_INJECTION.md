# TODO: Refactor Structure for Easy Account Injection into Services and Repositories

## Overview
The current structure makes it difficult to inject the Account (from Helper Account, which could be user retail, office, or ecommerce) into services and repositories. Services are instantiated inside controller methods (e.g., `new TeamService(new TeamsRepository($this->account()))`), which prevents proper dependency injection. We need to refactor to allow services and repositories to be injected into controllers via __construct, with the Account resolved and passed where needed.

## Current Issues
- Services are instantiated in controller methods, not injected.
- Account is resolved dynamically in BaseController methods like `getAccount()`.
- No centralized way to inject Account into services/repositories.
- Tight coupling between controllers and service instantiation.

## Proposed Refactoring Plan

### 1. Update BaseController
- Add a method to resolve and provide the Account instance.
- Ensure Account is available for injection into services.

### 2. Modify Services and Repositories
- Update service constructors to accept Account as a parameter where needed.
- Ensure repositories can receive Account if required.

### 3. Refactor Controllers
- Inject services into controller __construct instead of instantiating in methods.
- Pass the resolved Account to services during injection.
- Remove method-level service instantiation (e.g., `getTeamService()`).

### 4. Use Service Container or Factory
- Implement a factory or use Laravel's service container to resolve services with Account.
- Bind services in service providers with Account resolution.

### 5. Update Specific Controllers
- For each controller (e.g., TeamController, AccountController), replace method instantiation with injected services.
- Ensure AuthService and ApiResource are still injected as per current __construct.

### 6. Test and Verify
- Run tests to ensure Account is properly injected and services work.
- Check that routes and functionality remain intact.

## Steps to Implement
1. [ ] Analyze which services/repositories need Account injection.
2. [ ] Update service constructors to accept Account parameter.
3. [ ] Modify BaseController to provide Account resolution method.
4. [ ] Refactor controller __construct to inject services with Account.
5. [ ] Update controller methods to use injected services.
6. [ ] Test the refactored structure.
7. [ ] Document changes and update any related code.

## Benefits
- Easier testing with mocked services.
- Better separation of concerns.
- Consistent Account injection across the application.
- Reduced coupling in controllers.
