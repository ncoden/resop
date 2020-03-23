<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AvailabilityInterface;
use DateTimeInterface;

interface AvailabilityRepositoryInterface
{
    public function findOneByInterval(DateTimeInterface $from, DateTimeInterface $to): ?AvailabilityInterface;

    public function findByOwnerAndDates(array $owners, \DateTimeInterface $start, \DateTimeInterface $end): array;
}
