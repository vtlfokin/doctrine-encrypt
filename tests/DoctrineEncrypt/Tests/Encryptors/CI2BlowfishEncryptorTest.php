<?php

namespace DoctrineEncrypt\Tests\Encryptors;
use DoctrineEncrypt\Encryptors\CI2BlowfishEncryptor;

/**
 * Class CI2BlowfishEncryptorTest
 * @package DoctrineEncrypt\Tests\Encryptors
 */
class CI2BlowfishEncryptorTest extends \PHPUnit_Framework_TestCase
{

    public function testEncryptDecrypt()
    {
        $sixteenChars = 'testkeywith16___';
        $e = new CI2BlowfishEncryptor($sixteenChars);

        $plainText = 'test-data';

        $cipherText = $e->encrypt($plainText);
        $this->assertNotEquals($plainText, $cipherText);

        $this->assertEquals($plainText, $e->decrypt($cipherText));
    }

    public function testEncryptDecryptNull()
    {
        $sixteenChars = 'testkeywith16___';
        $e = new CI2BlowfishEncryptor($sixteenChars);

        $plainText = null;

        $cipherText = $e->encrypt($plainText);
        $this->assertNull($cipherText);

        $this->assertNull($e->decrypt($cipherText));
    }

    public function testEncryptDecryptEmpty()
    {
        $sixteenChars = 'testkeywith16___';
        $e = new CI2BlowfishEncryptor($sixteenChars);

        $plainText = '';

        $cipherText = $e->encrypt($plainText);
        $this->assertNotEquals($plainText, $cipherText);

        $this->assertTrue($plainText === $e->decrypt($cipherText));
    }

}