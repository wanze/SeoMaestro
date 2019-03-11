<?php

namespace SeoMaestro;

use ProcessWire\WireData;

class SitemapItem extends WireData
{
    public function __construct()
    {
        parent::__construct();

        $this->set('loc', '');
        $this->set('lastmod', '');
        $this->set('priority', 0.5);
        $this->set('changefreq', 'monthly');
        $this->set('alternates', []);
    }

    /**
     * Add an alternate URL for another language.
     *
     * @param string $languageCode
     * @param string $url
     *
     * @return \SeoMaestro\SitemapItem
     */
    public function addAlternate($languageCode, $url)
    {
        $alternates = $this->get('alternates');
        $alternates[$languageCode] = $url;
        $this->set('alternates', $alternates);

        return $this;
    }
}
