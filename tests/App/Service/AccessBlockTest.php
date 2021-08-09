<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Service;

use Fc2blog\Service\AccessBlock;
use Fc2blog\Web\Request;
use PHPUnit\Framework\TestCase;

class AccessBlockTest extends TestCase
{
    public function testAccessBlock(): void
    {
        $jp_ip_address = "133.0.0.1"; // Some JP address https://www.nic.ad.jp/ja/dns/jp-addr-block.html
        $r = new Request(null, null, null, null, null, null, [
            'REMOTE_ADDR' => $jp_ip_address
        ]);

        $ab = new AccessBlock("JP");
        $this->assertTrue($ab->isUserBlockIp($r));

        $ab = new AccessBlock("JP,US");
        $this->assertTrue($ab->isUserBlockIp($r));

        $ab = new AccessBlock("US,JP");
        $this->assertTrue($ab->isUserBlockIp($r));

        $ab = new AccessBlock("US");
        $this->assertFalse($ab->isUserBlockIp($r));

        $ab = new AccessBlock();
        $this->assertFalse($ab->isUserBlockIp($r));
    }
}
