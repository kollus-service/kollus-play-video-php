<?php

namespace Kollus\Component\Container;

class MediaContent extends AbstractContainer
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $kind;

    /**
     * @var string
     */
    private $kind_name;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $upload_file_key;

    /**
     * @var string
     */
    private $duration;

    /**
     * @var Category
     */
    private $category;

    /**
     * @var integer
     */
    private $use_encryption;

    /**
     * @var string
     */
    private $poster_url;

    /**
     * @var string
     */
    private $original_file_name;

    /**
     * @var integer
     */
    private $original_file_size;

    /**
     * @var string
     */
    private $original_file_human_readable_size;

    /**
     * @var MediaInformation
     */
    private $media_information;

    /**
     * @var integer
     */
    private $transcoding_stage;

    /**
     * @var string
     */
    private $transcoding_stage_name;

    /**
     * @var string
     */
    private $media_content_key;

    /**
     * @var integer
     */
    private $status;

    /**
     * @var integer
     */
    private $transcoded_at;

    /**
     * @var integer
     */
    private $created_at;

    /**
     * @var integer
     */
    private $updated_at;

    /**
     * @var ContainerArray
     */
    private $transcoding_files;

    /**
     * @var ContainerArray
     */
    private $subtitles;

    /**
     * @var ContainerArray
     */
    private $channels;

    /**
     * MediaContent constructor.
     * @param array|object $items
     */
    public function __construct($items = [])
    {
        $this->transcoding_files = new ContainerArray();
        $this->subtitles = new ContainerArray();
        $this->channels = new ContainerArray();

        $items = (array)$items;
        if (isset($items['watcher_file_key'])) {
            $items['upload_file_key'] = $items['watcher_file_key'];
            unset($items['watcher_file_key']);
        }

        if (isset($items['snapshot_path'])) {
            $items['poster_path'] = $items['snapshot_path'];
        }

        if (isset($items['media_packages']) && is_array($items['media_packages'])) {
            foreach ($items['media_packages'] as $node) {
                $this->channels->appendElement(new Channel($node));
            }
            unset($items['media_packages']);
        } elseif (isset($items['channels']) && is_array($items['channels'])) {
            foreach ($items['channels'] as $node) {
                $this->channels->appendElement(new Channel($node));
            }
            unset($items['channels']);
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
     * @return int
     */
    public function getKind()
    {
        return $this->kind;
    }

    /**
     * @param int $kind
     */
    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    /**
     * @return string
     */
    public function getKindName()
    {
        return $this->kind_name;
    }

    /**
     * @param string $kind_name
     */
    public function setKindName($kind_name)
    {
        $this->kind_name = $kind_name;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getUploadFileKey()
    {
        return $this->upload_file_key;
    }

    /**
     * @param string $upload_file_key
     */
    public function setUploadFileKey($upload_file_key)
    {
        $this->upload_file_key = $upload_file_key;
    }

    /**
     * @return string
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param string $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return int
     */
    public function getUseEncryption()
    {
        return $this->use_encryption;
    }

    /**
     * @param int $use_encryption
     */
    public function setUseEncryption($use_encryption)
    {
        $this->use_encryption = $use_encryption;
    }

    /**
     * @return string
     */
    public function getPosterUrl()
    {
        return $this->poster_url;
    }

    /**
     * @param string $poster_url
     */
    public function setPosterUrl($poster_url)
    {
        $this->poster_url = $poster_url;
    }

    /**
     * @return string
     */
    public function getOriginalFileName()
    {
        return $this->original_file_name;
    }

    /**
     * @param string $original_file_name
     */
    public function setOriginalFileName($original_file_name)
    {
        $this->original_file_name = $original_file_name;
    }

    /**
     * @return int
     */
    public function getOriginalFileSize()
    {
        return $this->original_file_size;
    }

    /**
     * @param int $original_file_size
     */
    public function setOriginalFileSize($original_file_size)
    {
        $this->original_file_size = $original_file_size;
    }

    /**
     * @return string
     */
    public function getOriginalFileHumanReadableSize()
    {
        return $this->original_file_human_readable_size;
    }

    /**
     * @param string $original_file_human_readable_size
     */
    public function setOriginalFileHumanReadableSize($original_file_human_readable_size)
    {
        $this->original_file_human_readable_size = $original_file_human_readable_size;
    }

    /**
     * @return MediaInformation
     */
    public function getMediaInformation()
    {
        return $this->media_information;
    }

    /**
     * @param MediaInformation $media_information
     */
    public function setMediaInformation($media_information)
    {
        $this->media_information = $media_information;
    }

    /**
     * @return int
     */
    public function getTranscodingStage()
    {
        return $this->transcoding_stage;
    }

    /**
     * @param int $transcoding_stage
     */
    public function setTranscodingStage($transcoding_stage)
    {
        $this->transcoding_stage = $transcoding_stage;
    }

    /**
     * @return string
     */
    public function getTranscodingStageName()
    {
        return $this->transcoding_stage_name;
    }

    /**
     * @param string $transcoding_stage_name
     */
    public function setTranscodingStageName($transcoding_stage_name)
    {
        $this->transcoding_stage_name = $transcoding_stage_name;
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

    /**
     * @return int
     */
    public function getTranscodedAt()
    {
        return $this->transcoded_at;
    }

    /**
     * @param int $transcoded_at
     */
    public function setTranscodedAt($transcoded_at)
    {
        $this->transcoded_at = $transcoded_at;
    }

    /**
     * @return int
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param int $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    /**
     * @return int
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param int $updated_at
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
    }

    /**
     * @return ContainerArray
     */
    public function getTranscodingFiles()
    {
        return $this->transcoding_files;
    }

    /**
     * @param ContainerArray $transcoding_files
     */
    public function setTranscodingFiles($transcoding_files)
    {
        $this->transcoding_files = $transcoding_files;
    }

    /**
     * @return ContainerArray
     */
    public function getSubtitles()
    {
        return $this->subtitles;
    }

    /**
     * @param ContainerArray $subtitles
     */
    public function setSubtitles($subtitles)
    {
        $this->subtitles = $subtitles;
    }

    /**
     * @return ContainerArray
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @param ContainerArray $channels
     */
    public function setChannels($channels)
    {
        $this->channels = $channels;
    }
}
