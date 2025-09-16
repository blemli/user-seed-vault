<?php

namespace Blemli\UserSeedVault\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class UserSeedVaultCommand extends Command
{
    public $signature = 'seedvault:add';

    public $description = 'Add a new user to the seed vault with encrypted data';

    protected $users = [];

    public function handle(): int
    {
        $this->info('🔐 User Seed Vault - Add New User');
        $this->line('');

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
        
        $name = $this->ask('Name');
        $email = $this->ask('Email');
        $password = $this->secret('Password');
        $avatarPath = $this->ask('Avatar file path (absolute path)');

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
        $this->info('🎉 Generated encrypted user data:');
        $this->line('');
        
        foreach ($this->users as $index => $user) {
            $this->line("// User " . ($index + 1));
            $this->line('[');
            $this->line('    "name" => "' . $user['name'] . '",');
            $this->line('    "email" => "' . $user['email'] . '",');
            $this->line('    "password" => "' . $user['password'] . '",');
            $this->line('    "avatar" => "' . $user['avatar'] . '",');
            $this->line('],');
            $this->line('');
        }

        $this->info('💡 Copy the above array entries and add them to your UserSeeder $users array.');
        $this->line('');
    }
}
