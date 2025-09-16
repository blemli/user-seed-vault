<?php

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
        $filename = \Illuminate\Support\Str::ulid() . '.jpg';
        $relativePath = $directory . '/' . $filename;
        \Illuminate\Support\Facades\Storage::disk('public')->put($relativePath, $file);
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
                'email' => Crypt::decrypt($user['email'])
            ], [
                'name' => Crypt::decrypt($user['name']),
                'password' => bcrypt(Crypt::decrypt($user['password'])),
                'avatar_url' => $this->saveFromBase64(Crypt::decrypt($user['avatar']), 'avatars'),
            ]);
        }
    }
}
