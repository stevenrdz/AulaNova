<?php

namespace App\Message;

class ImportUsersMessage
{
    public function __construct(public readonly int $batchId)
    {
    }
}
