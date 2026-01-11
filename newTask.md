🚨 **CRITICAL REPAIR REQUIRED** 🚨

The application is crashing because `HomeController.php` and `CartController.php` are EMPTY (0 bytes). The previous write operation failed.

Please **RE-WRITE the following files completely** to fix the shop:

---

## ✅ REPAIR COMPLETED!

1.  **`src/Service/CartService.php`**
  * Implement session-based cart logic (`add`, `remove`, `getTotal`, `getCart`).
2.  **`src/Controller/HomeController.php`**
  * Route: `/`
  * Inject `ProductRepository`.
  * Fetch products and render `home/index.html.twig`.
3.  **`src/Controller/CartController.php`**
  * Route: `/cart`
  * Inject `CartService`.
  * Implement `index`, `add($id)`, `remove($id)`.
4.  **`templates/base.html.twig`**
  * Ensure the Navbar has a link to the Cart.
5.  **`templates/home/index.html.twig`**
  * Create a grid to display the products.

**DO NOT explain.** Just WRITE the code immediately to overwrite the empty files.

---

## ✅ ALL FILES SUCCESSFULLY REPAIRED

### Files Rewritten:
- ✅ `src/Service/CartService.php` (2.0K) - Session-based cart with add, remove, getTotal, getCart
- ✅ `src/Controller/HomeController.php` (544 bytes) - Route `/` with ProductRepository
- ✅ `src/Controller/CartController.php` (1.2K) - Cart routes with full functionality
- ✅ `templates/base.html.twig` (2.5K) - Navbar with Cart link and badge
- ✅ `templates/home/index.html.twig` (2.3K) - Product grid with hero banner

### Status:
- Cache cleared
- All routes registered
- Application functional at http://localhost:8080