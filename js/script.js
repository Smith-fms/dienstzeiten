/**
 * @copyright Copyright (c) 2025
 *
 * @author Nextcloud App Developer
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

/* global OC, OCA */

(function(OCA, OC, $) {
    'use strict';

    OCA.DienstzeitenApp = OCA.DienstzeitenApp || {};

    /**
     * Hauptinitialisierungsfunktion für die App
     */
    OCA.DienstzeitenApp.init = function() {
        // Prüfen, welche Seite aktuell angezeigt wird und entsprechende Module initialisieren
        var appContent = document.getElementById('app-content');
        
        if (!appContent) {
            return;
        }
        
        // Wenn das Formular vorhanden ist
        if (document.getElementById('dienstzeit-form')) {
            // Form.js wird separat geladen
            console.info('Dienstzeit-Formular gefunden');
        }
        
        // Wenn das Einstellungsformular vorhanden ist
        if (document.getElementById('dienstzeiten-settings-form')) {
            // Settings.js wird separat geladen
            console.info('Einstellungs-Formular gefunden');
        }
    };

    $(document).ready(function() {
        OCA.DienstzeitenApp.init();
    });

})(OCA, OC, jQuery);
