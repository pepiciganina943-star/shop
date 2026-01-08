# Task: Implement "Inner Zoom" (Magnifying Glass Effect)

**Current Issue:**
The previous fullscreen zoom caused browser freezing issues.

**Goal:**
Implement a standard e-commerce "Inner Zoom" or "Lens Zoom" effect.
When the user hovers over the main product image, the image should zoom in **within its own container** (or show a magnified view), allowing the user to inspect details by moving the mouse.

**Technical Requirements:**

1.  **Library:** Use a lightweight, stable library like **`drift-zoom`** (highly recommended) or a simple CSS/JS custom implementation.
    * *Note:* `Drift` is very popular for this exact behavior.
2.  **Implementation Details (`templates/product/show.html.twig`):**
    * Include the library via CDN (e.g., `https://cdn.jsdelivr.net/npm/drift-zoom/dist/Drift.min.js` and its CSS).
    * Initialize the zoom on the `#main-product-image`.
    * **Behavior:** On mouse hover, show the zoomed detail. On mouse leave, return to normal.
    * **Gallery Compatibility:** Ensure that when a user clicks a thumbnail and the main image `src` changes, the zoom updates to zoom the *new* image (not the old one). You might need to update the `data-zoom` attribute or destroy/re-init the instance.

3.  **Fallback:** On mobile devices (touch screens), simple pinch-to-zoom or just opening the raw image in a new tab is acceptable, but the hover effect is critical for desktop.

**Deliverables:**
Provide the **full updated code** for `templates/product/show.html.twig` including the necessary scripts and styles.