<?php

return [
    'auth' => [
        'invalid_credentials' => 'Las credenciales proporcionadas son incorrectas.',
        'account_inactive' => 'Esta cuenta se encuentra inactiva o suspendida.',
        'company_registered' => 'Empresa registrada. Pendiente de aprobación.',
        'consumer_registered' => 'Cuenta creada correctamente.',
        'logged_out' => 'Sesión cerrada correctamente.',
        'reset_link_sent' => 'Enlace de recuperación enviado.',
        'reset_link_failed' => 'No pudimos enviar el enlace.',
        'password_updated' => 'Contraseña actualizada correctamente.',
        'invalid_reset_token' => 'El token no es válido o expiró.',
    ],

    'company' => [
        'created' => 'Empresa creada correctamente.',
        'updated' => 'Empresa actualizada correctamente.',
        'deleted' => 'Empresa eliminada correctamente.',
        'approved' => 'Empresa aprobada correctamente.',
        'suspended' => 'Empresa suspendida correctamente.',
        'reactivated' => 'Empresa reactivada correctamente.',
        'no_company_assigned' => 'No tienes una empresa asociada.',
    ],

    'food_truck' => [
        'created' => 'Food truck creado correctamente.',
        'updated' => 'Food truck actualizado correctamente.',
        'deleted' => 'Food truck eliminado correctamente.',
    ],

    'location' => [
        'created' => 'Ubicación creada correctamente.',
        'updated' => 'Ubicación actualizada correctamente.',
        'deleted' => 'Ubicación eliminada correctamente.',
        'not_found' => 'Ubicación no encontrada para este food truck.',
    ],

    'menu' => [
        'created' => 'Menú creado correctamente.',
        'updated' => 'Menú actualizado correctamente.',
        'deleted' => 'Menú eliminado correctamente.',
        'not_found' => 'Menú no encontrado para este food truck.',
    ],

    'menu_item' => [
        'created' => 'Platillo agregado correctamente.',
        'updated' => 'Platillo actualizado correctamente.',
        'deleted' => 'Platillo eliminado correctamente.',
        'not_found' => 'Platillo no encontrado para este menú.',
    ],

    'promotion' => [
        'created' => 'Promoción creada correctamente.',
        'updated' => 'Promoción actualizada correctamente.',
        'deleted' => 'Promoción eliminada correctamente.',
        'food_truck_mismatch' => 'El food truck indicado no pertenece a tu empresa.',
    ],

    'plan' => [
        'created' => 'Plan creado correctamente.',
        'updated' => 'Plan actualizado correctamente.',
        'deleted' => 'Plan eliminado correctamente.',
        'has_subscriptions' => 'No se puede eliminar un plan con suscripciones asociadas. Desactívalo en su lugar.',
        'not_available' => 'Este plan no está disponible actualmente.',
    ],

    'subscription' => [
        'activated' => 'Suscripción al plan ":plan" activada correctamente.',
        'cancelled' => 'Suscripción cancelada correctamente.',
        'no_active_subscription' => 'No tienes una suscripción activa para cancelar.',
        'no_subscription' => 'Esta empresa no tiene una suscripción activa.',
    ],

    'user' => [
        'created' => 'Usuario creado correctamente.',
        'updated' => 'Usuario actualizado correctamente.',
        'deleted' => 'Usuario eliminado correctamente.',
        'cannot_remove_own_role' => 'No puedes quitarte tu propio rol de Platform Owner.',
        'cannot_delete_self' => 'No puedes eliminar tu propia cuenta.',
    ],

    'operator' => [
        'created' => 'Operador creado correctamente.',
        'updated' => 'Operador actualizado correctamente.',
        'deleted' => 'Operador eliminado correctamente.',
        'not_found' => 'Operador no encontrado.',
    ],

    'favorite' => [
        'added' => 'Agregado a favoritos.',
        'removed' => 'Eliminado de favoritos.',
    ],

    'upload' => [
        'uploaded' => 'Imagen subida correctamente.',
    ],
];
