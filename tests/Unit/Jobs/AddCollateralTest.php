<?php

use App\Entities\Collateral;
use App\Entities\Loan;
use App\Jobs\AddCollateralJob;
use Tests\TestCase;


class AddCollateralTest extends TestCase
{
    public function test_add_a_single_collateral_to_a_loan()
    {
        $loan = factory(Loan::class)->create();

        $this->request->merge(['market_value' => 300, 'label' => 'Mobile Phone']);

        $collateral = $this->dispatch(new AddCollateralJob($this->request, $loan));

        self::assertInstanceOf(Collateral::class, $collateral);
        self::assertEquals(300, $collateral->value());
        self::assertEquals('Mobile Phone', $collateral->label);
        self::assertCount(1, $loan->fresh()->collaterals);
    }

    public function test_update_collateral()
    {
        $loan = factory(Loan::class)->create();

        $this->request->merge([
            'market_value' => 300, 'label' => 'Mobile Phone'
        ]);

        $collateral = $this->dispatch(new AddCollateralJob($this->request, $loan));

        $this->request->merge([
            'collateral_id' => $collateral->id,
            'label' => 'Digital Camera',
            'market_value' => 600
        ]);

        $updatedCollateral = $this->dispatch(new AddCollateralJob($this->request, $loan));

        self::assertInstanceOf(Collateral::class, $updatedCollateral);
        self::assertEquals(600, $updatedCollateral->value());
        self::assertNotEquals('Mobile Phone', $updatedCollateral->label);
        self::assertCount(1, $loan->fresh()->collaterals);
    }
}
