<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlaced extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public int $orderId;

    public string $total;

    /** @var array<int, string> */
    public array $items;

    public function __construct(Order $order)
    {
        $this->orderId = $order->id;
        $this->total = $order->formattedTotal();
        $this->items = $order->items->map(fn ($item) => $item->quantity.' × '.$item->product_name.' — '.$item->formattedSubtotal())->all();
        $this->afterCommit();
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /** @return array<string, string> */
    public function viaConnections(): array
    {
        return ['database' => 'sync'];
    }

    /** @return array<string, string|int> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Encomenda recebida',
            'body' => 'Recebemos a tua encomenda #'.$this->orderId.'. Total: '.$this->total.'.',
            'url' => route('account.orders.show', $this->orderId, absolute: false),
            'kind' => 'order',
            'order_id' => $this->orderId,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Recebemos a tua encomenda #'.$this->orderId.' — '.config('app.name'))
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('Recebemos a tua encomenda #'.$this->orderId.'. Obrigado pela tua compra!')
            ->line('Estado: Pendente. O pagamento será confirmado pela loja.');

        foreach ($this->items as $item) {
            $message->line($item);
        }

        return $message->line('Total: '.$this->total)
            ->action('Ver encomenda', route('account.orders.show', $this->orderId))
            ->salutation('Até breve, equipa '.config('app.name'));
    }
}
