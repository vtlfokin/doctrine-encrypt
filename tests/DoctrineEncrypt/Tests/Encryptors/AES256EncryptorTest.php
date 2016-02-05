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
        $e = new AES256Encryptor('testkey');

        $plainText = 'test-data';

        $cipherText = $e->encrypt($plainText);
        $this->assertNotEquals($plainText, $cipherText);

        $this->assertEquals($plainText, $e->decrypt($cipherText));
    }

    public function testEncryptDecryptNull()
    {
        $e = new AES256Encryptor('testkey');

        $plainText = null;

        $cipherText = $e->encrypt($plainText);
        $this->assertNull($cipherText);

        $this->assertNull($e->decrypt($cipherText));
    }

    public function testEncryptDecryptEmpty()
    {
        $e = new AES256Encryptor('testkey');

        $plainText = '';

        $cipherText = $e->encrypt($plainText);
        $this->assertNotEquals($plainText, $cipherText);

        $this->assertTrue($plainText === $e->decrypt($cipherText));
    }

}