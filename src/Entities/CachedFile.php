<?php

namespace Tapestry\Entities;

class CachedFile
{
    /**
     * File unique identifier.
     *
     * @var string
     */
    private $uid;

    /**
     * File invalidation hash.
     *
     * @var string
     */
    private $hash;

    /**
     * @var string
     */
    private $sourceDirectory;

    /**
     * @var array
     */
    private $layouts;

    /**
     * CachedFile constructor.
     *
     * @param File $file
     * @param array $layouts
     * @param string $sourceDirectory
     */
    public function __construct(File $file, array $layouts = [], $sourceDirectory = '')
    {
        $this->layouts = $layouts;
        $this->sourceDirectory = $sourceDirectory;
        $this->uid = $file->getUid();
        $this->hash = $this->hashFile($file);
    }

    /**
     * Check to see if the current cache entry is still valid.
     *
     * @param File $file
     * @return bool
     * @throws \Exception
     */
    public function check(File $file)
    {
        if ($file->getUid() !== $this->uid) {
            throw new \Exception('This CachedFile is not for uid ['.$file->getUid().']');
        }

        return $this->hash === $this->hashFile($file);
    }

    /**
     * Calculates the invalidation hash for the given File.
     *
     * @param File $file
     * @return string
     */
    private function hashFile(File $file)
    {
        $arr = [];

        foreach ($this->layouts as $layout) {
            if (strpos($layout, '_templates') === false) {
                $layout = '_templates'.DIRECTORY_SEPARATOR.$layout;
            }

            $layoutPathName = $this->sourceDirectory.DIRECTORY_SEPARATOR.$layout.'.phtml';
            if (file_exists($layoutPathName)) {
                array_push($arr, sha1_file($layoutPathName));
            }
        }

        array_push($arr, $file->getLastModified());

        return sha1(implode('.', $arr));
    }
}
