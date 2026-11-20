<?php

namespace App\Notifications;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public int $orderId;

    public OrderStatus $status;

    public function __construct(Order $order, public OrderStatus $previousStatus)
    {
        $this->orderId = $order->id;
        $this->status = $order->status;
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
            'title' => 'Encomenda '.$this->status->label(),
            'body' => 'A tua encomenda #'.$this->orderId.' passou de '.$this->previousStatus->label().' para '.$this->status->label().'.',
            'url' => route('account.orders.show', $this->orderId, absolute: false),
            'kind' => 'order',
            'order_id' => $this->orderId,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $description = match ($this->status) {
            OrderStatus::Pending => 'A tua encomenda está pendente de confirmação de pagamento.',
            OrderStatus::Paid => 'A loja confirmou o pagamento da tua encomenda.',
            OrderStatus::Shipped => 'A tua encomenda foi marcada como enviada.',
            OrderStatus::Cancelled => 'A tua encomenda foi cancelada.',
            OrderStatus::Refunded => 'A loja marcou a tua encomenda como reembolsada. Se precisares de esclarecimentos, contacta a loja.',
        };

        return (new MailMessage)
            ->subject('Encomenda #'.$this->orderId.' — '.$this->status->label())
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('O estado da tua encomenda #'.$this->orderId.' foi atualizado.')
            ->line('Estado anterior: '.$this->previousStatus->label())
            ->line('Novo estado: '.$this->status->label())
            ->line($description)
            ->action('Ver encomenda', route('account.orders.show', $this->orderId))
            ->salutation('Equipa '.config('app.name'));
    }
}
