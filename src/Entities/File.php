<?php namespace Tapestry\Entities;

use DateTime;
use Symfony\Component\Finder\SplFileInfo;

class File implements ProjectFileInterface
{
    private $uid = null;

    /**
     * @var SplFileInfo
     */
    private $fileInfo;

    /**
     * File data, usually found via frontmatter
     * @var array
     */
    private $data = [];

    /**
     * File Content
     * @var string
     */
    private $content = '';

    /**
     * Has the file content been loaded
     * @var bool
     */
    private $loaded = false;

    /**
     * If a file has been set as deferred it means that it will be picked up by a ContentType generator such as the Blog
     * generator. This is set by the LoadSourceFiles step when a file is considered a template belonging to a content type.
     *
     * @todo this functionality
     * @var bool
     */
    private $deferred = false;

    /**
     * Is true if the file info has been overwritten.
     * @var bool
     */
    private $overWritten = false;

    /**
     * @var Permalink
     */
    private $permalink;

    /**
     * File constructor.
     * @param SplFileInfo $fileInfo
     */
    public function __construct(SplFileInfo $fileInfo)
    {
        $this->fileInfo = $fileInfo;
        $defaultData = [
            'date' => DateTime::createFromFormat('U', $fileInfo->getMTime())
        ];

        $this->permalink = new Permalink();

        preg_match('/^(\d{4}-\d{2}-\d{2})-(.*)/', $this->fileInfo->getBasename('.'.$this->fileInfo->getExtension()), $matches);
        if (count($matches) === 3) {
            $defaultData['date'] = new DateTime($matches[1]);
            $defaultData['draft'] = false;
            $defaultData['slug'] = $matches[2];
            $defaultData['title'] = ucfirst(str_replace('-', ' ', $defaultData['slug']));
        }
        $this->setData($defaultData);

        if (substr($this->fileInfo->getRelativePath(), 0, 1) === '_' || substr($this->fileInfo->getFilename(), 0, 1) === '_') {
            $this->deferred = true;
        }
    }

    /**
     * Get identifier for this file, the relative pathname is unique to each file so that should be good enough
     * @return string
     */
    public function getUid()
    {
        if (is_null($this->uid)){
            $this->uid = str_replace('.', '_', $this->getFileInfo()->getRelativePathname());
        }
        return $this->uid;
    }

    /**
     * Returns the SplFileInfo class that the Symfony Finder created
     *
     * @return SplFileInfo
     */
    public function getFileInfo()
    {
        return $this->fileInfo;
    }

    /**
     *
     * @param SplFileInfo $fileInfo
     */
    public function setFileInfo(SplFileInfo $fileInfo)
    {
        $this->fileInfo = $fileInfo;
        $this->overWritten = true;
    }

    /**
     * Returns the file content, this will be excluding any frontmatter
     * @return string
     * @throws \Exception
     */
    public function getContent()
    {
        if (!$this->isLoaded()) {
            throw new \Exception('The file [' . $this->fileInfo->getRelativePathname() . '] has not been loaded.');
        }
        return $this->content;
    }

    public function getRenderedContent()
    {
        if (!$this->isLoaded()) {
            throw new \Exception('The file [' . $this->fileInfo->getRelativePathname() . '] has not been loaded.');
        }

        return 'todo, add rendered content code...';
    }

    /**
     * Set the files content, this should be excluding any frontmatter
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
        $this->loaded = true;
    }

    public function setPermalink(Permalink $permalink) {
        $this->permalink = $permalink;
    }

    public function getPermalink()
    {
        return $this->permalink->getCompiled($this);
    }

    /**
     * A file can be considered loaded once its content property has been set, that way you know any frontmatter has
     * also been injected into the File objects data property.
     *
     * @return bool
     */
    public function isLoaded()
    {
        return $this->loaded;
    }

    public function setDeferred($value)
    {
        $this->deferred = boolval($value);
    }

    public function isDeferred()
    {
        return $this->deferred;
    }

    /**
     * Set this files data (via frontmatter or other source)
     *
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = array_merge($this->data, $data);
    }

    /**
     * Return this files data (set via frontmatter if any is found)
     *
     * @param null $key
     * @param null $default
     * @return array|mixed|null
     */
    public function getData($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->data;
        }
        if (!isset($this->data[$key])) {
            return $default;
        }
        return $this->data[$key];
    }

    /**
     * Get the content of the file that this object relates to.
     *
     * @return string
     */
    public function getFileContent()
    {
        return file_get_contents($this->fileInfo->getPathname());
    }
}