<?php

/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 15/10/2016
 * Time: 23:14
 */

use App\Entities\User;


class LoginControllerTest extends TestCase
{
    public function test_login_page_is_accessible()
    {
        $this->get('/login')->seeStatusCode(200);
    }

    public function test_authenticated_user_can_see_dashboard()
    {
        $user = factory(User::class)->create();

        auth()->login($user);

        $this->get('/')->seeStatusCode(200);
        $this->see('Dashboard');
    }
}
