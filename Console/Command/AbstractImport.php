<?php

/**
 * @author Mygento Team
 * @copyright 2019-2022 Mygento (https://www.mygento.ru)
 * @package Mygento_Content
 */

namespace Mygento\Content\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractImport extends \Symfony\Component\Console\Command\Command
{
    /**
     * Force run of import
     */
    public const FORCE_RUN = 'force';

    public const GLUE = '|';

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $builder;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;

    /**
     * @var \Mygento\Content\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $stores = [];

    /**
     * @param \Mygento\Content\Helper\Data $helper
     * @param DirectoryList $directory
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $builder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Mygento\Content\Helper\Data $helper,
        \Magento\Framework\App\Filesystem\DirectoryList $directory,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\Api\SearchCriteriaBuilder $builder,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct();

        $this->directory = $directory;
        $this->file = $file;
        $this->builder = $builder;
        $this->storeManager = $storeManager;
        $this->stores = [];
        $this->helper = $helper;
    }

    /**
     * @param string|null $folder
     * @param string|null $filter
     */
    protected function getFiles($folder = null, $filter = null)
    {
        $dir = 'content' . DIRECTORY_SEPARATOR;
        if ($folder) {
            $dir .= $folder . DIRECTORY_SEPARATOR;
        }
        $contentDir = $this->directory->getPath(DirectoryList::VAR_DIR) . DIRECTORY_SEPARATOR . $dir;

        $result = [];

        try {
            $directory = $this->file->readDirectory($contentDir);
            foreach ($directory as $file) {
                if (!$filter) {
                    $result[] = $file;
                    continue;
                }

                if (strpos($file, $filter) === false) {
                    continue;
                }
                $result[] = $file;
            }
        } catch (\Exception $e) {
            unset($e);
        }

        return $result;
    }

    /**
     * @param string $name
     * @return int|null
     */
    protected function getStoreId($name)
    {
        if (empty($this->stores)) {
            $stores = $this->storeManager->getStores(true, false);
            foreach ($stores as $store) {
                $this->stores[$store->getCode()] = $store->getId();
            }
        }

        return $this->stores[$name] ?? null;
    }

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            new InputOption(
                self::FORCE_RUN,
                '-f',
                InputOption::VALUE_NONE,
                'Overwrite'
            ),
        ];
    }

    /**
     * @param string $file
     * @return array
     */
    protected function splitName(string $file): array
    {
        return $this->helper->splitName(str_replace(' ', '/', $file));
    }
}
