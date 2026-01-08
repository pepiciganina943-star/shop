# Task: Implement Checkout, Access Control & Email Notifications

**Goal:**
Turn the current session-based cart into a real order system.
1.  **Restrict Checkout:** Only logged-in users can place orders.
2.  **Save Orders:** Persist order details in the database.
3.  **Send Emails:** Notify both the customer and the admin (`asem4o@gmail.com`) upon successful order.

**EXECUTION PLAN:**

### 1. Database Entities (Create New)
* **`Order` Entity:**
    * `user` (ManyToOne to `User`).
    * `createdAt` (datetime_immutable).
    * `totalPrice` (decimal).
    * `status` (string, e.g., 'PENDING').
    * `items` (OneToMany to `OrderItem`).
    * `shippingAddress` (text).
* **`OrderItem` Entity:**
    * `orderRef` (ManyToOne to `Order`).
    * `product` (ManyToOne to `Product`).
    * `quantity` (integer).
    * `price` (decimal) - store the price at the moment of purchase.

### 2. Checkout Controller (`src/Controller/CheckoutController.php`)
* **Route:** `#[Route('/checkout', name: 'app_checkout')]`
* **Security:** Add attribute `#[IsGranted('ROLE_USER')]`. If a guest tries to access it, they should be redirected to Login.
* **Logic (`placeOrder` method):**
    1.  Get the current User (`$this->getUser()`).
    2.  Get cart items from Session. If empty, redirect to cart.
    3.  Create new `Order` entity and populate it with `OrderItem`s based on the cart.
    4.  Calculate total price.
    5.  Persist to Database via EntityManager.
    6.  **Send Emails (Symfony Mailer):**
        * **To Customer:** Use `$user->getEmail()`. Subject: "Поръчка #{id} в Bellamie".
        * **To Admin:** `asem4o@gmail.com`. Subject: "НОВА ПОРЪЧКА #{id}".
    7.  Clear the Session Cart (`cart` variable).
    8.  Redirect to a "Success" page.

### 3. Email Templates (Twig)
* Create `templates/emails/order_confirmation.html.twig` (Beautiful HTML summary for the client).
* Create `templates/emails/admin_order_notification.html.twig` (Simple summary for you).

**Deliverables:**
1.  Code for `Order` and `OrderItem` entities.
2.  Full `CheckoutController` code with Mailer logic.
3.  The Twig templates for the emails.