<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento NotificationCreated
 * 
 * Se emite cuando se crea una nueva notificación.
 * Implementa ShouldBroadcast para enviar la notificación vía WebSocket.
 */
class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * La notificación creada.
     *
     * @var Notification
     */
    public Notification $notification;

    /**
     * Create a new event instance.
     *
     * @param Notification $notification
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->notification->user_id),
        ];
    }

    /**
     * El nombre del evento que se emitirá.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    /**
     * Los datos que se enviarán con el evento.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'type' => $this->notification->type,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'data' => $this->notification->data,
            'read' => $this->notification->read,
            'action_url' => $this->notification->action_url,
            'action_label' => $this->notification->action_label,
            'created_at' => $this->notification->created_at->toIso8601String(),
        ];
    }
}
