<?php

declare(strict_types=1);

namespace JayI\Foundation\Audit\Data;

use Illuminate\Database\Eloquent\Model;

/**
 * Which audit entries to read, and how many.
 *
 *     AuditFilter::make()->source('keystone')->subject($product)->limit(20);
 */
final class AuditFilter
{
    public private(set) ?string $source = null;

    public private(set) ?string $subjectType = null;

    public private(set) ?string $subjectId = null;

    public private(set) ?string $action = null;

    public private(set) ?string $actorId = null;

    public private(set) int $limit = 25;

    public private(set) ?string $cursor = null;

    public static function make(): self
    {
        return new self;
    }

    /**
     * Only entries recorded from this package, by key.
     */
    public function source(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Only entries about this model.
     */
    public function subject(?Model $subject): self
    {
        return $this->subjectKey($subject?->getMorphClass(), $subject === null ? null : (string) $subject->getKey());
    }

    /**
     * Only entries about the model with this morph type and key.
     */
    public function subjectKey(?string $type, ?string $id): self
    {
        $this->subjectType = $type;
        $this->subjectId = $id;

        return $this;
    }

    /**
     * Only entries of this action: `product.updated`.
     */
    public function action(?string $action): self
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Only entries made by the user with this key.
     */
    public function actorId(?string $actorId): self
    {
        $this->actorId = $actorId;

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = max(1, $limit);

        return $this;
    }

    /**
     * The `nextCursor` of a previous page.
     */
    public function cursor(?string $cursor): self
    {
        $this->cursor = $cursor;

        return $this;
    }
}
