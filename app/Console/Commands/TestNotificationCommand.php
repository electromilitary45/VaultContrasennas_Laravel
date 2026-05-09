<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

/**
 * Comando de prueba para crear notificaciones
 * 
 * Permite crear notificaciones de prueba para verificar el sistema.
 */
class TestNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test 
                            {--user= : Email o ID del usuario (opcional, usa el primero si no se especifica)}
                            {--type= : Tipo de notificación (opcional, muestra opciones si no se especifica)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear una notificación de prueba para verificar el sistema';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        // Obtener usuario
        $user = $this->getUser();
        if (!$user) {
            $this->error('No se encontró ningún usuario en el sistema.');
            return Command::FAILURE;
        }

        $this->info("Usuario seleccionado: {$user->name} ({$user->email})");

        // Obtener tipo de notificación
        $type = $this->getNotificationType();
        if (!$type) {
            return Command::FAILURE;
        }

        // Crear datos de prueba según el tipo
        $data = $this->getTestData($type);

        // Crear notificación
        try {
            $notification = $notificationService->create($data, $user);

            $this->info("✅ Notificación creada exitosamente!");
            $this->line("ID: {$notification->id}");
            $this->line("Tipo: {$notification->type}");
            $this->line("Título: {$notification->title}");
            $this->line("Mensaje: {$notification->message}");
            
            if ($notification->action_url) {
                $this->line("Acción: {$notification->action_label} → {$notification->action_url}");
            }

            $this->newLine();
            $this->info("💡 La notificación debería aparecer en tiempo real si Reverb está corriendo.");
            $this->info("   Verifica el dropdown de notificaciones en el navbar o visita: /notifications");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error al crear notificación: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Obtener usuario para la notificación.
     *
     * @return User|null
     */
    private function getUser(): ?User
    {
        $userInput = $this->option('user');

        if ($userInput) {
            // Buscar por email o ID
            $user = User::where('email', $userInput)
                ->orWhere('id', $userInput)
                ->first();

            if (!$user) {
                $this->error("Usuario no encontrado: {$userInput}");
                return null;
            }

            return $user;
        }

        // Usar el primer usuario disponible
        return User::first();
    }

    /**
     * Obtener tipo de notificación.
     *
     * @return string|null
     */
    private function getNotificationType(): ?string
    {
        $type = $this->option('type');

        if ($type) {
            $validTypes = [
                'group_invitation',
                'group_invitation_accepted',
                'group_invitation_rejected',
                'group_access_request',
                'group_access_request_accepted',
                'group_access_request_rejected',
                'item_shared_user',
                'item_shared_group',
                'item_created_in_group',
            ];

            if (!in_array($type, $validTypes)) {
                $this->error("Tipo inválido: {$type}");
                $this->line("Tipos válidos: " . implode(', ', $validTypes));
                return null;
            }

            return $type;
        }

        // Mostrar opciones interactivas
        $type = $this->choice(
            'Selecciona el tipo de notificación:',
            [
                'group_invitation' => 'Invitación a grupo',
                'group_invitation_accepted' => 'Invitación aceptada',
                'group_invitation_rejected' => 'Invitación rechazada',
                'group_access_request' => 'Solicitud de acceso a grupo',
                'group_access_request_accepted' => 'Solicitud aceptada',
                'group_access_request_rejected' => 'Solicitud rechazada',
                'item_shared_user' => 'Item compartido (usuario)',
                'item_shared_group' => 'Item compartido (grupo)',
                'item_created_in_group' => 'Item creado en grupo',
            ],
            'item_shared_user'
        );

        return $type;
    }

    /**
     * Obtener datos de prueba según el tipo.
     *
     * @param string $type
     * @return array<string, mixed>
     */
    private function getTestData(string $type): array
    {
        $baseData = [
            'type' => $type,
            'data' => [
                'test' => true,
                'created_at' => now()->toIso8601String(),
            ],
        ];

        return match ($type) {
            'group_invitation' => array_merge($baseData, [
                'title' => 'Invitación a grupo',
                'message' => 'Has sido invitado al grupo "Equipo de Desarrollo"',
                'action_url' => route('groups.index'),
                'action_label' => 'Ver grupos',
                'data' => array_merge($baseData['data'], [
                    'group_id' => 1,
                    'group_name' => 'Equipo de Desarrollo',
                    'inviter_id' => 1,
                    'inviter_name' => 'Admin',
                    'role' => 'member',
                ]),
            ]),
            'group_invitation_accepted' => array_merge($baseData, [
                'title' => 'Invitación aceptada',
                'message' => 'Juan Pérez ha aceptado tu invitación al grupo "Equipo de Desarrollo"',
                'action_url' => route('groups.index'),
                'action_label' => 'Ver grupos',
                'data' => array_merge($baseData['data'], [
                    'group_id' => 1,
                    'group_name' => 'Equipo de Desarrollo',
                    'accepted_user_id' => 2,
                    'accepted_user_name' => 'Juan Pérez',
                ]),
            ]),
            'group_invitation_rejected' => array_merge($baseData, [
                'title' => 'Invitación rechazada',
                'message' => 'Juan Pérez ha rechazado tu invitación al grupo "Equipo de Desarrollo"',
                'action_url' => route('groups.index'),
                'action_label' => 'Ver grupos',
                'data' => array_merge($baseData['data'], [
                    'group_id' => 1,
                    'group_name' => 'Equipo de Desarrollo',
                    'rejected_user_id' => 2,
                    'rejected_user_name' => 'Juan Pérez',
                ]),
            ]),
            'group_access_request' => array_merge($baseData, [
                'title' => 'Solicitud de acceso',
                'message' => 'Juan Pérez ha solicitado acceso al grupo "Equipo de Desarrollo"',
                'action_url' => route('groups.index'),
                'action_label' => 'Ver solicitudes',
                'data' => array_merge($baseData['data'], [
                    'group_id' => 1,
                    'group_name' => 'Equipo de Desarrollo',
                    'requested_user_id' => 2,
                    'requested_user_name' => 'Juan Pérez',
                ]),
            ]),
            'group_access_request_accepted' => array_merge($baseData, [
                'title' => 'Solicitud aceptada',
                'message' => 'Tu solicitud de acceso al grupo "Equipo de Desarrollo" ha sido aceptada',
                'action_url' => route('groups.index'),
                'action_label' => 'Ver grupos',
                'data' => array_merge($baseData['data'], [
                    'group_id' => 1,
                    'group_name' => 'Equipo de Desarrollo',
                    'responded_by_user_id' => 1,
                    'responded_by_user_name' => 'Admin',
                ]),
            ]),
            'group_access_request_rejected' => array_merge($baseData, [
                'title' => 'Solicitud rechazada',
                'message' => 'Tu solicitud de acceso al grupo "Equipo de Desarrollo" ha sido rechazada',
                'action_url' => route('groups.index'),
                'action_label' => 'Ver grupos',
                'data' => array_merge($baseData['data'], [
                    'group_id' => 1,
                    'group_name' => 'Equipo de Desarrollo',
                    'responded_by_user_id' => 1,
                    'responded_by_user_name' => 'Admin',
                ]),
            ]),
            'item_shared_user' => array_merge($baseData, [
                'title' => 'Item compartido',
                'message' => 'Admin te ha compartido el item "Contraseña de Gmail"',
                'action_url' => route('vault.index'),
                'action_label' => 'Ver vault',
                'data' => array_merge($baseData['data'], [
                    'vault_item_id' => 1,
                    'vault_item_title' => 'Contraseña de Gmail',
                    'vault_item_type' => 'auth',
                    'shared_by_user_id' => 1,
                    'shared_by_user_name' => 'Admin',
                    'permission' => 'edit',
                ]),
            ]),
            'item_shared_group' => array_merge($baseData, [
                'title' => 'Item compartido con grupo',
                'message' => 'Admin ha compartido el item "API Key de Producción" con el grupo "Equipo de Desarrollo"',
                'action_url' => route('vault.index'),
                'action_label' => 'Ver vault',
                'data' => array_merge($baseData['data'], [
                    'vault_item_id' => 2,
                    'vault_item_title' => 'API Key de Producción',
                    'vault_item_type' => 'api_key',
                    'group_id' => 1,
                    'group_name' => 'Equipo de Desarrollo',
                    'shared_by_user_id' => 1,
                    'shared_by_user_name' => 'Admin',
                    'permission' => 'view',
                ]),
            ]),
            'item_created_in_group' => array_merge($baseData, [
                'title' => 'Nuevo item en grupo',
                'message' => 'Admin ha creado el item "Credenciales de Base de Datos" en el grupo "Equipo de Desarrollo"',
                'action_url' => route('groups.index'),
                'action_label' => 'Ver grupo',
                'data' => array_merge($baseData['data'], [
                    'vault_item_id' => 3,
                    'vault_item_title' => 'Credenciales de Base de Datos',
                    'vault_item_type' => 'auth',
                    'group_id' => 1,
                    'group_name' => 'Equipo de Desarrollo',
                    'created_by_user_id' => 1,
                    'created_by_user_name' => 'Admin',
                ]),
            ]),
            default => array_merge($baseData, [
                'title' => 'Notificación de prueba',
                'message' => 'Esta es una notificación de prueba del sistema',
                'action_url' => route('dashboard'),
                'action_label' => 'Ir al dashboard',
            ]),
        };
    }
}
