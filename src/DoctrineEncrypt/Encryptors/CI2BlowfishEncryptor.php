<?php

namespace DoctrineEncrypt\Encryptors;

/**
 * Class CI2BlowfishEncryptor
 * @package Components\Core\Encrypters
 *
 * Encryptor compatible with codeigniter 2 encrypt library, but use openssl
 */
class CI2BlowfishEncryptor implements EncryptorInterface
{
    /**
     * Secret key for algorythm
     * @var string
     */
    private $secretKey;

    /**
     * algorythm
     * @var string
     */
    private $cipher = 'bf-cbc';

    /**
     * @var string
     */
    private $hashType = 'sha1';

    /**
     * @var int
     */
    private $encryptOptions = OPENSSL_ZERO_PADDING | OPENSSL_RAW_DATA;

    /**
     * Initialization of encryptor
     * @param string $key Secret key for algorythm
     */
    public function __construct($key)
    {
        $this->secretKey = $key;
    }

    /**
     * Implementation of EncryptorInterface encrypt method
     *
     * @param string $message
     *
     * @return string|null
     */
    public function encrypt($message)
    {
        if (is_null($message)) {
            return $message;
        }

        $init_size = \openssl_cipher_iv_length($this->cipher);
        $init_vect = \openssl_random_pseudo_bytes($init_size);

        $message_padded = $message;
        if (strlen($message_padded) % $init_size) {
            $padLength = strlen($message_padded) + $init_size - strlen($message_padded) % $init_size;
            $message_padded = str_pad($message_padded, $padLength, "\0");
        }

        $value = \openssl_encrypt(
            $message_padded,
            $this->cipher,
            $this->getKey(),
            $this->encryptOptions,
            $init_vect
        );
        $value = $this->addCipherNoise($init_vect . $value, $this->getKey());

        return trim(base64_encode($value));
    }

    /**
     * Implementation of EncryptorInterface decrypt method
     * @param string $data
     * @return string|null
     */
    public function decrypt($data)
    {
        if (is_null($data)) {
            return $data;
        }

        $data = base64_decode($data);

        $data = $this->removeCipherNoise($data, $this->getKey());
        $init_size = openssl_cipher_iv_length($this->cipher);

        if ($init_size > strlen($data)) {
            return null;
        }

        $init_vect = substr($data, 0, $init_size);
        $data = substr($data, $init_size);
        return rtrim(
            \openssl_decrypt(
                $data,
                $this->cipher,
                $this->getKey(),
                $this->encryptOptions,
                $init_vect
            ),
            "\0"
        );
    }

    private function getKey()
    {
        return md5($this->secretKey);
    }

    /**
     * Adds permuted noise to the IV + encrypted data to protect
     * against Man-in-the-middle attacks on CBC mode ciphers
     * http://www.ciphersbyritter.com/GLOSSARY.HTM#IV
     *
     * Function description
     *
     * @param string $data Encrypted data
     * @param string $key  Secret key
     *
     * @return string
     * @access private
     */
    private function addCipherNoise($data, $key)
    {
        $keyhash = $this->hash($key);
        $keylen = strlen($keyhash);
        $str = '';

        for ($i = 0, $j = 0, $len = strlen($data); $i < $len; ++$i, ++$j) {
            if ($j >= $keylen) {
                $j = 0;
            }

            $str .= chr((ord($data[$i]) + ord($keyhash[$j])) % 256);
        }

        return $str;
    }

    /**
     * Removes permuted noise from the IV + encrypted data, reversing
     * addCipherNoise()
     *
     * Function description
     *
     * @param string $data Encrypted data
     * @param string $key  Secret key
     *
     * @return string
     * @access public
     */
    private function removeCipherNoise($data, $key)
    {
        $keyhash = $this->hash($key);
        $keylen = strlen($keyhash);
        $str = '';

        for ($i = 0, $j = 0, $len = strlen($data); $i < $len; ++$i, ++$j) {
            if ($j >= $keylen) {
                $j = 0;
            }

            $temp = ord($data[$i]) - ord($keyhash[$j]);

            if ($temp < 0) {
                $temp = $temp + 256;
            }

            $str .= chr($temp);
        }

        return $str;
    }

    /**
     * Hash encode a string
     *
     * @param    string
     * @return    string
     */
    private function hash($str)
    {
        return ($this->hashType == 'sha1') ? $this->sha1($str) : md5($str);
    }

    private function sha1($str)
    {
        if (!function_exists('sha1')) {
            if (!function_exists('mhash')) {
                throw new \Exception('No sha1 realization');
            }

            return bin2hex(mhash(MHASH_SHA1, $str));
        }

        return sha1($str);
    }
}
