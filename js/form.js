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

/* global SignaturePad, OC, OCA, t */

(function(OCA, OC, $) {
    'use strict';

    OCA.DienstzeitenApp = OCA.DienstzeitenApp || {};

    /**
     * @namespace OCA.DienstzeitenApp.Form
     */
    OCA.DienstzeitenApp.Form = {
        
        /** @type {SignaturePad} */
        signaturePad: null,
        
        /**
         * Initialize the form
         */
        init: function() {
            this.initSignaturePad();
            this.initFormEvents();
            this.initFormValidation();
            this.loadUserData();
        },
        
        /**
         * Initialize the signature pad
         */
        initSignaturePad: function() {
            var canvas = document.querySelector('.signature-pad canvas');
            
            if (!canvas) {
                return;
            }
            
            // Canvas-Größe an den Container anpassen
            var container = document.querySelector('.signature-pad');
            canvas.width = container.clientWidth;
            canvas.height = 200;
            
            this.signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'white',
                penColor: 'black'
            });
            
            // Event-Handler für die Clear-Schaltfläche
            document.querySelector('[data-action="clear"]').addEventListener('click', function() {
                OCA.DienstzeitenApp.Form.signaturePad.clear();
            });
            
            // Unterschrift beim Absenden des Formulars in das versteckte Feld übertragen
            document.getElementById('dienstzeit-form').addEventListener('submit', function() {
                if (!OCA.DienstzeitenApp.Form.signaturePad.isEmpty()) {
                    var signatureData = OCA.DienstzeitenApp.Form.signaturePad.toDataURL();
                    document.getElementById('signature').value = signatureData;
                }
            });
        },
        
        /**
         * Initialize form events
         */
        initFormEvents: function() {
            // Bedingtes Anzeigen des Textfelds für Sonstige Wache
            var stationSelect = document.getElementById('station');
            var otherDetailsGroup = document.getElementById('other_details_group');
            var otherDetailsInput = document.getElementById('other_details');
            
            if (stationSelect && otherDetailsGroup && otherDetailsInput) {
                stationSelect.addEventListener('change', function() {
                    if (this.value === 'Sonstiges') {
                        otherDetailsGroup.classList.remove('hidden');
                        otherDetailsInput.setAttribute('required', 'required');
                    } else {
                        otherDetailsGroup.classList.add('hidden');
                        otherDetailsInput.removeAttribute('required');
                        otherDetailsInput.value = '';
                    }
                });
            }
            
            // Bedingtes Anzeigen des Textfelds für Einsatznummer
            var overtimeCheckbox = document.getElementById('overtime_due_to_emergency');
            var emergencyNumberGroup = document.getElementById('emergency_number_group');
            var emergencyNumberInput = document.getElementById('emergency_number');
            
            if (overtimeCheckbox && emergencyNumberGroup && emergencyNumberInput) {
                overtimeCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        emergencyNumberGroup.classList.remove('hidden');
                        emergencyNumberInput.setAttribute('required', 'required');
                    } else {
                        emergencyNumberGroup.classList.add('hidden');
                        emergencyNumberInput.removeAttribute('required');
                        emergencyNumberInput.value = '';
                    }
                });
            }
            
            // Formularabsendung abfangen und AJAX-Request senden
            $('#dienstzeit-form').on('submit', function(e) {
                e.preventDefault();
                OCA.DienstzeitenApp.Form.submitForm();
            });
        },
        
        /**
         * Initialize form validation
         */
        initFormValidation: function() {
            $('#dienstzeit-form').on('submit', function(e) {
                var form = this;
                var isValid = true;
                
                // Überprüfen, ob alle Pflichtfelder ausgefüllt sind
                $(form).find('[required]').each(function() {
                    if (!this.value) {
                        isValid = false;
                        $(this).addClass('error');
                    } else {
                        $(this).removeClass('error');
                    }
                });
                
                // Überprüfen, ob eine Unterschrift vorhanden ist
                if (OCA.DienstzeitenApp.Form.signaturePad && OCA.DienstzeitenApp.Form.signaturePad.isEmpty()) {
                    isValid = false;
                    $('.signature-pad').addClass('error');
                    OC.Notification.showTemporary(t('dienstzeiten_app', 'Bitte unterschreiben Sie das Formular'));
                } else {
                    $('.signature-pad').removeClass('error');
                }
                
                if (!isValid) {
                    e.preventDefault();
                    OC.Notification.showTemporary(t('dienstzeiten_app', 'Bitte füllen Sie alle Pflichtfelder aus'));
                }
                
                return isValid;
            });
        },
        
        /**
         * Load user data from the API
         */
        loadUserData: function() {
            $.ajax({
                url: OC.generateUrl('/apps/dienstzeiten_app/api/user'),
                type: 'GET',
                contentType: 'application/json',
                success: function(response) {
                    if (response && response.first_name) {
                        $('#first_name').val(response.first_name);
                    }
                    if (response && response.last_name) {
                        $('#last_name').val(response.last_name);
                    }
                    if (response && response.email) {
                        $('#email').val(response.email);
                    }
                },
                error: function(xhr) {
                    console.error('Error loading user data:', xhr);
                }
            });
        },
        
        /**
         * Submit the form via AJAX
         */
        submitForm: function() {
            var formData = $('#dienstzeit-form').serializeArray();
            var data = {};
            
            // Serialize form data into a simple object
            for (var i = 0; i < formData.length; i++) {
                data[formData[i].name] = formData[i].value;
            }
            
            // Add checkbox value manually
            data.overtime_due_to_emergency = $('#overtime_due_to_emergency').is(':checked') ? 1 : 0;
            
            // Add signature if available
            if (!OCA.DienstzeitenApp.Form.signaturePad.isEmpty()) {
                data.signature = OCA.DienstzeitenApp.Form.signaturePad.toDataURL();
            }
            
            // Show loading indicator
            OC.Notification.show(t('dienstzeiten_app', 'Dienstzeit wird eingereicht...'), {type: 'loading'});
            
            $.ajax({
                url: OC.generateUrl('/apps/dienstzeiten_app/dienst'),
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(response) {
                    OC.Notification.hide();
                    
                    if (response.success) {
                        OC.Notification.showTemporary(response.message);
                        // Reset form after success
                        $('#dienstzeit-form')[0].reset();
                        // Clear signature pad
                        OCA.DienstzeitenApp.Form.signaturePad.clear();
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
        OCA.DienstzeitenApp.Form.init();
    });

})(OCA, OC, jQuery);
