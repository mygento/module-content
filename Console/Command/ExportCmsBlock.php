<?php

/**
 * @author Mygento Team
 * @copyright 2019 Mygento (https://www.mygento.ru)
 * @package Mygento_Content
 */

namespace Mygento\Content\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCmsBlock extends AbstractExport
{
    /**
     * @var \Magento\Cms\Api\BlockRepositoryInterface
     */
    private $repo;

    public function __construct(
        \Magento\Cms\Api\BlockRepositoryInterface $repo,
        \Magento\Framework\Filesystem $fs,
        \Magento\Framework\App\Filesystem\DirectoryList $directory,
        \Magento\Framework\Api\SearchCriteriaBuilder $builder
    ) {
        parent::__construct($fs, $directory, $builder);
        $this->repo = $repo;
    }

    protected function configure()
    {
        $this->setName('setup:content:export-cms-block')
            ->setDescription('Export CMS Blocks to files');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->repo->getList($this->builder->create());

        $output->setDecorated(true);
        $progress = new \Symfony\Component\Console\Helper\ProgressBar($output, $result->getTotalCount());
        $progress->setFormat('<comment>%message%</comment> %current%/%max% [%bar%] %percent:3s%% %elapsed%');

        foreach ($result->getItems() as $item) {
            /** @var \Magento\Cms\Api\Data\BlockInterace $item */
            $progress->setMessage('block ' . $item->getIdentifier());

            $this->writeFile(
                'block_' . $item->getIdentifier() . '_' . $item->getStoreCode() . '.txt',
                $item->getContent(),
                'cms'
            );
            $progress->advance();
        }

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
