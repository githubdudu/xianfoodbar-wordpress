<?php

namespace App\Core;


class SitemapGeneratorConfig {

    private $lastmod = '';
    private $loc = '';
    private $changefreq = '';
    private $priority = '';

    public function setLoc(string $loc) {
        $this->loc = $loc;
        return $this;
    }

    public function setLastmod(string $lastmod) {
        $this->lastmod = $lastmod;
        return $this;
    }

    public function setChangefreq(string $changefreq) {
        $this->changefreq = $changefreq;
        return $this;
    }

    public function setPriority(string $priority) {
        $this->priority = $priority;
        return $this;
    }

    public function getLoc() {
        return $this->loc;
    }

    public function getLastmod() {
        return $this->lastmod;
    }

    public function getChangefreq() {
        return $this->changefreq;
    }

    public function getPriority() {
        return $this->priority;
    }
}