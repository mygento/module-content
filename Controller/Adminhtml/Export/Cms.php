<?php

/**
 * @author Mygento Team
 * @copyright 2019 Mygento (https://www.mygento.ru)
 * @package Mygento_Content
 */

namespace Mygento\Content\Controller\Adminhtml\Export;

class Cms extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonResult;

    /**
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResult
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $jsonResult,
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);

        $this->jsonResult = $jsonResult;
    }

    public function execute()
    {
        $resultJson = $this->jsonResult->create();

        return $resultJson->setData([
            'valid' => true,
            'message' => '',
        ]);
    }
}
