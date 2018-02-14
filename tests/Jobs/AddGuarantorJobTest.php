<?php

use App\Entities\Guarantor;
use App\Entities\Loan;
use App\Jobs\AddGuarantorJob;


class AddGuarantorJobTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->setGuarantorDetails();
    }

    public function test_add_guarantor()
    {
        $loan = factory(Loan::class)->create();

        $guarantor = dispatch(new AddGuarantorJob($this->request, $loan));

        self::assertInstanceOf(Guarantor::class, $guarantor);
        self::assertEquals($this->request->name, $guarantor->name);
    }

    public function test_update_guarantor()
    {
        $loan = factory(Loan::class)->create();

        $guarantor = dispatch(new AddGuarantorJob($this->request, $loan));

        self::assertInstanceOf(Guarantor::class, $guarantor);
        self::assertEquals($this->request->name, $guarantor->name);

        $this->request->merge([
            'guarantor_id' => $guarantor->id,
            'years_known' => 30,
            'job_title' => 'Web Developer'
        ]);

        $updatedGuarantor = dispatch(new AddGuarantorJob($this->request, $loan));

        self::assertInstanceOf(Guarantor::class, $updatedGuarantor);
        self::assertEquals('Web Developer', $updatedGuarantor->job_title);
        self::assertEquals(30, $updatedGuarantor->years_known);
    }

    private function setGuarantorDetails()
    {
        $this->request->merge([
            'name' => 'Jacob Danso',
            'work_phone' => faker()->phoneNumber,
            'personal_phone' => faker()->phoneNumber,
            'employer' => faker()->company,
            'job_title' => faker()->jobTitle,
            'years_known' => random_int(3, 40),
            'email' => 'jacob@example.com',
            'residential_address' => faker()->address
        ]);
    }
}