<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Home;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @DI\Service("claroline.common.graph_service")
 */
class GraphService
{
    private $graph;
    private $crawler;

    public function get($url)
    {
        $this->graph['url'] = $url;
        $headers = get_headers($url, 1);

        if ($headers && is_string($type = $headers['Content-Type']) && strpos($type, 'image/') === 0) {
            $this->graph['type'] = 'raw';
            $this->graph['images'][] = $url;
        } elseif (false !== ($content = @file_get_contents($url))) {
            $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');

            $this->crawler = new Crawler();
            $this->crawler->addHtmlContent($content);
            $this->openGraph();
            $this->twitter();

            if (!isset($this->graph['title']) &&
                !isset($this->graph['type']) &&
                !isset($this->graph['description'])) {
                $this->html();
            }

            if (isset($this->graph['type'])) {
                //for example in case of slideshare:presentation
                $this->graph['type'] = str_replace(':', '-', $this->graph['type']);
            } else {
                $this->graph['type'] = 'default';
            }
        }

        return $this->graph;
    }

    public function twitter()
    {
        $this->find(
            ['title', 'description', 'type', 'video', 'site_name', 'url', 'image'],
            'og',
            'property'
        );
    }

    public function openGraph()
    {
        $this->find(
            ['card', 'site', 'player', 'player:width', 'player:height', 'image'],
            'twitter',
            'name'
        );
    }

    public function find($values, $name, $attribute)
    {
        foreach ($values as $value) {
            try {
                $tmp = $this->crawler->filter("meta[$attribute='$name:$value']")->attr('content');

                if (!$tmp) {
                    $tmp = $this->crawler->filter("meta[$attribute='$name:$value']")->attr('value');
                }

                $this->graph[$value] = $tmp;
            } catch (\Exception $e) {
                $this->graph['error'] = 1;
            }
        }
    }

    public function html()
    {
        $this->graph['title'] = $this->crawler->filter('title')->text();
        $this->graph['type'] = 'raw';

        $this->graph['description'] = '';

        $this->crawler->filter('body p')->each(
            function ($node, $i) {
                if (strlen($this->graph['description']) < 100) {
                    $this->graph['description'] .= trim($node->text()).' ';
                }
            }
        );

        $this->crawler->filter('img')->each(
            function ($node, $i) {
                $this->graph['images'][$i] = $node->attr('src');
            }
        );
    }
}
