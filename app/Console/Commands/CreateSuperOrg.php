<?php

namespace App\Console\Commands;

use App\Models\Organisation;
use App\Models\User;
use App\Models\Membership;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateSuperOrg extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'org:create-super {email : Email of the initial super admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the super organisation and assign initial admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        // Check if super org already exists
        if (Organisation::where('is_super_org', true)->exists()) {
            $this->error('Super organisation already exists!');
            return 1;
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error('User not found!');
            return 1;
        }

        DB::transaction(function () use ($user) {
            $superOrg = Organisation::create([
                'name' => 'System Administration',
                'personal_organisation' => false,
                'is_super_org' => true,
            ]);

            // Attach the user as owner
            $superOrg->users()->attach($user->id, [
                'role' => Membership::ROLE_OWNER
            ]);

            $this->info('Super organisation created successfully!');
        });

        return 0;
    }
}
