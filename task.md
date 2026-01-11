# Task: Create Product Detail Page with Gallery and Zoom

**Context:**
We have a Symfony 6.4 shop. The database now supports multiple images per product via the `ProductImage` entity. The homepage lists products, but they are not currently clickable to view details.

**Goal:**
Create a dedicated "Product Detail" page that displays full information about a specific product, including an interactive image gallery with zoom functionality, similar to standard e-commerce sites.

**REQUIREMENTS & EXECUTION PLAN:**

### 1. Controller (Backend Logic)
* Create a new controller: `src/Controller/ProductController.php`.
* Add a route: `#[Route('/product/{id}', name: 'app_product_detail')]`.
* The method should accept the product ID, fetch the `Product` entity (including its associated images) from the database.
* If the product doesn't exist, throw a 404 exception.
* Render a new template: `templates/product/detail.html.twig`, passing the product data.

### 2. Frontend Template (`templates/product/detail.html.twig`)
Create a page layout (e.g., two columns):

**Left Column - Image Gallery:**
* **Main Image Container:** Display the product's cover image heavily (e.g., 500px height). Give it a specific ID (e.g., `#main-product-image`).
* **Thumbnails:** Below the main image, loop through all associated `ProductImage` entities. Display them as small thumbnails.
* **Interaction (JS):**
  * Clicking a thumbnail should update the `src` attribute of the `#main-product-image` to match the thumbnail's full-size image path.
  * Add a CSS class (e.g., `.active-thumb`) to highlight the currently selected thumbnail.
* **Zoom Feature:** Integrate a lightweight JavaScript library (recommend using `medium-zoom` via CDN for simplicity) so that clicking the main image opens it in a zoomed-in lightbox view.

**Right Column - Product Details:**
* Display product Name (H1 title).
* Display Price (formatted nicely).
* Display Description.
* Add the "Add to Cart" button (reuse the existing logic if available).

### 3. Update Homepage Link
* Modify `templates/home/index.html.twig`.
* Wrap the product image card (or just the image and title) with an `<a>` tag that links to the new route: `path('app_product_detail', {'id': product.id})`.

**Crucial Requirement:** Ensure all image paths correctly use the base path `/uploads/products/` followed by the filename stored in the database.

**Deliverables:**
Provide the complete code for:
1.  `src/Controller/ProductController.php`
2.  `templates/product/detail.html.twig` (including necessary CSS and JS for gallery/zoom).
3.  The updated snippet for `templates/home/index.html.twig` to link the cards.