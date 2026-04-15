<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Domain\Model;

use TYPO3\CMS\Beuser\Domain\Model\BackendUser;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Reaction extends AbstractEntity
{
    public const string REACTION_LIKE = 'like';
    public const string REACTION_HEART = 'heart';
    public const string REACTION_THUMBS_DOWN = 'thumbs_down';
    public const string REACTION_CELEBRATE = 'celebrate';

    protected string $reaction = self::REACTION_LIKE;

    protected ?Document $document = null;

    protected ?BackendUser $user = null;

    public function getReaction(): string
    {
        return $this->reaction;
    }

    public function setReaction(string $reaction): void
    {
        $this->reaction = $reaction;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(Document $document): void
    {
        $this->document = $document;
    }

    public function getUser(): ?BackendUser
    {
        return $this->user;
    }

    public function setUser(BackendUser $user): void
    {
        $this->user = $user;
    }
}
