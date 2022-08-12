<?php
namespace MODX\Twilio\Snippet;

abstract class Snippet
{
    /** @var \modX */
    protected $modx;

    /** @var \Twilio */
    protected $twilio;

    /** @var array */
    protected $sp = [];

    /** @var bool */
    protected $debug = false;

    public function __construct(\Twilio &$twilio, array $sp = [])
    {
        $this->twilio =& $twilio;
        $this->modx =& $this->twilio->modx;
        $this->sp = $sp;
        $this->debug = (bool)$this->getOption('debug', 0);
    }

    abstract public function process();

    protected function getOption($key, $default = null, $skipEmpty = false)
    {
        return $this->modx->getOption($key, $this->sp, $default, $skipEmpty);
    }

    /**
     * Encodes a string for URL safe transmission
     *
     * @access public
     * @param string $str
     * @return string
     */
    public function base64urlEncode($str) {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    }

    /**
     * Decodes an URL safe encoded string
     *
     * @access public
     * @param string $str
     * @return string
     */
    public function base64urlDecode($str) {
        return base64_decode(str_pad(strtr($str, '-_', '+/'), strlen($str) % 4, '=', STR_PAD_RIGHT));
    }

    protected function redirect()
    {
        $redirect = (int)$this->getOption('twilioRedirect', null);
        if ($redirect > 0) {
            $this->modx->sendRedirect($this->modx->makeUrl($redirect));
        }
    }
}
