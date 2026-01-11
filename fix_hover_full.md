# Task: Enable Image Swap AND "View" Overlay

**Issues:**
1.  **No Image Swap:** The frontend uses the same image for front and back because the API (`HomeController`) only returns the main image.
2.  **Missing Overlay:** We want to see a text "РАЗГЛЕДАЙ" (View) centered over the image on hover, along with the image swap.

**EXECUTION PLAN:**

### 1. Update Backend (`src/Controller/HomeController.php`)
Modify the `getProducts` method (the API endpoint).
Current logic returns `image` => cover image.
**New Logic:**
* Fetch the product's images collection.
* `image`: The Cover Image (or first image).
* `hoverImage`: The **second** image in the collection (index 1). If there is only 1 image, fallback to the same as `image`.
* Return both in the JSON response.

### 2. Update Frontend (`templates/home/index.html.twig`)
Modify the JavaScript `col.innerHTML` template inside `loadProducts`.

**HTML Structure Required:**
```html
<div class="product-card">
    <a href="...">
        <div class="image-swap-container">
            <img src="${product.image}" class="img-front" ...>
            
            <img src="${product.hoverImage}" class="img-back" ...>
            
            <div class="hover-overlay">
                <span class="badge-view">РАЗГЛЕДАЙ</span>
            </div>
        </div>
        </a>
</div>