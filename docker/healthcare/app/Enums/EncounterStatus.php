<?php

namespace App\Enums;

enum EncounterStatus: string
{
    case Planned = 'planned';

    case InProgress = 'in-progress';

    case Finished = 'finished';

    case Cancelled = 'cancelled';
}