<?php

namespace App\Notifications;

use App\Models\Category;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCollection extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public int $categoryId;

    public string $name;

    public string $slug;

    public function __construct(Category $category)
    {
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->afterCommit();
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /** @return array<string, string> */
    public function viaConnections(): array
    {
        return ['database' => 'sync'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nova coleção: '.$this->name.' — '.config('app.name'))
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('A coleção '.$this->name.' já chegou à '.config('app.name').'.')
            ->line('Descobre as novas peças e encontra os teus próximos favoritos.')
            ->action('Descobrir coleção', route('shop.category', $this->slug))
            ->salutation('Até breve, equipa '.config('app.name'));
    }

    /** @return array<string, string|int> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Nova coleção: '.$this->name,
            'body' => 'Há novidades na B-Su! Descobre as peças da coleção '.$this->name.'.',
            'url' => route('shop.category', $this->slug, absolute: false),
            'kind' => 'collection',
            'category_id' => $this->categoryId,
        ];
    }
}
