<?php

namespace Blemli\UserSeedVault\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class UserSeedVaultCommand extends Command
{
    public $signature = 'seedvault:add 
                        {--name= : The name of the user}
                        {--mail= : The email address of the user}
                        {--avatar= : The path to the avatar image file}';

    public $description = 'Add a new user to the seed vault with encrypted data';

    protected $users = [];

    public function handle(): int
    {
        $this->info('🔐 User Seed Vault - Add New User');
        $this->line('');

        // Check if avatar_url column exists
        if (!$this->checkAvatarUrlColumn()) {
            if ($this->confirm('The users table is missing the required avatar_url column. Would you like to publish and run the migration now?', true)) {
                $this->publishAndRunMigration();
            } else {
                $this->error('Cannot proceed without the avatar_url column. Please run the migration manually.');
                return self::FAILURE;
            }
        }

        do {
            $this->addUser();
            $addAnother = $this->confirm('Would you like to add another user?', false);
        } while ($addAnother);

        $this->outputUsers();

        return self::SUCCESS;
    }

    protected function addUser(): void
    {
        $this->info('Enter user details:');
        
        // Get name from option or ask for it
        $name = $this->option('name');
        if (!$name) {
            $name = $this->ask('Name');
        }
        
        // Get email from option or ask for it
        $email = $this->option('mail');
        if (!$email) {
            $email = $this->ask('Email');
        }
        
        // Always ask for password (never as parameter)
        $password = $this->secret('Password');
        
        // Get avatar path from option or ask for it
        $avatarPath = $this->option('avatar');
        if (!$avatarPath) {
            $avatarPath = $this->ask('Avatar file path (absolute path)');
        }

        // Validate avatar file exists
        if (!file_exists($avatarPath)) {
            $this->error("Avatar file not found: {$avatarPath}");
            return;
        }

        // Process avatar
        $this->info('Processing avatar...');
        $base64Avatar = $this->processAvatar($avatarPath);

        if (!$base64Avatar) {
            $this->error('Failed to process avatar image');
            return;
        }

        // Encrypt all data
        $encryptedUser = [
            'name' => Crypt::encrypt($name),
            'email' => Crypt::encrypt($email),
            'password' => Crypt::encrypt($password),
            'avatar' => Crypt::encrypt($base64Avatar),
        ];

        $this->users[] = $encryptedUser;
        
        $this->info("✅ User '{$name}' added successfully!");
        $this->line('');
    }

    protected function processAvatar(string $avatarPath): ?string
    {
        try {
            // Try using Intervention Image first
            if (class_exists('\Intervention\Image\ImageManager')) {
                return $this->processAvatarWithIntervention($avatarPath);
            }
            
            // Fallback to GD functions
            return $this->processAvatarWithGD($avatarPath);
        } catch (\Exception $e) {
            $this->error("Error processing avatar: " . $e->getMessage());
            return null;
        }
    }

    protected function processAvatarWithIntervention(string $avatarPath): ?string
    {
        $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        $image = $manager->read($avatarPath);
        $image->resize(96, 96);
        $encoded = $image->toJpeg(90);
        return base64_encode($encoded);
    }

    protected function processAvatarWithGD(string $avatarPath): ?string
    {
        // Check if GD extension is available
        if (!extension_loaded('gd')) {
            throw new \Exception('GD extension is not available');
        }

        // Get image info
        $imageInfo = getimagesize($avatarPath);
        if (!$imageInfo) {
            throw new \Exception('Invalid image file');
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $type = $imageInfo[2];

        // Create image resource based on type
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($avatarPath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($avatarPath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($avatarPath);
                break;
            default:
                throw new \Exception('Unsupported image type');
        }

        if (!$source) {
            throw new \Exception('Failed to create image resource');
        }

        // Create 96x96 canvas
        $canvas = imagecreatetruecolor(96, 96);
        
        // Resize image
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, 96, 96, $width, $height);

        // Start output buffering to capture JPEG data
        ob_start();
        imagejpeg($canvas, null, 90);
        $imageData = ob_get_contents();
        ob_end_clean();

        // Clean up resources
        imagedestroy($source);
        imagedestroy($canvas);

        return base64_encode($imageData);
    }

    protected function outputUsers(): void
    {
        if (empty($this->users)) {
            $this->warn('No users were added.');
            return;
        }

        $this->line('');
        $this->info('🎉 Adding encrypted user data to UserSeeder.php...');
        
        $this->addUsersToSeeder();
        
        $this->info('✅ Users successfully added to database/seeders/UserSeeder.php');
        $this->line('');
        $this->info('💡 You can now run "php artisan db:seed --class=UserSeeder" to seed the users.');
        $this->line('');
    }

    protected function addUsersToSeeder(): void
    {
        $seederPath = database_path('seeders/UserSeeder.php');
        
        // Create seeders directory if it doesn't exist
        if (!is_dir(dirname($seederPath))) {
            mkdir(dirname($seederPath), 0755, true);
        }
        
        // Check if UserSeeder.php exists, if not create it
        if (!file_exists($seederPath)) {
            $this->createUserSeeder($seederPath);
        }
        
        // Read the current seeder file
        $seederContent = file_get_contents($seederPath);
        
        // Find the $users array and add new users
        $this->updateUsersArray($seederContent, $seederPath);
    }

    protected function createUserSeeder(string $path): void
    {
        $seederTemplate = '<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function saveFromBase64($base64, $directory)
    {
        $file = base64_decode($base64);
        $filename = \Illuminate\Support\Str::ulid() . \'.jpg\';
        $relativePath = $directory . \'/\' . $filename;
        \Illuminate\Support\Facades\Storage::disk(\'public\')->put($relativePath, $file);
        return $relativePath;
    }

    protected $users = [
        // Users will be added here by the seedvault:add command
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->users as $user) {
            User::updateOrCreate([
                \'email\' => Crypt::decrypt($user[\'email\'])
            ], [
                \'name\' => Crypt::decrypt($user[\'name\']),
                \'password\' => bcrypt(Crypt::decrypt($user[\'password\'])),
                \'avatar_url\' => $this->saveFromBase64(Crypt::decrypt($user[\'avatar\']), \'avatars\'),
            ]);
        }
    }
}
';
        file_put_contents($path, $seederTemplate);
    }

    protected function updateUsersArray(string $seederContent, string $seederPath): void
    {
        // Find the $users array
        $pattern = '/protected \$users = \[(.*?)\];/s';
        
        if (preg_match($pattern, $seederContent, $matches)) {
            $currentUsersContent = $matches[1];
            
            // Generate new user entries
            $newUsersContent = $currentUsersContent;
            
            foreach ($this->users as $index => $user) {
                $userEntry = "\n        // User " . ($this->getUserCount($currentUsersContent) + $index + 1) . "\n";
                $userEntry .= "        [\n";
                $userEntry .= "            \"name\" => \"" . $user['name'] . "\",\n";
                $userEntry .= "            \"email\" => \"" . $user['email'] . "\",\n";
                $userEntry .= "            \"password\" => \"" . $user['password'] . "\",\n";
                $userEntry .= "            \"avatar\" => \"" . $user['avatar'] . "\",\n";
                $userEntry .= "        ],";
                
                $newUsersContent .= $userEntry;
            }
            
            // Replace the users array in the seeder content
            $newSeederContent = preg_replace(
                $pattern,
                'protected $users = [' . $newUsersContent . "\n    ];",
                $seederContent
            );
            
            file_put_contents($seederPath, $newSeederContent);
        }
    }

    protected function getUserCount(string $usersContent): int
    {
        // Count existing user entries by counting opening brackets
        return substr_count($usersContent, '[') - substr_count($usersContent, '// Users will be added here');
    }

    protected function checkAvatarUrlColumn(): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn('users', 'avatar_url');
        } catch (\Exception $e) {
            // If we can't check the column (e.g., users table doesn't exist), return false
            return false;
        }
    }

    protected function publishAndRunMigration(): void
    {
        $this->info('📦 Publishing migrations...');
        
        // Publish migrations
        $this->call('vendor:publish', [
            '--provider' => 'Blemli\UserSeedVault\UserSeedVaultServiceProvider',
            '--tag' => 'migrations',
            '--force' => true
        ]);

        // Check if migration was published
        $migrationPath = database_path('migrations');
        $migrationFiles = glob($migrationPath . '/*add_avatar_url_to_users_table.php');
        
        if (empty($migrationFiles)) {
            // If not published via vendor:publish, copy manually with timestamp
            $this->info('📝 Creating migration file manually...');
            $timestamp = date('Y_m_d_His');
            $migrationFile = $migrationPath . '/' . $timestamp . '_add_avatar_url_to_users_table.php';
            
            $migrationContent = file_get_contents(__DIR__ . '/../../database/migrations/add_avatar_url_to_users_table.php.stub');
            file_put_contents($migrationFile, $migrationContent);
            
            $this->info("✅ Migration created: {$migrationFile}");
        }

        $this->info('🚀 Running migrations...');
        
        // Run migrations
        $this->call('migrate');

        // Verify the column was added
        if ($this->checkAvatarUrlColumn()) {
            $this->info('✅ Avatar URL column successfully added to users table!');
        } else {
            $this->error('❌ Failed to add avatar_url column. Please check the migration manually.');
        }
    }
}
