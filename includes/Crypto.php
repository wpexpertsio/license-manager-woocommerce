<?php

namespace LicenseManagerForWooCommerce;

use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto as DefuseCrypto;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;

defined('ABSPATH') || exit;

class Crypto
{
    /**
     * The defuse key file name.
     */
    const DEFUSE_FILE = 'defuse.txt';

    /**
     * The secret file name.
     */
    const SECRET_FILE = 'secret.txt';

    /**
     * Folder name inside the wp_contents directory where the cryptographic secrets are stored.
     */
    const PLUGIN_SLUG = 'lmfwc-files';

    /**
     * The defuse key file content.
     *
     * @var string
     */
    private $keyAscii;

    /**
     * The hashing key
     * 
     * @var string
     */
    private $keySecret;

    /**
     * Directory path to the plugin folder inside wp-content/uploads.
     * 
     * @var string
     */
    private $uploads_dir;

    /**
     * Setup Constructor.
     */
    public function __construct()
    {
        $uploads = wp_upload_dir(null, false);

        $this->uploads_dir = $uploads['basedir'] . '/lmfwc-files/';
        $this->setDefuse();
        $this->setSecret();

        add_filter('lmfwc_encrypt', array($this, 'encrypt'), 10, 1);
        add_filter('lmfwc_decrypt', array($this, 'decrypt'), 10, 1);
        add_filter('lmfwc_hash',    array($this, 'hash'),    10, 1);
        add_filter('lmfwc_activation_hash',    array($this, 'activationHash'),    10, 1);

    }

    /**
     * Sets the defuse encryption key.
     */
    private function setDefuse()
    {
        /* When the cryptographic secrets are loaded into these constants, no other files are needed */
        if (defined('LMFWC_PLUGIN_DEFUSE')) {
            $this->keyAscii = LMFWC_PLUGIN_DEFUSE;
            return;
        }

        if (file_exists($this->uploads_dir . self::DEFUSE_FILE)) {
            $this->keyAscii = file_get_contents($this->uploads_dir . self::DEFUSE_FILE);
        }
    }

    /**
     * Sets the cryptographic secret.
     */
    private function setSecret()
    {
        /* When the cryptographic secrets are loaded into these constants, no other files are needed */
        if (defined('LMFWC_PLUGIN_SECRET')) {
            $this->keySecret = LMFWC_PLUGIN_SECRET;
            return;
        }

        if (file_exists($this->uploads_dir . self::SECRET_FILE)) {
            $this->keySecret = file_get_contents($this->uploads_dir . self::SECRET_FILE);
        }
    }

    /**
     * Load the defuse key from the plugin folder.
     *
     * @throws BadFormatException
     * @throws EnvironmentIsBrokenException
     *
     * @return Key|string
     */
    private function loadEncryptionKeyFromConfig()
    {
        if (!$this->keyAscii) {
            return '';
        }

        return Key::loadFromAsciiSafeString($this->keyAscii);
    }

    /**
     * Encrypt a string and return the encrypted cipher text.
     *
     * @param string $value
     *
     * @throws BadFormatException
     * @throws EnvironmentIsBrokenException
     *
     * @return string
     */
    public function encrypt($value)
    {
        return DefuseCrypto::encrypt($value, $this->loadEncryptionKeyFromConfig());
    }

    /**
     * Decrypt a cipher and return the decrypted value.
     *
     * @param string $cipher
     *
     * @throws BadFormatException
     * @throws EnvironmentIsBrokenException
     *
     * @return string
     */
    public function decrypt($cipher)
    {
        if (!$cipher) {
            return '';
        }

        try {
            return DefuseCrypto::decrypt($cipher, $this->loadEncryptionKeyFromConfig());
        } catch (WrongKeyOrModifiedCiphertextException $ex) {
            // An attack! Either the wrong key was loaded, or the cipher text has changed since it was created -- either
            // corrupted in the database or intentionally modified by someone trying to carry out an attack.
        }
    }

    /**
     * Hashes the given string using the HMAC-SHA256 method.
     *
     * @param string $value
     *
     * @return false|string
     */
    public function hash($value)
    {
        return hash_hmac('sha256', $value, $this->keySecret);
    }

    public function activationHash( $license_key ) {
        return sha1( sprintf( '%s%s%s%s', $license_key, lmfwc_rand_hash(), mt_rand( 10000, 1000000 ), lmfwc_clientIp() ) );
    }

}