<?php
/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\DienstzeitenApp\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
    'routes' => [
        // Hauptroute für die Anzeige des Dienstzeit-Formulars
        ['name' => 'dienstzeiten#index', 'url' => '/', 'verb' => 'GET'],
        
        // Route zum Speichern eines neuen Dienstzeit-Eintrags
        ['name' => 'dienstzeiten#store', 'url' => '/dienst', 'verb' => 'POST'],
        
        // Route für die Übersicht aller eigenen Dienstzeit-Einträge
        ['name' => 'dienstzeiten#list', 'url' => '/list', 'verb' => 'GET'],
        
        // Route zum Anzeigen eines einzelnen Dienstzeit-Eintrags
        ['name' => 'dienstzeiten#show', 'url' => '/dienst/{id}', 'verb' => 'GET'],
        
        // Route für die Approve-Seite
        ['name' => 'dienstzeiten#approval', 'url' => '/approval/{id}/{token}', 'verb' => 'GET'],
        
        // Route zum Verarbeiten der Genehmigung/Ablehnung
        ['name' => 'dienstzeiten#processApproval', 'url' => '/approval/{id}/{token}', 'verb' => 'POST'],
        
        // Route zum Herunterladen eines PDF-Dokuments
        ['name' => 'dienstzeiten#downloadPdf', 'url' => '/dienst/{id}/pdf', 'verb' => 'GET'],
        
        // Einstellungsrouten
        ['name' => 'settings#index', 'url' => '/settings', 'verb' => 'GET'],
        ['name' => 'settings#save', 'url' => '/settings', 'verb' => 'POST'],
        
        // API-Routen
        ['name' => 'api#getUserData', 'url' => '/api/user', 'verb' => 'GET']
    ]
];
