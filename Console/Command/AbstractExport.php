<?php

/**
 * @author Mygento Team
 * @copyright 2019 Mygento (https://www.mygento.ru)
 * @package Mygento_Content
 */

namespace Mygento\Content\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractExport extends \Symfony\Component\Console\Command\Command
{
    /**
     * Force run of export
     */
    const FORCE_RUN = 'force';

    const IDENTIFIER = 'identifier';

    const STORE = 'store_id';

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $builder;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directory;

    /**
     * @var \Mygento\Content\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $fs;

    /**
     * @param \Mygento\Content\Helper\Data $helper
     * @param \Magento\Framework\Filesystem $fs
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $builder
     */
    public function __construct(
        \Mygento\Content\Helper\Data $helper,
        \Magento\Framework\Filesystem $fs,
        \Magento\Framework\App\Filesystem\DirectoryList $directory,
        \Magento\Framework\Api\SearchCriteriaBuilder $builder
    ) {
        $this->fs = $fs;
        $this->directory = $directory;
        $this->builder = $builder;
        $this->helper = $helper;

        parent::__construct();
    }

    /**
     * @param string $name
     * @param string $content
     * @param string|null $folder
     * @param mixed $force
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function writeFile(string $name, string $content, $folder = null, $force = false)
    {
        $dir = 'content' . DIRECTORY_SEPARATOR;
        if ($folder) {
            $dir .= $folder . DIRECTORY_SEPARATOR;
        }
        $writeAdapter = $this->fs->getDirectoryWrite(DirectoryList::VAR_DIR);

        if ($writeAdapter->isExist($dir . $name) && !$force) {
            throw new \Magento\Framework\Exception\FileSystemException(__('File already exists %1', $dir . $name));
        }
        $writeAdapter->writeFile($dir . $name, $content);
    }

    /**
     * @param string $entity
     * @param mixed $item
     * @return string
     */
    protected function getFile(string $entity, $item)
    {
        if (!$item->getStoreCode()) {
            throw new \Magento\Framework\Exception\FileSystemException(
                __('Block %1 does not have store', $item->getIdentifier())
            );
        }

        return $this->helper->createFilename(
            $entity,
            $item->getIdentifier(),
            $item->getStoreCode()
        );
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
            new InputOption(
                self::IDENTIFIER,
                '-i',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'List of Identifiers',
                []
            ),
            new InputOption(
                self::STORE,
                '-s',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'List of Stores',
                []
            ),
        ];
    }
}
