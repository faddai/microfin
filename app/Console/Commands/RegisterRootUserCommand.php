<?php

namespace App\Console\Commands;

use App\Entities\User;
use App\Jobs\AddUserJob;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;

class RegisterRootUserCommand extends Command
{

    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'microfin:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a root user for the application';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->registerRootUser($this->getRootUserDetails());
    }

    /**
     * @return array
     */
    private function getRootUserDetails()
    {
        $user['name'] = $this->ask('Full name', 'Francis Addai');
        $user['email'] = $this->ask('E-mail Address', 'me@faddai.com');
        $user['branch_id'] = 1;
        $user['password'] = $this->secret('Password');
        $confirmation = $this->secret('Repeat Password');

        while (!$this->isValidRegistration($user, $confirmation)) {

            if (!$this->isAcceptableLength($user['password'])) {

                $this->error('Password cannot be shorter than 8 characters');

            } else if (!$this->passwordMatches($user['password'], $confirmation)) {

                $this->error('Passwords do not match. Please try again');

            }

            $user['password'] = $this->secret('Password');

            $confirmation = $this->secret('Repeat Password');
        }

        if ($this->userExists($user['email'])) {
            $this->error('A user with same email already exists');
        } else {
            return $user;
        }

        return [];
    }

    private function userExists($email)
    {
        return User::where('email', $email)->first();
    }

    /**
     * @param $password
     * @param $confirmation
     *
     * @return bool
     */
    private function passwordMatches($password, $confirmation)
    {
        return $password === $confirmation;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    private function isAcceptableLength($value)
    {
        return strlen($value) >= 8;
    }

    /**
     * @param $user
     *
     * @return mixed
     */
    private function registerRootUser(array $user)
    {
        $request = new Request($user);

        return $this->dispatch(new AddUserJob($request, null, false));
    }

    private function isValidRegistration(array $user, $passwordConfirmation)
    {
        return $this->isAcceptableLength($user['password']) &&
            $this->passwordMatches($user['password'], $passwordConfirmation);
    }
}
