<?php

/**
 * @author Mygento Team
 * @copyright 2019-2022 Mygento (https://www.mygento.ru)
 * @package Mygento_Content
 */

namespace Mygento\Content\Controller\Adminhtml;

use Magento\Framework\App\Filesystem\DirectoryList;

abstract class Import extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $builder;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fs;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonResult;

    /**
     * @var \Mygento\Content\Helper\Data
     */
    private $helper;

    /**
     * @param \Mygento\Content\Helper\Data $helper
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResult
     * @param \Magento\Framework\Filesystem $fs
     * @param DirectoryList $directory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $builder
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Mygento\Content\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResult,
        \Magento\Framework\Filesystem $fs,
        \Magento\Framework\App\Filesystem\DirectoryList $directory,
        \Magento\Framework\Api\SearchCriteriaBuilder $builder,
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);

        $this->jsonResult = $jsonResult;
        $this->directory = $directory;
        $this->fs = $fs;
        $this->builder = $builder;
        $this->helper = $helper;
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
