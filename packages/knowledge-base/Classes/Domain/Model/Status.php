<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Status extends AbstractEntity
{
    protected string $title = '';

    protected Document $document;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDocument(): Document
    {
        return $this->document;
    }

    public function setDocument(Document $document): void
    {
        $this->document = $document;
    }
}
