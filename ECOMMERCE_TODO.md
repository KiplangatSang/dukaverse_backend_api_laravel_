# Ecommerce Retailer Product Setup - Implementation Todo List

This document outlines the steps to implement and understand how retail ecommerce vendors setup their products in this project.

---

## 1. Retail Ecommerce Vendor Management

- Implement vendor CRUD operations (already exists in `EcommerceVendorController` and `EcommerceVendorService`)
  - List vendors accessible by the user
  - Create a new ecommerce vendor (store)
  - Retrieve vendor details
  - Update vendor details
  - Delete vendor

- Ensure user permission checks using PermissionsService to allow ecommerce management

---

## 2. Retail Ecommerce Product Setup

- Create and manage product entities using the `RetailItem` model which represents individual retail products
  - Configure product attributes: name, description, product images (`product_images` as array), other metadata
  - Associate products with owners/vendors using polymorphic `ownerable()` relation
  - Link products to stocks, sales, required items, supply items, orders, and transactions

- Implement product CRUD interface if not already present
  - Possibly through existing or new controller/service wrapping `RetailItem`
  - Provide API endpoints to create, update, delete products

- Manage media and images associated with products through `media()` polymorphic relationship

---

## 3. Product Stock and Sales Management

- Manage stocks related to retail items (product stock quantities) through `stocks()` relationship
- Track sales and sale transactions through related methods on RetailItem

---

## 4. Ecommerce Product Visibility and Settings

- Use `EcommerceProductsService` and `EcommerceProductController` endpoints to:
  - Toggle showing all retail products on ecommerce site
  - Remove products automatically when stock is low
  - Remove all products from ecommerce site
  - Fetch all products or single product details visible on ecommerce

- Update ecommerce settings for products (using `EcommerceSettingController` and related service)
  - Settings include `show_all_products`, `remove_products_in_low_stock`, `remove_all_products`
  - Ensure the settings are saved correctly linked to the user’s ecommerce account

---

## 5. Frontend Interaction

- Use frontend APIs documented in `frontend-docs/EcommerceVendor.md` as guide to interact with vendor APIs
- Extend or create similar documentation and Vue components to manage products (RetailItem entities), stocks, and visibility settings

---

## 6. Additional Considerations

- Ensure authentication and authorization checking in all product and vendor management flows
- Add validation and error handling for API endpoints related to ecommerce vendor and product management
- Implement tests covering vendor and product CRUD and ecommerce settings interactions (refer to existing `EcommerceVendorApiTest.php` and `EcommerceSettingApiTest.php`)
- Optionally implement advanced product features such as variations, pricing, inventory notifications, etc. according to business needs

---

## Summary

This todo list contains the main steps and components required for retail ecommerce vendors to setup products in the system integrated with ecommerce visibility and stock management features.

The system currently provides vendor management and ecommerce product visibility controls. The product CRUD operations are expected to be implemented using the `RetailItem` model, possibly requiring separate API and frontend components to fully enable product setup for retail ecommerce.

---

# End of Todo List
