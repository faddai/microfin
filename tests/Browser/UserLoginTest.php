<?php

namespace Tests\Browser;

use App\Entities\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UserLoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_can_see_login_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('microFin');
        });
    }

    public function test_can_login_successfully()
    {
        $user = ['email' => 'john@snow.com', 'password' => 'winteriscoming'];

        factory(User::class)->create($user);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/')
                    ->type('email', $user['email'])
                    ->type('password', $user['password'])
                    ->press('Login')
                    ->assertPathIs('/')
                    ->assertSee('Dashboard');
        });
    }

}
