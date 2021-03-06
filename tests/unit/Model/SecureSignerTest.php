<?php namespace Solinor\PaymentHighway\Tests\Unit\Model;

use \Solinor\PaymentHighway\Model\Security\SecureSigner;
use Solinor\PaymentHighway\Tests\TestBase;

/**
 * Class Unit_Model_SecureSignerTest
 */

class SecureSignerTest extends TestBase
{
    static $KEYID = 'testKeyId';
    static $KEY = 'somesemirandomkeydata';

    /**
     * @dataProvider provider
     * @test
     */
    public function SignerReturnsCorrectlyFormattedString($method, $uri, array $nameValuePairs )
    {
        $signer = new SecureSigner(self::$KEYID, self::$KEY);
        $sign = $signer->createSignature($method, $uri, $nameValuePairs);

        $this->assertRegExp('/^SPH1 \w+ [0-9a-z]+/', $sign);

    }

    /**
     * @return array
     */
    public function provider()
    {
        return array(
            array(
                'POST',
                '/form/view/add_card',
                array(
                    'sph-testi1' => 'arvo1',
                    'sph-testi2' => 'arvo2',
                    'sph-testi3' => 'arvo3',
                    'sph-testi4' => 'arvo4',
                )
            )
        );
    }
}