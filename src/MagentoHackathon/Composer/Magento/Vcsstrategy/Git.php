<?php
/**
 * Composer Magento Installer
 */

namespace MagentoHackathon\Composer\Magento\Vcsstrategy;

/**
 * A class for handling Git version control system
 *
 * Assumes (incorrectly) that git files are located in the current working directory
 * @todo Fix false assumption
 *
 * @package MagentoHackathon\Composer\Magento\Vcsstrategy
 */
class Git extends VcsstrategyAbstract
{
    const GIT_DIRNAME = '.git';
    const GITIGNORE_FILENAME = '.gitignore';

    /**
     * @var \SplFileObject
     */
    protected $_file = null;

    /**
     * @var array
     */
    protected $_fileContents = null;

    /**
     * @var array
     */
    protected $_fileContentsBuffer = null;

    /**
     * Register the gitignore file ready for use
     */
    public function __construct()
    {
        $this->setFile(self::GITIGNORE_FILENAME);
    }

    /**
     * Determine if Git is the version control system in use
     *
     * @param string $vendorDir
     * @return bool
     */
    static function isApplicable()
    {
        return file_exists(getcwd() . DIRECTORY_SEPARATOR . self::GIT_DIRNAME);
    }

    /**
     * Return an instance of SplFileObject for specified file. File is created if it does not exist
     *
     * @param string|SplFileObject $file
     */
    public function setFile($file)
    {
        if (is_string($file)) {
            $file = new \SplFileObject($file, 'c+');
        }
        $this->_file = $file;
    }

    /**
     * @return \SplFileObject
     */
    public function getFile()
    {
        return $this->_file;
    }

    public function getFileContents()
    {
        if (!$this->_fileContents) {
            $this->_fileContents = array();
            $file = $this->getFile();
            foreach ($file as $line) {
                array_push($this->_fileContents, $line);
            }
        }
        return $this->_fileContents;
    }

    public function getFileContentsBuffer()
    {
        if (!$this->_fileContentsBuffer) {
            $this->_fileContentsBuffer = $this->getFileContents();
        }
        return $this->_fileContentsBuffer;
    }

    public function setFileContentsBuffer($array)
    {
        $this->_fileContentsBuffer = $array;
    }

    public function isIgnored($path)
    {
        $command = sprintf("git status -u --ignored --porcelain %s", $path);
        if ($output = shell_exec($command)) {
            if ($match = preg_match("/^!! (.*)$/", $output, $matches)) {
                return true;
            } elseif ($match = preg_match("/^\?\? (.*)$/", $output, $matches)) {
                return false;
            }
        }

        return null;
    }

    protected function addIgnore($path)
    {
        $file = $this->getFile();
        return $file->fwrite("\n" . $path);
    }

    public function create($dest)
    {
        // Remove trailing slash (git will also remove this to treat it as a shell glob for fnmatch)
        $dest = preg_replace("/\/$/", '', $dest);

        if (!$this->isIgnored($dest)) {
            $this->addIgnore($dest);
        }
    }

    protected function removeIgnore($path)
    {
        $path = preg_replace("/\//", "\/", $path);

        $fileBuffer = $this->getFileContentsBuffer();
        foreach ($fileBuffer as $lineNumber => $line) {
            if (preg_match("/^{$path}$/", $line)) {
                unset($fileBuffer[$lineNumber]);
            }
        }
        $this->setFileContentsBuffer($fileBuffer);

        $file = $this->getFile();
        $file->ftruncate(0);
        $file->rewind();
        $file->fwrite(implode('', $this->getFileContentsBuffer()));
    }

    public function remove($dest)
    {
        // Remove trailing slash (git will also remove this to treat it as a shell glob for fnmatch)
        $dest = preg_replace("/\/$/", '', $dest);

        if ($this->isIgnored($dest)) {
            $this->removeIgnore($dest);
        }
    }
}