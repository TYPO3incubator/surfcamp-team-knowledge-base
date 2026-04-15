<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Domain\Model;

use TYPO3\CMS\Beuser\Domain\Model\BackendUser;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Comment extends AbstractEntity
{
    protected string $comment = '';

    protected ?Document $document = null;

    protected ?BackendUser $user = null;

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
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
