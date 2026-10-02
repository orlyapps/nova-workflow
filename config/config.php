<?php
return [
    'marking_store_field' => 'status',
    'activity_user_resource' => 'users',
    'log_model' => \Orlyapps\NovaWorkflow\Models\Log::class,

    /*
     * Exceptions, deren Meldung eine WorkflowAction direkt anzeigt (z. B. fachliche Fehler
     * aus der App). Alle anderen Fehler werden gemeldet und neutral angezeigt.
     */
    'user_exceptions' => [],
];
