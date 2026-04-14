<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Domain\Model;

use TYPO3\CMS\Beuser\Domain\Model\BackendUser;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Document extends AbstractEntity implements \JsonSerializable
{
    public const string TYPE_NORMAL = 'normal';
    public const string TYPE_BOARD = 'board';

    public const string VISIBILITY_PUBLIC = 'public';
    public const string VISIBILITY_PRIVATE = 'private';

    protected string $headline = '';

    protected string $markup = '';

    protected string $type = self::TYPE_NORMAL;

    protected string $visibility = self::VISIBILITY_PUBLIC;

    protected ?Document $parent = null;

    protected ?Status $status = null;

    protected ?BackendUser $user = null;

    public function getHeadline(): string
    {
        return $this->headline;
    }

    public function setHeadline(string $headline): void
    {
        $this->headline = $headline;
    }

    public function getMarkup(): string
    {
        return $this->markup;
    }

    public function setMarkup(string $markup): void
    {
        $this->markup = $markup;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function isBoard(): bool
    {
        return $this->type === self::TYPE_BOARD;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): void
    {
        $this->visibility = $visibility;
    }

    public function getParent(): ?Document
    {
        return $this->parent;
    }

    public function setParent(?Document $parent): void
    {
        $this->parent = $parent;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): void
    {
        $this->status = $status;
    }

    public function getUser(): ?BackendUser
    {
        return $this->user;
    }

    public function setUser(BackendUser $user): void
    {
        $this->user = $user;
    }

    public function getBreadcrumbs(): array
    {
        // TODO add breadcrumbs builder
        return [];
    }

    public function jsonSerialize(): array
    {
        return [
            'uid' => $this->getUid(),
            'headline' => $this->getHeadline(),
            'markup' => $this->getMarkup(),
            'type' => $this->getType(),
            'visibility' => $this->getVisibility(),
            'parent' => $this->getParent()?->getUid(),
            'status' => $this->getStatus()?->getUid(),
            'user' => $this->getUser()?->getUid(),
        ];
    }
}
