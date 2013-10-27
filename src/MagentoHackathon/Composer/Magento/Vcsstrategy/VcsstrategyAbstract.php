<?php
/**
 * Composer Magento Installer
 */

namespace MagentoHackathon\Composer\Magento\Vcsstrategy;

/**
 * Abstract deploy strategy
 */
abstract class VcsstrategyAbstract
{
    /**
     * @var \SplFileObject The gitignore file
     */
    protected $_file = null;

    protected $mappings = array();

    /**
     * Returns the path mappings to map project's directories to magento's directory structure
     *
     * @return array
     */
    public function getMappings()
    {
        return $this->mappings;
    }

    /**
     * Sets path mappings to map project's directories to magento's directory structure
     *
     * @param array $mappings
     */
    public function setMappings(array $mappings)
    {
        $this->mappings = $mappings;
    }

    /**
     * Add a key value pair to mapping
     */
    public function addMapping($key, $value)
    {
        $this->mappings[] = array($key, $value);
    }


    public function deploy()
    {
        foreach ($this->getMappings() as $data) {
            $this->create($data[1]);
        }
        return $this;
    }

    public function clean()
    {
        foreach ($this->getMappings() as $data) {
            $this->remove($data[1]);
        }
        return $this;
    }

    abstract function create($dest);

    abstract function remove($dest);
}