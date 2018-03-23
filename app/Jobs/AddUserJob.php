<?php

namespace App\Jobs;

use App\Entities\User;
use App\Notifications\NewUserAccountCreatedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class AddUserJob
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Request $request
     */
    private $request;

    /**
     * @var User $user
     */
    private $user;

    /**
     * @var bool
     */
    private $sendEmailNotification;

    /**
     * @var bool
     */
    private $isANewUser;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param User $user
     * @param bool $sendEmailNotification
     */
    public function __construct(Request $request, User $user = null, $sendEmailNotification = true)
    {
        $this->request = $request;
        $this->isANewUser = $user === null;
        $this->user = $user ?? new User;
        $this->sendEmailNotification = $sendEmailNotification;
    }
    /**
     * Execute the job.
     *
     *
     * @return mixed
     */
    public function handle()
    {
        return $this->addOrUpdateUser();
    }

    /**
     * @return mixed
     */
    private function addOrUpdateUser()
    {
        return DB::transaction(function () {

            foreach ($this->user->getFillable() as $fillable) {
                if ($this->request->filled($fillable)) {
                    $this->user->{$fillable} = $this->request->get($fillable);
                }
            }

            $this->user->save();

            if ($this->request->filled('roles')) {
                $this->user->syncRoles($this->request->get('roles'));
            }

            cache()->forget('users'); // bust users from cache

            // fire a password reset email to newly created user except indicated otherwise
            if ($this->isANewUser && $this->sendEmailNotification) {
                $this->user->notify(new NewUserAccountCreatedNotification($this->user));
            }

            return $this->user;

        });
    }

}
