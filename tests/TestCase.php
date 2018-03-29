<?php

/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 15/10/2016
 * Time: 23:14
 */
namespace Tests;

use App\Entities\Loan;
use App\Entities\Permission;
use App\Entities\Role;
use App\Entities\User;
use App\Jobs\ApproveLoanJob;
use App\Jobs\DisburseLoanJob;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Request;



abstract class TestCase extends BaseTestCase
{

    use DispatchesJobs, CreatesApplication;

    /**
     * @var Request
     */
    protected $request;

    public function setUp()
    {
        parent::setUp();

        $this->request = new Request();

        $this->runMigrationsAndSeed();
    }

    /**
     * Run migrations together with seeding
     */
    private function runMigrationsAndSeed()
    {
        if (env('DB_DATABASE') === ':memory:') {
            $this->artisan('migrate', ['--seed' => true]);
        }
    }

    /**
     * @param $objectInstance
     * @param $methodName
     * @param array $parameters
     * @return mixed
     */
    public function callPrivateMethod(&$objectInstance, $methodName, $parameters = [])
    {
        $method = (new \ReflectionClass(get_class($objectInstance)))->getMethod($methodName);

        $method->setAccessible(true);

        return $method->invokeArgs($objectInstance, $parameters);
    }

    /**
     * @param null $permission
     * @return Request
     */
    public function setAuthenticatedUserForRequest($permission = null)
    {
        return $this->request->setUserResolver(function () use ($permission) {
            $user = $this->createUserWithARole(Role::CASHIER);

            if ($permission) {
                $permission = Permission::firstOrCreate(['name' => $permission])->id;

                $user->roles->first()->permissions()->sync([$permission]);
            }

            return $user;
        });
    }

    /**
     * @param $role
     * @param int $count
     * @param array $data
     * @return User|Collection
     */
    protected function createUserWithARole($role, $count = 1, array $data = [])
    {
        $role = Role::firstOrCreate(['name' => str_slug($role)]);

        if ($count > 1) {
            return factory(User::class, $count)->create($data)
                ->each(function (User $user) use ($role) {
                    return $user->attachRole($role);
                });
        }

        return factory(User::class)->create($data)->attachRole($role);
    }

    /**
     * @param Loan $loan
     * @param Request $request
     * @return mixed
     */
    protected function approveAndDisburseLoan(Loan $loan, Request $request = null)
    {
        $request = $request ?? $this->request;

        ! $request->user() && $this->setAuthenticatedUserForRequest();

        return $this->dispatch(new DisburseLoanJob($request, $this->dispatch(new ApproveLoanJob($request, $loan))));
    }

    /**
     * test should not break when run on weekends
     */
    public function addWeekdayToCarbonNowOnWeekend()
    {
        if (Carbon::now()->isWeekend()) {
            Carbon::setTestNow(Carbon::now()->addWeekday());
        }
    }

}
