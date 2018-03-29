<?php

use App\Entities\User;
use App\Jobs\AddUserJob;
use App\Notifications\NewUserAccountCreatedNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;


class AddUserJobTest extends TestCase
{
    public function test_that_user_is_successfully_created()
    {
        Notification::fake();

        $this->request->merge([
            'name' => 'Francis Addai',
            'email' => 'me@name.com',
            'password' => 'secret'
        ]);

        $user = $this->dispatch(new AddUserJob($this->request));

        self::assertInstanceOf(User::class, $user);
        Notification::assertSentTo($user, NewUserAccountCreatedNotification::class);
    }

    public function test_that_user_is_successfully_created_and_no_email_notification_is_sent()
    {
        Notification::fake();

        $this->request->merge([
            'name' => 'Francis Addai',
            'email' => 'me@name.com',
            'password' => 'secret'
        ]);

        $user = $this->dispatch(new AddUserJob($this->request, null, false));

        self::assertInstanceOf(User::class, $user);
        Notification::assertNotSentTo($user, NewUserAccountCreatedNotification::class);
    }

    public function test_can_update_user_through_a_request_and_send_no_email()
    {
        Notification::fake();

        $user = factory(User::class)->create();

        $this->request->merge(['name' => 'Gentle Jack']);

        $updatedUser = $this->dispatch(new AddUserJob($this->request, $user));

        self::assertEquals('Gentle Jack', $updatedUser->name);
        Notification::assertNotSentTo($user, NewUserAccountCreatedNotification::class);
    }

    public function test_user_can_be_suspended()
    {
        $user = factory(User::class)->create();

        $this->request->merge(['is_active' => 0]);

        self::assertTrue($user->isActive());

        $updatedUser = $this->dispatch(new AddUserJob($this->request, $user));

        self::assertFalse($updatedUser->isActive());
    }
}
