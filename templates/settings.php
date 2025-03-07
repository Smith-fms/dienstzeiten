<?php
/** @var array $_ */
/** @var \OCP\IL10N $l */
/** @var \OCP\Defaults $theme */
$teamleadGroup = $_['teamlead_group'];
$hrEmail = $_['hr_email'];
$groups = $_['groups'];
?>

<div id="app-content">
    <div id="app-content-wrapper">
        <div class="section">
            <h2><?php p($l->t('Dienstzeiten-App Einstellungen')); ?></h2>
            
            <p class="settings-hint">
                <?php p($l->t('Hier können Sie die erforderlichen Einstellungen für die Dienstzeiten-App vornehmen.')); ?>
            </p>
            
            <form id="dienstzeiten-settings-form" class="settings-form">
                <div class="form-group">
                    <label for="teamlead_group"><?php p($l->t('Teamleitung-Gruppe')); ?> *</label>
                    <select id="teamlead_group" name="teamlead_group" required>
                        <option value=""><?php p($l->t('-- Bitte auswählen --')); ?></option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?php p($group['id']); ?>" <?php p($teamleadGroup === $group['id'] ? 'selected' : ''); ?>>
                                <?php p($group['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="form-hint">
                        <?php p($l->t('Die Mitglieder dieser Gruppe erhalten E-Mails zur Genehmigung von Dienstzeiten.')); ?>
                    </p>
                </div>
                
                <div class="form-group">
                    <label for="hr_email"><?php p($l->t('E-Mail-Adresse der Personalabteilung')); ?> *</label>
                    <input type="email" id="hr_email" name="hr_email" value="<?php p($hrEmail); ?>" required>
                    <p class="form-hint">
                        <?php p($l->t('An diese E-Mail-Adresse werden genehmigte Dienstzeiten als PDF gesendet.')); ?>
                    </p>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="primary"><?php p($l->t('Speichern')); ?></button>
                    <button type="reset"><?php p($l->t('Zurücksetzen')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
