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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImportCmsPage extends AbstractImport
{
    /**
     * @var \Magento\Cms\Api\Data\PageInterfaceFactory
     */
    private $entityFactory;

    /**
     * @var \Magento\Cms\Api\PageRepositoryInterface
     */
    private $repo;

    /**
     * @param \Magento\Cms\Api\PageRepositoryInterface $repo
     * @param \Magento\Cms\Api\Data\PageInterfaceFactory $entityFactory
     * @param \Mygento\Content\Helper\Data $helper
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directory
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $builder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Cms\Api\PageRepositoryInterface $repo,
        \Magento\Cms\Api\Data\PageInterfaceFactory $entityFactory,
        \Mygento\Content\Helper\Data $helper,
        \Magento\Framework\App\Filesystem\DirectoryList $directory,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\Api\SearchCriteriaBuilder $builder,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($helper, $directory, $file, $builder, $storeManager);

        $this->repo = $repo;
        $this->entityFactory = $entityFactory;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('setup:content:import-cms-page')
            ->setDescription('Import CMS Pages to files')
            ->setDefinition($this->getOptions());
        parent::configure();
    }

    /**
     * @param Symfony\Component\Console\Input\InputInterface $input
     * @param Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->setDecorated(true);
        $files = $this->getFiles('cms', 'page|');

        $progress = new ProgressBar($output, count($files));
        $progress->setFormat(
            '<comment>%message%</comment> %current%/%max% [%bar%] %percent:3s%% %elapsed%'
        );

        foreach ($files as $file) {
            $name = pathinfo($file)['filename'];
            $progress->setMessage('block ' . $name);
            $data = $this->splitName($name);
            if (count($data) !== 3) {
                $progress->advance();
                continue;
            }
            $store = $data[2] === 'admin' ? 0 : $this->getStoreId($data[2]);
            $id = $data[1];
            if ($store === null) {
                continue;
            }
            $result = $this->repo->getList(
                $this->builder
                    ->addFilter('store_id', $store, 'eq')
                    ->addFilter(\Magento\Cms\Api\Data\BlockInterface::IDENTIFIER, $id, 'eq')
                    ->create()
            );

            if ($result->getTotalCount() > 0) {
                if ($input->getOption(self::FORCE_RUN)) {
                    $output->writeln('');
                    $output->writeln('<info>' . __('Overwrite %1', $name) . '</info>');
                    $this->updateEntity($result->getItems(), $file);
                } else {
                    $output->writeln('');
                    $output->writeln(
                        '<info>' . __('Skip %1, Page exists', $name) . '</info>'
                    );
                }

                $progress->advance();
                continue;
            }
            $this->createEntity($file, $id, $store);
            $progress->advance();
        }

        $output->writeln('');

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    /**
     * @param array $result
     * @param string $file
     */
    private function updateEntity(array $result, $file)
    {
        $data = \Spyc::YAMLLoad($file);
        foreach ($result as $entity) {
            try {
                $data = \Spyc::YAMLLoad($file);
                $this->helper->fillEntity(
                    'page',
                    $data,
                    $entity
                );
                $this->repo->save($entity);
                break;
            } catch (\Exception $e) {
                echo $e->getMessage();
                unset($e);
            }
        }
    }

    /**
     * @param string $file
     * @param string $id
     * @param string $storeId
     */
    private function createEntity($file, $id, $storeId)
    {
        try {
            $entity = $this->entityFactory->create();
            $data = \Spyc::YAMLLoad($file);
            $this->helper->fillEntity(
                'page',
                array_merge($data, ['store' => $storeId, 'identity' => $id]),
                $entity
            );
            $this->repo->save($entity);
        } catch (\Exception $e) {
            echo $e->getMessage();
            unset($e);
        }
    }
}
