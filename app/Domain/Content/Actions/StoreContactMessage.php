<?php

namespace App\Domain\Content\Actions;

use App\Domain\Content\Models\ContactMessage;

/**
 * Saves a message sent through the public contact form.
 */
class StoreContactMessage
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): ContactMessage
    {
        return ContactMessage::query()->create($data);
    }
}
