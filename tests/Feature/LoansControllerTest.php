<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 29/03/2017
 * Time: 4:05 PM
 */

use App\Entities\User;
use Tests\TestCase;

class LoansControllerTest extends TestCase
{
    public function test_that_unauthenticated_users_cannot_see_loans()
    {
        $this->get('loans')
            ->assertStatus(302)
            ->assertRedirect('/login');
    }

    public function test_that_authenticated_users_can_see_loans()
    {
        $this->actingAs(factory(User::class)->create())
            ->get('loans')
            ->assertSeeText('Loan Applications');
    }
}