# User Seed Vault - seedvault:add Command

This artisan command allows you to create encrypted user data for seeding your Laravel application.

## Installation

1. Add the package to your Laravel project
2. Run `composer install` to install dependencies including `intervention/image`
3. The command will be automatically registered

## Usage

Run the command:
```bash
php artisan seedvault:add
```

The command will prompt you for:
- **Name**: User's full name
- **Email**: User's email address  
- **Password**: User's password (hidden input)
- **Avatar file path**: Absolute path to an image file

## Features

- **Avatar Processing**: Automatically resizes images to 96x96px and converts to JPEG
- **Encryption**: All data is encrypted using Laravel's encryption system
- **Multiple Users**: Option to add multiple users in one session
- **Fallback Support**: Uses Intervention Image if available, falls back to PHP GD functions
- **Format Output**: Generates properly formatted array entries for your seeder

## Supported Image Formats

- JPEG (.jpg, .jpeg)
- PNG (.png)
- GIF (.gif)

## Output

The command generates encrypted array entries that you can copy and paste into your UserSeeder's `$users` array:

```php
// User 1
[
    "name" => "eyJpdiI6InZkbFd4TFdJbmo0ZWhDazZuUVhMNkE9PSIsInZhbHVlIjoi...",
    "email" => "eyJpdiI6InJ1R20xM0hSZmlYeDBuT2dXTGo4WFE9PSIsInZhbHVlIjoi...",
    "password" => "eyJpdiI6IjRLNGtLMkN6aEc4VDNHNE42Y2cxdGc9PSIsInZhbHVlIjoi...",
    "avatar" => "eyJpdiI6ImZJakJtQkNXZmEzN3BnQXBkQjh0VlE9PSIsInZhbHVlIjoi...",
],
```

## Example Usage

```bash
$ php artisan seedvault:add

🔐 User Seed Vault - Add New User

Enter user details:
 Name:
 > John Doe

 Email:
 > john@example.com

 Password:
 > 

 Avatar file path (absolute path):
 > /Users/john/Pictures/avatar.jpg

Processing avatar...
✅ User 'John Doe' added successfully!

 Would you like to add another user? (yes/no) [no]:
 > no

🎉 Generated encrypted user data:

// User 1
[
    "name" => "eyJpdiI6InZkbFd4TFdJbmo0ZWhDazZuUVhMNkE9PSIsInZhbHVlIjoi...",
    "email" => "eyJpdiI6InJ1R20xM0hSZmlYeDBuT2dXTGo4WFE9PSIsInZhbHVlIjoi...",
    "password" => "eyJpdiI6IjRLNGtLMkN6aEc4VDNHNE42Y2cxdGc9PSIsInZhbHVlIjoi...",
    "avatar" => "eyJpdiI6ImZJakJtQkNXZmEzN3BnQXBkQjh0VlE9PSIsInZhbHVlIjoi...",
],

💡 Copy the above array entries and add them to your UserSeeder $users array.
```

## Requirements

- PHP 8.1+
- Laravel 9+
- GD extension (usually included with PHP)
- intervention/image package (automatically installed)

## Error Handling

The command includes comprehensive error handling for:
- Missing avatar files
- Unsupported image formats
- Image processing failures
- Missing GD extension
- Encryption errors

## Security

- All user data is encrypted using Laravel's built-in encryption
- Passwords are handled securely with hidden input
- Avatar images are processed and base64 encoded before encryption
