# Task: Implement Order Approval Workflow

**Goal:**
Change the order flow so that new orders are not automatically "confirmed" but set to "PENDING". The Admin must manually approve them in EasyAdmin.

**EXECUTION PLAN:**

### 1. Update `Order` Entity
* Add a `status` column (string, length 50).
* Define constants in the class:
    * `const STATUS_PENDING = 'PENDING';`
    * `const STATUS_APPROVED = 'APPROVED';`
    * `const STATUS_SHIPPED = 'SHIPPED';`
    * `const STATUS_CANCELLED = 'CANCELLED';`
* Set the default value to `self::STATUS_PENDING`.

### 2. Update `CheckoutController`
* When saving a new order, explicitly set the status to `Order::STATUS_PENDING`.
* **Update Customer Email:** Change subject to "Order Received - Awaiting Approval". Body should say "Thank you! We have received your request and will contact you shortly for confirmation."
* **Update Admin Email:** Subject "Action Required: New Order #ID Pending Approval". Include a link to the EasyAdmin order detail page if possible.

### 3. Update `OrderCrudController` (EasyAdmin)
* Add the `status` field to the CRUD form.
* Use a `ChoiceField` for the status so the Admin can select:
    * Pending -> Approved -> Shipped.
* Add a visual badge (`renderAsBadges`) for the status in the index view (e.g., Pending = Yellow, Approved = Green).

**Deliverables:**
1. Code for updated `Order` entity (and migration command).
2. Updated `CheckoutController`.
3. Updated email templates (text changes).
4. Updated `OrderCrudController`.