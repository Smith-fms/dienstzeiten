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

/* global OC, OCA, t */

(function(OCA, OC, $) {
    'use strict';

    OCA.DienstzeitenApp = OCA.DienstzeitenApp || {};

    /**
     * @namespace OCA.DienstzeitenApp.Settings
     */
    OCA.DienstzeitenApp.Settings = {
        
        /**
         * Initialize the settings
         */
        init: function() {
            this.initFormEvents();
        },
        
        /**
         * Initialize form events
         */
        initFormEvents: function() {
            // Formularabsendung abfangen und AJAX-Request senden
            $('#dienstzeiten-settings-form').on('submit', function(e) {
                e.preventDefault();
                OCA.DienstzeitenApp.Settings.submitForm();
            });
        },
        
        /**
         * Submit the form via AJAX
         */
        submitForm: function() {
            var formData = $('#dienstzeiten-settings-form').serializeArray();
            var data = {};
            
            // Serialize form data into a simple object
            for (var i = 0; i < formData.length; i++) {
                data[formData[i].name] = formData[i].value;
            }
            
            // Show loading indicator
            OC.Notification.show(t('dienstzeiten_app', 'Einstellungen werden gespeichert...'), {type: 'loading'});
            
            $.ajax({
                url: OC.generateUrl('/apps/dienstzeiten_app/settings'),
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(response) {
                    OC.Notification.hide();
                    
                    if (response.success) {
                        OC.Notification.showTemporary(response.message);
                    } else {
                        OC.Notification.showTemporary(response.error || t('dienstzeiten_app', 'Ein Fehler ist aufgetreten'));
                    }
                },
                error: function(xhr) {
                    OC.Notification.hide();
                    
                    var errorMessage = t('dienstzeiten_app', 'Bei der Verarbeitung ist ein Fehler aufgetreten');
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    
                    OC.Notification.showTemporary(errorMessage);
                }
            });
        }
    };
    
    $(document).ready(function() {
        OCA.DienstzeitenApp.Settings.init();
    });

})(OCA, OC, jQuery);
