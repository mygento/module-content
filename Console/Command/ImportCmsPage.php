<?php

/**
 * @author Mygento Team
 * @copyright 2019 Mygento (https://www.mygento.ru)
 * @package Mygento_Content
 */

namespace Mygento\Content\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

    public function __construct(
        \Magento\Cms\Api\PageRepositoryInterface $repo,
        \Magento\Cms\Api\Data\PageInterfaceFactory $entityFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directory,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\Api\SearchCriteriaBuilder $builder,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($directory, $file, $builder, $storeManager);

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
        $files = $this->getFiles('cms', 'page_');

        $progress = new \Symfony\Component\Console\Helper\ProgressBar($output, count($files));
        $progress->setFormat('<comment>%message%</comment> %current%/%max% [%bar%] %percent:3s%% %elapsed%');

        foreach ($files as $file) {
            $name = basename($file, '.txt');
            echo $name . PHP_EOL;
            $progress->setMessage('block ' . $name);
            $data = explode('_', $name);
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
                    ->addFilter(\Magento\Cms\Api\Data\PageInterface::IDENTIFIER, $id, 'eq')
                    ->create()
            );

            if ($result->getTotalCount() > 0) {
                if ($input->getOption(self::FORCE_RUN)) {
                    $this->updateEntity($result->getItems(), $file);
                }

                $progress->advance();
                continue;
            }
            $this->createEntity($file, $id, $store);
            $progress->advance();
        }

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    private function updateEntity(array $result, $file)
    {
        $content = $this->file->fileGetContents($file);
        foreach ($result as $entity) {
            try {
                $entity->setContent($content);
                $this->repo->save($entity);
                break;
            } catch (\Exception $e) {
                echo $e->getMessage();
                unset($e);
            }
        }
    }

    private function createEntity($file, $id, $storeId)
    {
        try {
            $entity = $this->entityFactory->create();
            $content = $this->file->fileGetContents($file);
            $entity->setContent($content);
            $entity->setStoreId($storeId);
            $entity->setTitle($id);
            $entity->setIsActive(true);
            $entity->setIdentifier($id);
            $this->repo->save($entity);
        } catch (\Exception $e) {
            echo $e->getMessage();
            unset($e);
        }
    }
}
