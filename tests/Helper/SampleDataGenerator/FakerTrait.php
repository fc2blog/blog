<?php
declare(strict_types=1);

namespace Fc2blog\Tests\Helper\SampleDataGenerator;

use Faker\Factory;

trait FakerTrait
{
    public function getFaker()
    {
        return Factory::create('ja_JP');
    }
}
