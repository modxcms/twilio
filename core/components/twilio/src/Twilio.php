<?php

namespace MODX\Twilio;

require_once dirname(__FILE__, 2) . '/lib/FixedBitNotation.php';

use FixedBitNotation;
use MODX\Revolution\modX;

/**
 * The main Twilio service class.
 *
 * @package twilio
 */
class Twilio
{
    public $modx = null;
    public $namespace = 'twilio';
    public $cache = null;
    public $options = [];

    public function __construct(modX &$modx, array $options = [])
    {
        $this->modx =& $modx;

        $corePath = $this->getOption(
            'core_path',
            $options,
            $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/twilio/'
        );
        $assetsPath = $this->getOption(
            'assets_path',
            $options,
            $this->modx->getOption('assets_path', null, MODX_ASSETS_PATH) . 'components/twilio/'
        );
        $assetsUrl = $this->getOption(
            'assets_url',
            $options,
            $this->modx->getOption('assets_url', null, MODX_ASSETS_URL) . 'components/twilio/'
        );

        /* loads some default paths for easier management */
        $this->options = array_merge([
            'namespace' => $this->namespace,
            'corePath' => $corePath,
            'snippetsPath' => $corePath . 'src/Snippet/',
            'templatesPath' => $corePath . 'templates/',
            'assetsPath' => $assetsPath,
            'assetsUrl' => $assetsUrl,
            'jsUrl' => $assetsUrl . 'js/',
            'cssUrl' => $assetsUrl . 'css/',
        ], $options);

        $this->modx->lexicon->load('twilio:default');
    }

    /**
     * Get a local configuration option or a namespaced system setting by key.
     *
     * @param string $key The option key to search for.
     * @param array $options An array of options that override local options.
     * @param mixed $default The default value returned if the option is not found locally or as a
     * namespaced system setting; by default this value is null.
     * @return mixed The option value or the default value specified.
     */
    public function getOption($key, $options = array(), $default = null)
    {
        $option = $default;
        if (!empty($key) && is_string($key)) {
            if ($options !== null && array_key_exists($key, $options)) {
                $option = $options[$key];
            } elseif (array_key_exists($key, $this->options)) {
                $option = $this->options[$key];
            } elseif (array_key_exists("{$this->namespace}.{$key}", $this->modx->config)) {
                $option = $this->modx->getOption("{$this->namespace}.{$key}");
            }
        }
        return $option;
    }

    public function getCode($user)
    {
        $profile = $user->getOne('Profile');
        $extended = $profile->get('extended');
        $secret = $extended['twilio_totp']['binding']['secret'] ?? null;
        if ($secret) {
            $base32 = new FixedBitNotation(5, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567', true, true);
            $secret = $base32->decode($secret);
            $time = floor(time() / 30);
            $time = pack("N", $time);
            $time = str_pad($time, 8, chr(0), STR_PAD_LEFT);
            $hash = hash_hmac('sha1', $time, $secret, true);
            $offset = ord(substr($hash, -1));
            $offset &= 0xF;

            $truncatedHash = substr($hash, $offset);
            $truncatedHash = unpack("N", substr($truncatedHash, 0, 4));
            $truncatedHash = $truncatedHash[1] & 0x7FFFFFFF;

            return str_pad($truncatedHash % (10 ** 6), 6, "0", STR_PAD_LEFT);
        }
        return null;
    }
}
