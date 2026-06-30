<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppInventar\Support;

use App\Models\User;
use Illuminate\Support\Collection;

class VorgesetztenResolver
{
    /**
     * @return Collection<int, User>
     */
    public function vorgesetzteOhneHgf(User $user): Collection
    {
        return $user->getVorgesetzte()->filter(fn (User $v): bool => ! $v->ist_hgf)->values();
    }
}
