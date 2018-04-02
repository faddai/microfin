<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UserLoginTest extends DuskTestCase
{
    public function test_can_see_login_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('microFin');
        });
    }

}
