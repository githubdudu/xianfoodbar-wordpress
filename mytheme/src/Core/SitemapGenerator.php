<?php

namespace App\Core;

use DOMDocument;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Response;

class SitemapGenerator {

    /**
     * Undocumented variable
     *
     * @var SitemapGeneratorConfig[]
     */
    private $dataList = [];

    private function getList($className) {
        $ref = new ReflectionClass($className);
        $refObj = $className;
        foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isConstructor()) {
                continue;
            }
            $return = $method->invoke($refObj);
            if (is_array($return)) {
                $this->dataList = array_merge($this->dataList, $return);
            } else {
                $this->dataList[] = $return;
            }
        }
    }

    private function renderXML() {
        $xml = new DOMDocument('1.0', 'utf-8');
        $xmlRoot = $xml->createElement('urlset');
        $xmlRoot->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $xmlRoot->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
        $xmlRoot->setAttribute("xsi:schemaLocation", "http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd");
        $xmlRoot = $xml->appendChild($xmlRoot);

        foreach ($this->dataList as $data) {
            $urlElement = $xml->createElement('url');
            if ($data instanceof SitemapGeneratorConfig) {
                // 永久链接
                $loc = $xml->createElement('loc');
                $loc->textContent = $data->getLoc();
                $urlElement->appendChild($loc);
                // 最后更新
                $lastmod = $xml->createElement('lastmod');
                $lastmod->textContent = $data->getLastmod();
                $urlElement->appendChild($lastmod);
                // 更新频率
                $changefreq = $xml->createElement('changefreq');
                $changefreq->textContent = $data->getChangefreq();
                $urlElement->appendChild($changefreq);
                // 优先级
                $priority = $xml->createElement('priority');
                $priority->textContent = $data->getPriority();

                // 防止空URL标签
                $xmlRoot->appendChild($urlElement);
            }
        }

        return $xml->saveXML();
    }

    public function render($className) {
        $this->getList($className);
        $xmlContent = $this->renderXML();
        $response = new Response($xmlContent);
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }
}