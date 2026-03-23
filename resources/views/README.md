# Views Folder Structure

The `views` folder contains all Blade template files for the frontend UI. It's organized into reusable components, layouts, and Livewire interactive components.

## Folder Organization

```
views/
├── components/                          # Reusable UI components
│   ├── layouts/
│   │   └── app.blade.php                # Component wrappers for app layout
│   ├── action-message.blade.php         # Flash message display
│   ├── application-logo.blade.php       # App logo component
│   ├── auth-session-status.blade.php    # Session status alerts
│   ├── danger-button.blade.php          # Destructive action button
│   ├── dropdown.blade.php               # Dropdown menu component
│   ├── dropdown-link.blade.php          # Individual dropdown menu item
│   ├── input-error.blade.php            # Form validation error display
│   ├── input-label.blade.php            # Form field labels
│   ├── modal.blade.php                  # Modal dialog component
│   ├── nav-link.blade.php               # Navigation link with active state
│   ├── primary-button.blade.php         # Primary action button
│   ├── responsive-nav-link.blade.php    # Mobile-responsive navigation link
│   ├── secondary-button.blade.php       # Secondary action button
│   └── text-input.blade.php             # Text input field
├── layouts/                             # Main page layouts
│   ├── app.blade.php                    # Authenticated user layout (with navigation & sidebar)
│   └── guest.blade.php                  # Unauthenticated user layout (public pages)
├── livewire/                            # Livewire interactive components
│   ├── layout/
│   │   └── navigation.blade.php         # Main navigation bar for authenticated pages
│   ├── pages/                           # Full-page Livewire components
│   │   ├── admin/
│   │   │   └── user-roles.blade.php     # User roles and permissions management page
│   │   └── auth/                        # Authentication-related pages
│   │       ├── login.blade.php          # Login form
│   │       ├── register.blade.php       # User registration form
│   │       ├── forgot-password.blade.php # Password reset request page
│   │       ├── reset-password.blade.php  # Password reset form
│   │       ├── verify-email.blade.php   # Email verification page
│   │       └── confirm-password.blade.php # Password confirmation for sensitive actions
│   ├── profile/                         # User profile management components
│   │   ├── update-profile-information-form.blade.php # Edit user profile
│   │   ├── update-password-form.blade.php # Change password form
│   │   └── delete-user-form.blade.php   # Account deletion form
│   ├── welcome/
│   │   └── navigation.blade.php         # Navigation for welcome/public pages
│   └── .gitkeep
├── dashboard.blade.php                  # Main dashboard page (authenticated users)
├── profile.blade.php                    # User profile page
├── welcome.blade.php                    # Welcome/homepage (public landing page)
└── README.md                            # This file
```

## Key Concepts

- **Layouts** (`layouts/`): Base HTML structures that wrap page content with common elements like headers and footers
- **Components** (`components/`): Small, reusable UI parts (buttons, forms, alerts) that can be included in any view
- **Livewire Pages** (`livewire/pages/`): Full interactive pages built with Livewire for real-time user interactions without page reloads
- **Livewire Forms** (`livewire/profile/`): Specialized Livewire components for handling form submissions with validation
