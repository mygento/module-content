<?php

/**
 * @author Mygento Team
 * @copyright 2019 Mygento (https://www.mygento.ru)
 * @package Mygento_Content
 */

namespace Mygento\Content\Controller\Adminhtml\Import;

class CmsBlock extends \Mygento\Content\Controller\Adminhtml\Import
{
    /**
     * @var \Magento\Cms\Api\BlockRepositoryInterface
     */
    private $repo;

    /**
     * @param \Magento\Cms\Api\BlockRepositoryInterface $repo
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResult
     * @param \Magento\Framework\Filesystem $fs
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $builder
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Cms\Api\BlockRepositoryInterface $repo,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResult,
        \Magento\Framework\Filesystem $fs,
        \Magento\Framework\App\Filesystem\DirectoryList $directory,
        \Magento\Framework\Api\SearchCriteriaBuilder $builder,
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($jsonResult, $fs, $directory, $builder, $context);
        $this->repo = $repo;
    }

    /**
     * Execute action based on request and return result
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $resultJson = $this->jsonResult->create();

        $result = $this->repo->getList($this->builder->create());

        foreach ($result->getItems() as $item) {
            /** @var \Magento\Cms\Api\Data\BlockInterace $item */
            $this->writeFile(
                'block_' . $item->getIdentifier() . '_' . $item->getStoreCode() . '.txt',
                $item->getContent(),
                'cms'
            );
        }

        return $resultJson->setData([
            'success' => true,
        ]);
    }
}
