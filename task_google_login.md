# Task: Implement Google OAuth Login (Standard Symfony 6.4 Way)

**Goal:**
Allow users to log in AND register using their Google account via `knpuniversity/oauth2-client-bundle`.
Logic:
1. User clicks "Login with Google" (on Login OR Register page).
2. Google approves and returns user info.
3. **Existing User:** Link Google ID to existing email and log in.
4. **New User (Registration):** Automatically create a new User entity, set email/googleId, random password, role ROLE_USER, and log them in immediately.

**Prerequisites:**
The `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` are already present in `.env`.

**EXECUTION PLAN:**

### 1. Installation
Run command: `composer require knpuniversity/oauth2-client-bundle league/oauth2-google`

### 2. Configuration (`config/packages/knpu_oauth2_client.yaml`)
Create config:
* `type: google`
* `client_id: '%env(GOOGLE_CLIENT_ID)%'`
* `client_secret: '%env(GOOGLE_CLIENT_SECRET)%'`
* `redirect_route: connect_google_check`
* `redirect_params: {}`

### 3. Database (`User` Entity)
* Add column `googleId` (string, nullable).
* Add column `avatar` (string, nullable).
* Generate migration.

### 4. Controller (`src/Controller/GoogleController.php`)
Routes:
1.  `#[Route('/connect/google', name: 'connect_google')]` -> Redirects to Google.
2.  `#[Route('/connect/google/check', name: 'connect_google_check')]` -> Empty method (handled by Authenticator).

### 5. Authenticator (`src/Security/GoogleAuthenticator.php`)
Extend `OAuth2Authenticator`.
**On Success:**
* Fetch Google User.
* Check DB for email.
* If **Found**: Update `googleId`, log in.
* If **Not Found**: Create new `User`, persist, flush, log in.
* Redirect to `app_home` (homepage).

### 6. Security Config (`config/packages/security.yaml`)
* Register `GoogleAuthenticator` under `firewall: main: custom_authenticator`.

### 7. Frontend Templates (Add Buttons)
* **Login Page (`templates/security/login.html.twig`):** Add "Login with Google" button pointing to `connect_google`.
* **Registration Page (`templates/registration/register.html.twig`):** Add the SAME "Sign up with Google" button.

**Deliverables:**
Provide full code for Controller, Authenticator, Config files, and the HTML snippets for the buttons.