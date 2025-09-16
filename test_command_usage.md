# User Seed Vault Command Usage Examples

The `seedvault:add` command now supports the following parameters:

## Command Signature
```bash
php artisan seedvault:add [--name=] [--mail=] [--avatar=] [--seed]
```

## Parameters
- `--name`: The name of the user (optional - will prompt if not provided)
- `--mail`: The email address of the user (optional - will prompt if not provided)  
- `--avatar`: The path to the avatar image file (optional - will prompt if not provided)
- `--seed`: Run the UserSeeder automatically after adding users (optional)
- Password is NEVER a parameter - always prompted for security

## Usage Examples

### 1. With all parameters provided
```bash
php artisan seedvault:add --name="Peter Meier" --mail="peter@xy.test" --avatar="./peter.jpg"
```

### 2. With some parameters (missing ones will be prompted)
```bash
php artisan seedvault:add --name="Peter Meier" --mail="peter@xy.test"
# Will prompt for avatar path and password
```

### 3. With no parameters (all will be prompted)
```bash
php artisan seedvault:add
# Will prompt for name, email, avatar path, and password
```

### 4. With only one parameter
```bash
php artisan seedvault:add --name="Peter Meier"
# Will prompt for email, avatar path, and password
```

### 5. With --seed option to automatically run the seeder
```bash
php artisan seedvault:add --name="Peter Meier" --mail="peter@xy.test" --avatar="./peter.jpg" --seed
# Will add the user and immediately run the UserSeeder to insert into database
```

### 6. Using --seed with interactive prompts
```bash
php artisan seedvault:add --seed
# Will prompt for all user details, then automatically run the seeder
```

## Behavior
- If any parameter is omitted, the command will ask for it interactively
- Password is ALWAYS asked for (never passed as parameter for security)
- The command validates that the avatar file exists before processing
- All data is encrypted before being stored in the UserSeeder.php file
- When `--seed` is used, the UserSeeder will be executed automatically after adding users
- Without `--seed`, you need to manually run `php artisan db:seed --class=UserSeeder`
