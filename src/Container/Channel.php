<?php

namespace Kollus\Component\Container;

class Channel extends AbstractContainer
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $key;

    /**
     * @var integer
     */
    private $count_of_media_contents;

    /**
     * @var string
     */
    private $media_content_key;

    /**
     * @var integer
     */
    private $use_pingback;

    /**
     * @var integer
     */
    private $status;

    /**
     * Channel constructor.
     * @param object|array $items
     */
    public function __construct($items = [])
    {
        $items = (array)$items;
        if (isset($items['channel_name'])) {
            $items['name'] = $items['channel_name'];
            unset($items['channel_name']);
        }

        if (isset($items['channel_key'])) {
            $items['key'] = $items['channel_key'];
            unset($items['channel_key']);
        }
        parent::__construct($items);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return int
     */
    public function getCountOfMediaContents()
    {
        return $this->count_of_media_contents;
    }

    /**
     * @param int $count_of_media_contents
     */
    public function setCountOfMediaContents($count_of_media_contents)
    {
        $this->count_of_media_contents = $count_of_media_contents;
    }

    /**
     * @return string
     */
    public function getMediaContentKey()
    {
        return $this->media_content_key;
    }

    /**
     * @param string $media_content_key
     */
    public function setMediaContentKey($media_content_key)
    {
        $this->media_content_key = $media_content_key;
    }

    /**
     * @return int
     */
    public function getUsePingback()
    {
        return $this->use_pingback;
    }

    /**
     * @param int $use_pingback
     */
    public function setUsePingback($use_pingback)
    {
        $this->use_pingback = $use_pingback;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}
