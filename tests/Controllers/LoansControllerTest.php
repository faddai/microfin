<?php

use App\Entities\User;


class LoansControllerTest extends TestCase
{
    public function test_that_unauthenticated_users_cannot_see_loans()
    {
        $this->get('loans')
            ->seeStatusCode(302)
            ->assertRedirectedTo('/login');
    }

    public function test_that_authenticated_users_can_see_loans()
    {
        $this->actingAs(factory(User::class)->create())
            ->get('loans')
            ->see('Loan Applications');
    }
}