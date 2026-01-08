The user wants a full **Admin Dashboard** to manage Products and Categories via UI.
Please BUILD the backend using EasyAdmin Bundle.

1. **REFACTOR DATA STRUCTURE (Category Entity)**:
    - Create `src/Entity/Category.php`. Fields: `id`, `name` (string), `products` (OneToMany relation to Product).
    - Update `src/Entity/Product.php`. Add `category` (ManyToOne relation to Category). Update getters/setters.

2. **SETUP EASYADMIN**:
    - Create `src/Controller/Admin/DashboardController.php`.
        - Route: `/admin`.
        - Secure it: `#[IsGranted('ROLE_ADMIN')]`.
        - Menu items: Dashboard, Products (CRUD), Categories (CRUD), Back to Shop.

3. **CREATE CRUD CONTROLLERS**:
    - **`src/Controller/Admin/CategoryCrudController.php`**: Simple name field.
    - **`src/Controller/Admin/ProductCrudController.php`**:
        - Fields:
            - `TextField::new('name')`
            - `TextEditorField::new('description')` (for rich text)
            - `MoneyField::new('price')->setCurrency('BGN')`
            - `AssociationField::new('category')`
            - `ImageField::new('image')->setBasePath('uploads/products')->setUploadDir('public/uploads/products')` (Allow file upload).

4. **UPDATE FIXTURES (`src/DataFixtures/AppFixtures.php`)**:
    - Create 5 Categories (Plush, Wooden, Interactive, etc.).
    - Assign products to these categories automatically so the database isn't broken.

DO NOT explain. Just WRITE the code for these files.