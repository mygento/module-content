<?php

/**
 * @author Mygento Team
 * @copyright 2019 Mygento (https://www.mygento.ru)
 * @package Mygento_Content
 */

namespace Mygento\Content\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;

abstract class AbstractExport extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $builder;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fs;

    /**
     * @param \Magento\Framework\Filesystem $fs
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $builder
     */
    public function __construct(
        \Magento\Framework\Filesystem $fs,
        \Magento\Framework\App\Filesystem\DirectoryList $directory,
        \Magento\Framework\Api\SearchCriteriaBuilder $builder
    ) {
        $this->fs = $fs;
        $this->directory = $directory;
        $this->builder = $builder;
        parent::__construct();
    }

    /**
     * @param string $name
     * @param string $content
     * @param string|null $folder
     */
    protected function writeFile(string $name, string $content, $folder = null)
    {
        $dir = 'content' . DIRECTORY_SEPARATOR;
        if ($folder) {
            $dir .= $folder . DIRECTORY_SEPARATOR;
        }
        $writeAdapter = $this->fs->getDirectoryWrite(DirectoryList::VAR_DIR);

        try {
            $writeAdapter->writeFile($dir . $name, $content);
        } catch (\Exception $e) {
            unset($e);
        }
    }
}
