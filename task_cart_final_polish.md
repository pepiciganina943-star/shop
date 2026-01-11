# Task: Shopping Cart Enhancement & Checkout System

**Current State:** Basic cart table exists but lacks functionality and images.
**Goal:** Add quantity controls, fix images, and implement a protected checkout with email notifications.

**EXECUTION PLAN:**

### 1. Fix Cart Display & Images (`templates/cart/index.html.twig`)
* **Product Images:** Update the `<img>` tag to use the correct path. If the product has a collection of images, take the first one.
* **Quantity Controls:** Replace the static number with an input group:
    * `[-]` Button (Decrease)
    * `Number Input` (ReadOnly)
    * `[+]` Button (Increase)
* **AJAX Logic:** Use JavaScript to update quantities without refreshing the whole page.

### 2. Checkout Form & Logic (`src/Controller/CheckoutController.php`)
* **Security:** Use `#[IsGranted('ROLE_USER')]`.
* **Form:** Create a `CheckoutType` form with:
    * `Full Name` (Text)
    * `Phone Number` (Tel)
    * `Shipping Address` (Textarea)
    * *Note: Email should be pulled automatically from `$this->getUser()->getEmail()`.*
* **Order Processing:**
    1. Save the `Order` and `OrderItems` to the database.
    2. Calculate final total.
    3. Clear the session cart.

### 3. SMTP Email Notifications
* **Trigger:** After successful database flush.
* **Email 1 (To Customer):** Use the user's registered email. Subject: "Потвърждение на поръчка #ID".
* **Email 2 (To Admin):** Send to `asem4o@gmail.com`. Subject: "Нова поръчка от ${customer_name}".
* **Content:** Include a table with products, quantities, and total price.

### 4. Fix Product Prices (Multiplier issue)
* Update `ProductCrudController.php`: ensure `MoneyField` uses `->setStoredAsCents(false)` to fix the `22200.00 лв` error seen on the home page.

**Deliverables:**
1. Updated `CartController` (for quantity updates).
2. `CheckoutController` and its Twig template.
3. Updated `Cart` template with quantity buttons and working images.
4. Email Twig templates.