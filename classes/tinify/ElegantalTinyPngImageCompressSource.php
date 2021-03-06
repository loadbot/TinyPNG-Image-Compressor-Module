<?php
/**
 * @author    ELEGANTAL
 * @copyright (c) 2022,
 * @license   Proprietary License - It is forbidden to resell or redistribute copies of the module or modified copies of the module.
 */

/**
 * Source class
 */
class ElegantalTinyPngImageCompressSource
{

    private $url;
    private $commands;

    public static function fromFile($path)
    {
        return self::fromBuffer(Tools::file_get_contents($path));
    }

    public static function fromBuffer($string)
    {
        $response = ElegantalTinyPngImageCompressTinify::getClient()->request("post", "/shrink", $string);
        return new self($response->headers["location"]);
    }

    public static function fromUrl($url)
    {
        $body = array("source" => array("url" => $url));
        $response = ElegantalTinyPngImageCompressTinify::getClient()->request("post", "/shrink", $body);
        return new self($response->headers["location"]);
    }

    public function __construct($url, $commands = array())
    {
        $this->url = $url;
        $this->commands = $commands;
    }

    public function preserve()
    {
        $options = $this->flatten(func_get_args());
        $commands = array_merge($this->commands, array("preserve" => $options));
        return new self($this->url, $commands);
    }

    public function resize($options)
    {
        $commands = array_merge($this->commands, array("resize" => $options));
        return new self($this->url, $commands);
    }

    public function store($options)
    {
        $response = ElegantalTinyPngImageCompressTinify::getClient()->request("post", $this->url, array_merge($this->commands, array("store" => $options)));
        return new ElegantalTinyPngImageCompressResult($response->headers, $response->body);
    }

    public function result()
    {
        $response = ElegantalTinyPngImageCompressTinify::getClient()->request("get", $this->url, $this->commands);
        return new ElegantalTinyPngImageCompressResult($response->headers, $response->body);
    }

    public function toFile($path)
    {
        return $this->result()->toFile($path);
    }

    public function toBuffer()
    {
        return $this->result()->toBuffer();
    }

    private static function flatten($options)
    {
        $flattened = array();
        foreach ($options as $option) {
            if (is_array($option)) {
                $flattened = array_merge($flattened, $option);
            } else {
                array_push($flattened, $option);
            }
        }
        return $flattened;
    }
}
