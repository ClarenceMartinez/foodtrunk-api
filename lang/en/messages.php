<?php

return [
    'auth' => [
        'invalid_credentials' => 'The provided credentials are incorrect.',
        'account_inactive' => 'This account is inactive or suspended.',
        'company_registered' => 'Company registered. Pending approval.',
        'consumer_registered' => 'Account created successfully.',
        'logged_out' => 'Signed out successfully.',
        'reset_link_sent' => 'Recovery link sent.',
        'reset_link_failed' => "We couldn't send the link.",
        'password_updated' => 'Password updated successfully.',
        'invalid_reset_token' => 'The token is invalid or has expired.',
    ],

    'company' => [
        'created' => 'Company created successfully.',
        'updated' => 'Company updated successfully.',
        'deleted' => 'Company deleted successfully.',
        'approved' => 'Company approved successfully.',
        'suspended' => 'Company suspended successfully.',
        'reactivated' => 'Company reactivated successfully.',
        'no_company_assigned' => "You don't have a company assigned.",
    ],

    'food_truck' => [
        'created' => 'Food truck created successfully.',
        'updated' => 'Food truck updated successfully.',
        'deleted' => 'Food truck deleted successfully.',
    ],

    'location' => [
        'created' => 'Location created successfully.',
        'updated' => 'Location updated successfully.',
        'deleted' => 'Location deleted successfully.',
        'not_found' => 'Location not found for this food truck.',
    ],

    'menu' => [
        'created' => 'Menu created successfully.',
        'updated' => 'Menu updated successfully.',
        'deleted' => 'Menu deleted successfully.',
        'not_found' => 'Menu not found for this food truck.',
    ],

    'menu_item' => [
        'created' => 'Item added successfully.',
        'updated' => 'Item updated successfully.',
        'deleted' => 'Item deleted successfully.',
        'not_found' => 'Item not found for this menu.',
    ],

    'promotion' => [
        'created' => 'Promotion created successfully.',
        'updated' => 'Promotion updated successfully.',
        'deleted' => 'Promotion deleted successfully.',
        'food_truck_mismatch' => "The specified food truck doesn't belong to your company.",
    ],

    'plan' => [
        'created' => 'Plan created successfully.',
        'updated' => 'Plan updated successfully.',
        'deleted' => 'Plan deleted successfully.',
        'has_subscriptions' => 'A plan with active subscriptions cannot be deleted. Deactivate it instead.',
        'not_available' => 'This plan is not currently available.',
    ],

    'subscription' => [
        'activated' => 'Subscription to the ":plan" plan activated successfully.',
        'cancelled' => 'Subscription cancelled successfully.',
        'no_active_subscription' => "You don't have an active subscription to cancel.",
        'no_subscription' => "This company doesn't have an active subscription.",
    ],

    'user' => [
        'created' => 'User created successfully.',
        'updated' => 'User updated successfully.',
        'deleted' => 'User deleted successfully.',
        'cannot_remove_own_role' => 'You cannot remove your own Platform Owner role.',
        'cannot_delete_self' => 'You cannot delete your own account.',
    ],

    'operator' => [
        'created' => 'Operator created successfully.',
        'updated' => 'Operator updated successfully.',
        'deleted' => 'Operator deleted successfully.',
        'not_found' => 'Operator not found.',
    ],

    'favorite' => [
        'added' => 'Added to favorites.',
        'removed' => 'Removed from favorites.',
    ],

    'upload' => [
        'uploaded' => 'Image uploaded successfully.',
    ],
];
