<?php
/** @var array $_ */
/** @var \OCP\IL10N $l */
/** @var \OCP\Defaults $theme */
$userData = $_['userData'];
?>

<div id="app-content">
    <div id="app-content-wrapper">
        <div class="section">
            <h2><?php p($l->t('Dienstzeit erfassen')); ?></h2>
            
            <form id="dienstzeit-form" class="dienstzeit-form">
                <!-- Persönliche Daten -->
                <fieldset>
                    <legend><?php p($l->t('Persönliche Daten')); ?></legend>
                    
                    <div class="form-group">
                        <label for="first_name"><?php p($l->t('Vorname')); ?> *</label>
                        <input type="text" id="first_name" name="first_name" value="<?php p($userData['firstName']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name"><?php p($l->t('Nachname')); ?> *</label>
                        <input type="text" id="last_name" name="last_name" value="<?php p($userData['lastName']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email"><?php p($l->t('E-Mail')); ?> *</label>
                        <input type="email" id="email" name="email" value="<?php p($userData['email']); ?>" required>
                    </div>
                </fieldset>
                
                <!-- Dienstzeit-Daten -->
                <fieldset>
                    <legend><?php p($l->t('Dienstzeit-Daten')); ?></legend>
                    
                    <div class="form-group">
                        <label for="service_date"><?php p($l->t('Datum des Dienstes')); ?> *</label>
                        <input type="date" id="service_date" name="service_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="start_time"><?php p($l->t('Beginn')); ?> *</label>
                        <input type="time" id="start_time" name="start_time" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_time"><?php p($l->t('Ende')); ?> *</label>
                        <input type="time" id="end_time" name="end_time" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="station"><?php p($l->t('Wache')); ?> *</label>
                        <select id="station" name="station" required>
                            <option value=""><?php p($l->t('-- Bitte auswählen --')); ?></option>
                            <option value="Wache 1"><?php p($l->t('Wache 1')); ?></option>
                            <option value="Wache 4"><?php p($l->t('Wache 4')); ?></option>
                            <option value="Sonderbedarf"><?php p($l->t('Sonderbedarf')); ?></option>
                            <option value="Sonstiges"><?php p($l->t('Sonstiges')); ?></option>
                        </select>
                    </div>
                    
                    <div class="form-group hidden" id="other_details_group">
                        <label for="other_details"><?php p($l->t('Sonstige Wache (bitte angeben)')); ?> *</label>
                        <input type="text" id="other_details" name="other_details">
                    </div>
                    
                    <div class="form-group">
                        <label for="overtime_due_to_emergency"><?php p($l->t('Mehrarbeit durch Einsatz?')); ?></label>
                        <div class="checkbox-container">
                            <input type="checkbox" id="overtime_due_to_emergency" name="overtime_due_to_emergency" value="1">
                            <label for="overtime_due_to_emergency"><?php p($l->t('Ja')); ?></label>
                        </div>
                    </div>
                    
                    <div class="form-group hidden" id="emergency_number_group">
                        <label for="emergency_number"><?php p($l->t('Einsatznummer')); ?> *</label>
                        <input type="text" id="emergency_number" name="emergency_number">
                    </div>
                </fieldset>
                
                <!-- Unterschrift -->
                <fieldset>
                    <legend><?php p($l->t('Unterschrift')); ?></legend>
                    
                    <div class="form-group">
                        <label for="signature"><?php p($l->t('Unterschrift')); ?> *</label>
                        <div id="signature-pad" class="signature-pad">
                            <div class="signature-pad--body">
                                <canvas></canvas>
                            </div>
                            <div class="signature-pad--footer">
                                <div class="description"><?php p($l->t('Unterschreiben Sie mit der Maus oder Ihrem Finger')); ?></div>
                                <div class="signature-pad--actions">
                                    <div>
                                        <button type="button" class="clear" data-action="clear"><?php p($l->t('Löschen')); ?></button>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="signature" name="signature" required>
                        </div>
                    </div>
                </fieldset>
                
                <div class="form-actions">
                    <button type="submit" class="primary"><?php p($l->t('Absenden')); ?></button>
                    <button type="reset"><?php p($l->t('Zurücksetzen')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
