<?php

namespace Kollus\Component\Container;

class MediaItem extends AbstractContainer
{
    /**
     * @var string
     */
    private $media_content_key;

    /**
     * @var string
     */
    private $profile_key;

    /**
     * @var bool
     */
    private $is_intro;

    /**
     * @var bool
     */
    private $is_seekable;

    /**
     * @var int
     */
    private $seekable_end;

    /**
     * @var bool
     */
    private $disable_playrate;

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
     * @return string
     */
    public function getProfileKey()
    {
        return $this->profile_key;
    }

    /**
     * @param string $profile_key
     */
    public function setProfileKey($profile_key)
    {
        $this->profile_key = $profile_key;
    }

    /**
     * @return bool
     */
    public function isIntro()
    {
        return $this->is_intro;
    }

    /**
     * @param bool $is_intro
     */
    public function setIsIntro($is_intro)
    {
        $this->is_intro = $is_intro;
    }

    /**
     * @return bool
     */
    public function isSeekable()
    {
        return $this->is_seekable;
    }

    /**
     * @param bool $is_seekable
     */
    public function setIsSeekable($is_seekable)
    {
        $this->is_seekable = $is_seekable;
    }

    /**
     * @return int
     */
    public function getSeekableEnd()
    {
        return $this->seekable_end;
    }

    /**
     * @param int $seekable_end
     */
    public function setSeekableEnd($seekable_end)
    {
        $this->seekable_end = $seekable_end;
    }

    /**
     * @return bool
     */
    public function isDisablePlayrate()
    {
        return $this->disable_playrate;
    }

    /**
     * @param bool $disable_playrate
     */
    public function setDisablePlayrate($disable_playrate)
    {
        $this->disable_playrate = $disable_playrate;
    }

}
