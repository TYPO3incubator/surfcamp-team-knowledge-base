<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Dto;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3Incubator\KnowledgeBase\Domain\Model\Document;

final class SlimDocumentDto extends AbstractEntity
{
    protected ?int $uid = 0;
    protected int $parent = 0;
    protected string $visibility = Document::VISIBILITY_PUBLIC;
    protected string $type = Document::TYPE_NORMAL;
    protected string $headline = '';

    public function getUid(): int
    {
        return $this->uid;
    }

    public function setUid(int $uid): void
    {
        $this->uid = $uid;
    }

    public function getParent(): int
    {
        return $this->parent;
    }

    public function setParent(int $parent): void
    {
        $this->parent = $parent;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): void
    {
        $this->visibility = $visibility;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getHeadline(): string
    {
        return $this->headline;
    }

    public function setHeadline(string $headline): void
    {
        $this->headline = $headline;
    }
}
