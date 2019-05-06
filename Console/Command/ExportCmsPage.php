<?php

/**
 * @author Mygento Team
 * @copyright 2019 Mygento (https://www.mygento.ru)
 * @package Mygento_Content
 */

namespace Mygento\Content\Console\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCmsPage extends AbstractExport
{
    /**
     * @var \Magento\Cms\Api\PageRepositoryInterface
     */
    private $repo;

    /**
     * @param \Magento\Cms\Api\PageRepositoryInterface $repo
     * @param \Magento\Framework\Filesystem $fs
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $builder
     */
    public function __construct(
        \Magento\Cms\Api\PageRepositoryInterface $repo,
        \Mygento\Content\Helper\Data $helper,
        \Magento\Framework\Filesystem $fs,
        \Magento\Framework\App\Filesystem\DirectoryList $directory,
        \Magento\Framework\Api\SearchCriteriaBuilder $builder
    ) {
        parent::__construct($helper, $fs, $directory, $builder);
        $this->repo = $repo;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('setup:content:export-cms-page')
            ->setDescription('Export CMS Pages to files')
            ->setDefinition($this->getOptions());
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->repo->getList($this->builder->create());

        $output->setDecorated(true);
        $progress = new ProgressBar($output, $result->getTotalCount());
        $progress->setFormat(
            '<comment>Exporting page %message%</comment> %current%/%max% [%bar%] %percent:3s%% %elapsed%'
        );

        foreach ($result->getItems() as $item) {
            /** @var \Magento\Cms\Api\Data\PageInterface $item */
            $progress->setMessage($item->getIdentifier());

            try {
                $this->writeFile(
                    $this->getFile('page', $item),
                    $this->helper->dumpContent('page', $item),
                    'cms',
                    $input->getOption(self::FORCE_RUN)
                );
            } catch (\Magento\Framework\Exception\FileSystemException $e) {
                $output->writeln('');
                $output->writeln('<error>' . $e->getMessage() . '</error>');
            }
            $progress->advance();
        }
        $output->writeln('');

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
