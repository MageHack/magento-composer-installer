<?php
/**
 * Composer Magento Installer
 */

namespace MagentoHackathon\Composer\Magento\Vcsstrategy;

class Git extends VcsstrategyAbstract
{
    const FILENAME = '.gitignore';

    public function __construct()
    {
        $this->setFile(self::FILENAME);
    }

    /**
     * @param string|SplFileObject $file
     */
    public function setFile($file)
    {
        if (is_string($file)) {
            $file = new \SplFileObject($file, 'a');
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
        $file = $this->getFile();
        $path = preg_replace("/\//", "\/", $path);

        // @todo Use SplFileObject
        $contents = file_get_contents($file->getFilename());
        var_dump($path, $contents);
        $contents = preg_replace("/^{$path}$/sm", '', $contents);
        var_dump($contents, '---------');
        return file_put_contents($file->getFilename(), $contents);
    }

    public function remove($dest)
    {
        // Remove trailing slash (git will also remove this to treat it as a shell glob for fnmatch)
        $dest = preg_replace("/\/$/", '', $dest);

        var_dump($this->isIgnored($dest));

        if ($this->isIgnored($dest)) {
            $this->removeIgnore($dest);
        }
    }
}