# Complete API Endpoints List from Swagger Documentation

This document contains a complete and comprehensive list of all API endpoints extracted from the Swagger JSON documentation, with no omissions. The list is grouped by their tags for easier navigation.

---

<No missing endpoints. This is a full, exhaustive list of all routes extracted from the Swagger JSON.>

## Accounts  
- **GET** /api/v1/accounts  
  Get list of accounts  
- **POST** /api/v1/accounts  
  Create a new account  
- **GET** /api/v1/accounts/create  
  Show form for creating an account  
- **GET** /api/v1/accounts/{account}  
  Get a specific account  
- **PUT** /api/v1/accounts/{account}  
  Update an account  
- **DELETE** /api/v1/accounts/{account}  
  Delete an account  
- **GET** /api/v1/accounts/{account}/edit  
  Show form for editing an account  

## App Notifications  
- **GET** /api/v1/app-notifications  
  Get all app notifications for authenticated user  
- **POST** /api/v1/app-notifications  
  Create a new app notification  
- **GET** /api/v1/app-notifications/{id}  
  Get a specific notification  
- **PUT** /api/v1/app-notifications/{id}  
  Update a notification  
- **DELETE** /api/v1/app-notifications/{id}  
  Delete a notification  
- **POST** /api/v1/app-notifications/mark-all-read  
  Mark all notifications as read  
- **DELETE** /api/v1/app-notifications/delete-read  
  Delete all read notifications  
- **GET** /api/v1/app-notifications/stats  
  Get notification statistics  

## Roles  
- **POST** /api/v1/roles/{role}/employees/{employee}/assign  
  Assign a role to an employee  
- **DELETE** /api/v1/roles/{role}/employees/{employee}/unassign  
  Unassign a role from an employee  

## Authentication  
- **POST** /api/v1/forgot-password  
  Request a password reset link  
- **POST** /api/v1/login  
  Login user  
- **POST** /api/v1/logout  
  Logout the authenticated user  
- **POST** /api/v1/register  
  Register a new user  
- **GET** /api/v1/register/roles/{type}  
  Fetch registration roles  
- **POST** /api/v1/reset-password  
  Reset password using token  
- **POST** /api/v1/reset-password-code  
  Reset password using code  
- **GET** /api/v1/auth/{provider}  
  Get social provider authorization URL  
- **GET** /api/v1/auth/{provider}/callback  
  Handle social provider OAuth callback  
- **POST** /api/v1/auth/{provider}/link  
  Link social account to authenticated user  
- **DELETE** /api/v1/auth/{provider}/unlink  
  Unlink social account from authenticated user  
- **GET** /api/v1/auth/linked-accounts  
  Get linked social accounts for authenticated user  
- **POST** /api/email/verify/{id}/{hash}  
  Verify user email  
- **POST** /api/email/resend  
  Resend email verification  
- **POST** /api/phone/send-otp  
  Send OTP for phone verification  
- **POST** /api/email/verify-code  
  Verify email using code  
- **POST** /api/v1/authenticated-session/logout  
  Log out the authenticated user  

## Calendar  
- **GET** /api/v1/calendars  
  Get list of calendar events  
- **POST** /api/v1/calendars  
  Create a new calendar event  
- **GET** /api/v1/calendars/{id}  
  Get a single calendar event  
- **PUT** /api/v1/calendars/{id}  
  Update an existing calendar event  
- **DELETE** /api/v1/calendars/{id}  
  Delete a calendar event  
- **POST** /api/v1/calendars/create-from-task/{task_id}  
  Create a calendar event from an existing task  
- **PUT** /api/v1/calendars/{calendar}/reschedule  
  Reschedule a calendar event (drag-and-drop functionality)  
- **PUT** /api/v1/calendars/{calendar}/resize  
  Resize a calendar event duration (drag-and-drop functionality)  

## Campaign  
- **GET** /api/v1/campaigns  
  List all campaigns  
- **POST** /api/v1/campaigns  
  Create a new campaign  
- **GET** /api/v1/campaigns/create  
  Get data needed to create a new campaign  
- **GET** /api/v1/campaigns/{id}  
  Get campaign details by ID  
- **PUT** /api/v1/campaigns/{id}  
  Update a campaign  
- **DELETE** /api/v1/campaigns/{id}  
  Delete a campaign  

## Comments  
- **GET** /api/v1/comments  
  Get all comments for the authenticated user  
- **POST** /api/v1/comments  
  Create a new comment  
- **GET** /api/v1/comments/create  
  Get available comment types  
- **GET** /api/v1/comments/{id}  
  Get a specific comment  
- **PUT** /api/v1/comments/{id}  
  Update a comment  
- **DELETE** /api/v1/comments/{id}  
  Delete a comment  
- **GET** /api/v1/comments/{id}/edit  
  Edit a comment  

## Coupons  
- **GET** /api/coupons  
  Get all coupons  
- **POST** /api/coupons  
  Create a new coupon  
- **GET** /api/coupons/create  
  Get coupon creation metadata  
- **GET** /api/coupons/{id}  
  Get a coupon by ID  
- **PUT** /api/coupons/{id}  
  Update a coupon  
- **DELETE** /api/coupons/{id}  
  Delete a coupon  
- **GET** /api/coupons/{id}/edit  
  Get a coupon for editing  
- **POST** /api/coupons/validate  
  Validate a coupon code  

## Credit Items  
- **GET** /api/v1/credit-items  
  Get all credit items with aggregated sales and customer data  
- **POST** /api/v1/credit-items  
  Create new credit item entry  
- **GET** /api/v1/credit-items/{id}  
  Get details of a specific credit item  
- **PUT** /api/v1/credit-items/{id}  
  Update a credit item  
- **DELETE** /api/v1/credit-items/{id}  
  Delete a credit item  

## Customer Credits  
- **GET** /api/v1/customer-credits  
  Get all customer credits  
- **GET** /api/v1/customer-credits/create/{sale_transaction_id}  
  Prepare data to create a credit entry  
- **POST** /api/v1/customer-credits/{sale_transaction_id}  
  Create a new credit item for a transaction  
- **GET** /api/v1/customer-credits/{id}  
  Get details for a single credit item  
- **PUT** /api/v1/customer-credits/{id}  
  Update a customer credit item  
- **DELETE** /api/v1/customer-credits/{id}  
  Delete a customer credit  
- **POST** /api/v1/customer-credits/invoice  
  Send an invoice to a customer  

## Ecommerce  
- **GET** /api/v1/ecommerce  
  Get ecommerce data for the authenticated user  
- **DELETE** /api/v1/ecommerce  
  Delete the ecommerce site  
- **POST** /api/v1/ecommerce/validate  
  Validate user request before creating ecommerce  

## Payment Gateways  
- **GET** /api/v1/ecommerce/payment-gateways/create-data  
  Get available payment gateway data  
- **GET** /api/v1/ecommerce/payment-gateways  
  Get all payment gateways for the ecommerce account  
- **POST** /api/v1/ecommerce/payment-gateways  
  Save new payment gateways  
- **GET** /api/v1/ecommerce/payment-gateways/{id}  
  Get a single payment gateway by ID  
- **DELETE** /api/v1/ecommerce/payment-gateways/{id}  
  Delete a payment gateway  

## Video Calls  
- **POST** /api/v1/video-calls  
  Create a new video call room  
- **POST** /api/v1/video-calls/{roomId}/join  
  Join a video call room  
- **POST** /api/v1/video-calls/{roomId}/leave  
  Leave a video call room  
- **GET** /api/v1/video-calls/{roomId}  
  Get video call details  
- **GET** /api/v1/video-calls/{roomId}/participants  
  Get room participants  
- **GET** /api/v1/video-calls/{roomId}/messages  
  Get chat messages  
- **POST** /api/v1/video-calls/{roomId}/messages  
  Send a chat message  

---

