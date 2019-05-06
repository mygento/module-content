<?php

/**
 * @author Mygento Team
 * @copyright 2019 Mygento (https://www.mygento.ru)
 * @package Mygento_Content
 */

namespace Mygento\Content\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const GLUE = '|';

    public function createFilename(
        string $entity,
        string $identity,
        string $store
    ): string {
        switch ($entity) {
            case 'page':
            case 'block':
                $ext = '.yaml';
                break;
            default:
                $ext = '.txt';
        }

        return $entity . self::GLUE . str_replace('/', ' ', $identity) . self::GLUE . '' . $store . $ext;
    }

    public function splitName(string $file): array
    {
        return explode(self::GLUE, $file);
    }

    public function dumpContent(string $entity, $item)
    {
        $content = '';
        switch ($entity) {
            case 'page':
                $content = \Spyc::YAMLDump([
                    'title' => $item->getTitle(),
                    'layout' => $item->getPageLayout(),
                    'heading' => $item->getContentHeading(),
                    'xml' => $item->getLayoutUpdateXml(),
                    'active' => $item->isActive(),
                    'meta_title' => $item->getMetaTitle(),
                    'meta_desc' => $item->getMetaDescription(),
                    'meta_key' => $item->getMetaKeywords(),
                    'content' => $item->getContent(),
                ]);
                break;
            case 'block':
                $content = \Spyc::YAMLDump([
                    'title' => $item->getTitle(),
                    'active' => $item->isActive(),
                    'content' => $item->getContent(),
                ]);
                break;
            default:
                $content = $item->getContent();
        }

        return $content;
    }

    public function fillEntity(string $entityType, array $data, $entity)
    {
        switch ($entityType) {
            case 'page':
                $entity->setTitle($data['title']);
                $entity->setPageLayout($data['layout']);
                $entity->setContentHeading($data['heading']);
                $entity->setLayoutUpdateXml($data['xml']);
                $entity->setIsActive($data['active']);
                $entity->setMetaTitle($data['meta_title']);
                $entity->setMetaDescription($data['meta_desc']);
                $entity->setMetaKeywords($data['meta_key']);
                $entity->setContent($data['content']);
                break;
            case 'block':
                $entity->setTitle($data['title']);
                $entity->setIsActive($data['active']);
                $entity->setContent($data['content']);
                break;
            default:
                $entity->setContent($data['content']);
        }

        if (isset($data['store']) && $data['store']) {
            $entity->setStoreId($data['store']);
        }

        if (isset($data['identity']) && $data['identity']) {
            $entity->setIdentifier($data['identity']);
        }

        return $entity;
    }
}
