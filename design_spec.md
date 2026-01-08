# Design & Optimization Specification for Bellamie (Symfony Shop)

## 1. Project Overview
We are building a handmade e-commerce shop called **Bellamie**. The backend is Symfony 6 with Twig.
**Target Hosting:** Koyeb (Free Tier). This means code must be lightweight, assets must load fast, and we must rely on CDNs where possible to save bandwidth.

---

## 2. STRICT CONSTRAINTS (DO NOT TOUCH)
**CRITICAL:** The product card design and the "Image Swap" hover effect are already perfect.
**YOU MUST PRESERVE the following CSS classes exactly as they are:**
* `.product-card`
* `.image-swap-container`
* `.img-front` / `.img-back`
* `.hover-overlay`
* `.badge-view`
* `.btn-add-cart`

Do **NOT** modify the HTML structure or CSS logic for the product grid in `home/index.html.twig` or `category/show.html.twig`.

---

## 3. Design System
* **Primary Color (Teal):** `#40E0D0` (Buttons, Accents, Logo)
* **Secondary Color:** `#35c5b8` (Hover states)
* **Background:** `#f8f5f2` (Cream/Off-white)
* **Text:** `#333` (Charcoal)
* **Footer Background:** `#2c2c2c` (Dark Grey)
* **Fonts:** * Headings: 'Merriweather', serif
    * Body: 'Open Sans', sans-serif

---

## 4. Tasks to Implement

### A. The Header (Modern & UX Focused)
Refactor the `<header>` in `base.html.twig` to look professional.
1.  **Top Bar:** Thin strip with "Free shipping over 50 BGN".
2.  **Main Header:**
    * **Logo:** Left aligned, stylish.
    * **Search Bar:** Centered, rounded edges (pill shape), clean focus state.
    * **Icons:** User, Heart (Wishlist), Cart (with a dynamic badge count).
3.  **Navigation Bar:**
    * Must use the Symfony render function: `{{ render(controller('App\\Controller\\MenuController::renderHeader')) }}`.
    * Must be responsive (Hamburger menu on mobile).
    * Style it clean, white background, sticky if possible.

### B. The Footer (Professional & Trustworthy)
Create a dark, 4-column footer in `base.html.twig`:
1.  **Brand:** Logo + Short text about "Handmade with love".
2.  **Quick Links:** Home, Products, About Us, Contact.
3.  **Customer Service:** Shipping, Returns, FAQ.
4.  **Connect:** Social media icons + Newsletter input.
* **Copyright:** Dynamic year `{{ "now"|date("Y") }}`.

---

## 5. SEO & Performance Optimization (Koyeb/Free Hosting Focus)
Since we are on a free tier, every millisecond counts. Implement these specific optimizations in `base.html.twig`:

1.  **Meta Tags:** Add proper `meta description`, `keywords`, and Open Graph (OG) tags for social sharing.
2.  **Preconnect:** Add `<link rel="preconnect">` for Google Fonts and CDN domains.
3.  **CDN Usage:** Ensure Bootstrap 5 and FontAwesome are loaded via CDN (jsdelivr/cdnjs) to reduce server load.
4.  **Lazy Loading:** Add `loading="lazy"` to the Logo and static images in the footer.
5.  **Font Display:** Use `&display=swap` in the Google Fonts URL to prevent invisible text during load.
6.  **Favicon:** Ensure a placeholder link for favicon is present to avoid 404 errors in console.
7.  **Mobile View:** Ensure `meta viewport` is set correctly.

---

## 6. Output Required
Please provide the full, final code for **`templates/base.html.twig`**.
* Combine the CSS into a `<style>` block in the head (for speed, avoiding extra HTTP requests).
* Keep the Symfony Logic (`app.user`, `app.session`, `render(...)`) intact.
* Comment the code clearly.