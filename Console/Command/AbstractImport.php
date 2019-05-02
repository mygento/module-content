<?php

/**
 * @author Mygento Team
 * @copyright 2019 Mygento (https://www.mygento.ru)
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
    const FORCE_RUN = 'force';

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $builder;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directory;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    private $stores = [];

    public function __construct(
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

    protected function getStoreId($name)
    {
        if (empty($this->stores)) {
            $stores = $this->storeManager->getStores(true, false);
            foreach ($stores as $store) {
                $this->stores[$store->getCode()] = $store->getId();
            }
        }

        return $this->stores[$name] ?: null;
    }

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
}
