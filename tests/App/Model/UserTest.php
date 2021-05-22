<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Model;

use Fc2blog\Service\UserService;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function test()
    {
        $user = UserService::getByLoginId('testadmin@localhost');
//        var_dump((array)$user);
//        var_dump($user);

//        foreach($user as $prop){
//            echo $prop.PHP_EOL;
//        }
        $this->assertIsIterable($user);

        $this->assertEquals('testadmin@localhost', $user['login_id']);

        $this->assertEquals(7, count($user));
    }
}
