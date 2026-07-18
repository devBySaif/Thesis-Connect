# Forgot Password Feature - Implementation Summary

## Overview
A complete forgot password feature has been implemented using a popup modal on the login page. Users can request a password reset, receive a reset link via email, and create a new password.

## Files Modified/Created

### 1. **Login Page** - [view/login.php](view/login.php)
- **Changed:** "Forgot Password?" link now has an ID and opens the popup modal
- **Added:** Forgot Password popup modal HTML with form

### 2. **Login Styles** - [css/login.css](css/login.css)
- **Added:** Complete modal styling including:
  - `.modal` - backdrop overlay with fade animation
  - `.modal-content` - centered modal box with slide animation
  - `.modal-header` - header with title and close button
  - `.modal-body` - body content with form
  - `.modal-error` / `.modal-success` - message displays
  - Responsive design for mobile devices

### 3. **JavaScript** - [js/auth.js](js/auth.js)
- **Added:** Forgot Password popup functionality:
  - Open modal on "Forgot Password?" link click
  - Close modal on X button or background click
  - Handle form submission via AJAX
  - Display success/error messages

### 4. **User Model** - [model/User.php](model/User.php)
- **Added Methods:**
  - `createPasswordResetTable()` - Creates the password_resets table if it doesn't exist
  - `generatePasswordResetToken($email)` - Generates a unique token (valid 24 hours)
  - `validatePasswordResetToken($token)` - Validates if token is still valid
  - `resetPasswordWithToken($token, $newPassword)` - Resets password if token is valid

### 5. **Controller** - [control/AuthController.php](control/AuthController.php)
- **Added Cases:** `forgot_password` and `reset_password` in the main switch statement
- **Added Functions:**
  - `forgotPassword($user)` - Handles forgot password requests, generates token, sends email
  - `resetPassword($user)` - Handles password reset form submission

### 6. **Reset Password Page** - [view/reset_password.php](view/reset_password.php)
- **New File:** Page where users reset their password using the token from email
- Features:
  - Token validation
  - New password input with confirmation
  - Password validation (min 8 characters)
  - Automatic redirect to login on success
  - Error handling

## Database Table Created

### password_resets Table
```sql
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reset_token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)
```

**Note:** This table is automatically created on first use if it doesn't exist.

## How It Works

### User Flow:
1. User clicks "Forgot Password?" on login page
2. Popup modal opens
3. User enters their email address
4. Backend validates email and generates a unique token (24-hour expiration)
5. Email is sent with reset link (includes token as URL parameter)
6. User clicks link in email → goes to reset_password.php
7. User enters new password and confirms it
8. Backend validates token and resets password
9. User redirected to login page
10. User logs in with new password

### Security Features:
- **Token Expiration:** Reset tokens expire after 24 hours
- **One-time Use:** Tokens are deleted after password is reset
- **Unique Tokens:** 32-byte random tokens generated with `random_bytes()`
- **Email Verification:** Only users with existing email can reset
- **Password Hashing:** New passwords are hashed with PASSWORD_DEFAULT (bcrypt)
- **Transaction Protection:** Database operations use transactions

## Email Configuration

The current implementation uses PHP's `mail()` function. For production use, consider:

### Option 1: Configure Server Mail
Update the `mail()` function in `forgotPassword()` in AuthController.php

### Option 2: Use PHPMailer
```php
composer require phpmailer/phpmailer
```

## Testing

### Development Mode:
The forgot password response includes a `resetLink` field for testing:
```json
{
    "status": "success",
    "message": "...",
    "resetLink": "http://localhost/Thesis-Connect/view/reset_password.php?token=..."
}
```

**Remove this in production!** Edit `forgotPassword()` in AuthController.php and remove the line:
```php
"resetLink" => $resetLink
```

### Test Steps:
1. Go to Login Page
2. Click "Forgot Password?"
3. Enter a registered email
4. Check browser console (network tab) or backend response for reset link
5. Visit the reset link
6. Enter new password and confirm
7. Try logging in with new password

## Customization Options

### Change Token Expiration Time:
In `model/User.php`, `generatePasswordResetToken()` method:
```php
$expiresAt = date('Y-m-d H:i:s', time() + 86400); // 24 hours
// Change 86400 to desired seconds (e.g., 3600 = 1 hour)
```

### Change Email Subject/Message:
In `control/AuthController.php`, `forgotPassword()` function:
```php
$subject = 'Your Custom Subject';
$message = 'Your custom message with link: ' . $resetLink;
```

### Style Customization:
Modal styles are in `css/login.css` under "Forgot Password Modal" section

## Notes

- Tokens are stored with expiration time for security
- Only one reset token per user at a time (old tokens are deleted)
- Email sending requires mail server configuration on the server
- For local development, use `mail()` function or configure local mail
- Consider adding rate limiting to prevent token flooding
