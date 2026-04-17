<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Status extends AbstractEntity
{
    protected string $title = '';

    protected ?Document $document = null;
    protected int $ordering = 0;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(Document $document): void
    {
        $this->document = $document;
    }

    public function getOrdering(): int
    {
        return $this->ordering;
    }

    public function setOrdering(int $ordering): void
    {
        $this->ordering = $ordering;
    }
}
