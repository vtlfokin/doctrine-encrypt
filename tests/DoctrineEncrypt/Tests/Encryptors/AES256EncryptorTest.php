<?php

namespace DoctrineEncrypt\Tests\Encryptors;
use DoctrineEncrypt\Encryptors\AES256Encryptor;

/**
 * Created by PhpStorm.
 * User: dustin
 * Date: 04/02/16
 * Time: 5:50 PM
 */
class AES256EncryptorTest extends \PHPUnit_Framework_TestCase
{

    public function testEncryptDecrypt()
    {
        $sixteenChars = 'testkeywith16___';
        $e = new AES256Encryptor($sixteenChars);

        $plainText = 'test-data';

        $cipherText = $e->encrypt($plainText);
        $this->assertNotEquals($plainText, $cipherText);

        $this->assertEquals($plainText, $e->decrypt($cipherText));
    }

    public function testEncryptDecryptNull()
    {
        $sixteenChars = 'testkeywith16___';
        $e = new AES256Encryptor($sixteenChars);

        $plainText = null;

        $cipherText = $e->encrypt($plainText);
        $this->assertNull($cipherText);

        $this->assertNull($e->decrypt($cipherText));
    }

    public function testEncryptDecryptEmpty()
    {
        $sixteenChars = 'testkeywith16___';
        $e = new AES256Encryptor($sixteenChars);

        $plainText = '';

        $cipherText = $e->encrypt($plainText);
        $this->assertNotEquals($plainText, $cipherText);

        $this->assertTrue($plainText === $e->decrypt($cipherText));
    }

}