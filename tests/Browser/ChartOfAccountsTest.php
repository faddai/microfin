<?php

namespace Tests\Browser;

use App\Entities\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ChartOfAccountsTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_can_see_seeded_ledgers_and_categories()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(factory(User::class)->create())
                    ->visit(route('accounting.chart'))
                    ->assertSee('Chart of Accounts')
                    ->assertSee('Share Capital')
                    ->assertSee('Non Current Liabilities');
        });
    }

}
