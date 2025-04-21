<?php

// src/Service/BorneContext.php
namespace App\Service;

use App\Entity\Espace;
use App\Repository\EspaceRepository;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class BorneContext
{
    private ?Espace $espace = null;

    public function __construct(
        private EspaceRepository $espaceRepository,
        private ParameterBagInterface $params,
        private ?CacheItemPoolInterface $cache = null
    ) {}

    public function getEspace(): ?Espace
    {
        if ($this->espace !== null) {
            return $this->espace;
        }

        $cacheKey = 'espace_'.$this->params->get('app.espace.default');
        
        // Tentative de récupération depuis le cache
        if ($this->cache) {
            $cachedItem = $this->cache->getItem($cacheKey);
            if ($cachedItem->isHit()) {
                return $cachedItem->get();
            }
        }

        // Récupération depuis la base
        $this->espace = $this->espaceRepository->findOneBy([
            'identifiant' => $this->params->get('app.espace.default')
        ]);

        // Mise en cache
        if ($this->cache && $this->espace) {
            $cachedItem->set($this->espace);
            $cachedItem->expiresAfter(3600); // 1h
            $this->cache->save($cachedItem);
        }

        return $this->espace;
    }

    public function setEspaceActuel(string $identifiant): void
    {
        $this->espace = null;
        $this->params->set('app.espace.default', $identifiant);
        
        if ($this->cache) {
            $this->cache->deleteItem('espace_'.$identifiant);
        }
    }
}