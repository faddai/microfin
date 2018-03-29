<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 06/11/2016
 * Time: 1:49 PM
 */
namespace Tests\Unit;

use App\Console\Commands\RegisterRootUserCommand;
use App\Entities\User;
use Tests\TestCase;

class RegisterRootUserCommandTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAddRootUser()
    {
        $data =  [
            'name' => 'Admin man',
            'email' => 'admin@gome.com',
            'password' => 'secret123'
        ];

        $cmd = new RegisterRootUserCommand;

        $results = $this->callPrivateMethod($cmd, 'registerRootUser', [$data]);

        self::assertInstanceOf(User::class, $results);
    }
}
