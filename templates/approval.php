<?php
/** @var array $_ */
/** @var \OCP\IL10N $l */
/** @var \OCP\Defaults $theme */
$dienstzeit = $_['dienstzeit'];
$token = $_['token'];
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php p($l->t('Dienstzeit genehmigen')); ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <link rel="stylesheet" href="<?php p(\OC::$server->getURLGenerator()->linkTo('core', 'css/styles.css')); ?>">
    <link rel="stylesheet" href="<?php p(\OC::$server->getURLGenerator()->linkToRoute('dienstzeiten_app.static.style')); ?>">
    <script src="<?php p(\OC::$server->getURLGenerator()->linkTo('core', 'js/oc.js')); ?>"></script>
    <script src="<?php p(\OC::$server->getURLGenerator()->linkToRoute('dienstzeiten_app.static.script')); ?>"></script>
</head>
<body>
    <div id="app-content">
        <div id="app-content-wrapper">
            <div class="section">
                <h2><?php p($l->t('Dienstzeit genehmigen')); ?></h2>
                
                <div class="dienstzeit-details">
                    <h3><?php p($l->t('Details zum Dienstzeit-Eintrag')); ?></h3>
                    
                    <div class="details-container">
                        <div class="detail-group">
                            <h4><?php p($l->t('Mitarbeiter-Informationen')); ?></h4>
                            <p>
                                <strong><?php p($l->t('Name:')); ?></strong> 
                                <?php p($dienstzeit->getFirstName() . ' ' . $dienstzeit->getLastName()); ?>
                            </p>
                            <p>
                                <strong><?php p($l->t('E-Mail:')); ?></strong> 
                                <?php p($dienstzeit->getEmail()); ?>
                            </p>
                        </div>
                        
                        <div class="detail-group">
                            <h4><?php p($l->t('Dienstzeit-Informationen')); ?></h4>
                            <p>
                                <strong><?php p($l->t('Datum:')); ?></strong> 
                                <?php p($dienstzeit->getServiceDate()->format('d.m.Y')); ?>
                            </p>
                            <p>
                                <strong><?php p($l->t('Beginn:')); ?></strong> 
                                <?php p($dienstzeit->getStartTime()->format('H:i')); ?> <?php p($l->t('Uhr')); ?>
                            </p>
                            <p>
                                <strong><?php p($l->t('Ende:')); ?></strong> 
                                <?php p($dienstzeit->getEndTime()->format('H:i')); ?> <?php p($l->t('Uhr')); ?>
                            </p>
                            <p>
                                <strong><?php p($l->t('Wache:')); ?></strong> 
                                <?php p($dienstzeit->getStation()); ?>
                                <?php if ($dienstzeit->getStation() === 'Sonstiges' && !empty($dienstzeit->getOtherDetails())): ?>
                                    (<?php p($dienstzeit->getOtherDetails()); ?>)
                                <?php endif; ?>
                            </p>
                            <p>
                                <strong><?php p($l->t('Mehrarbeit durch Einsatz:')); ?></strong> 
                                <?php p($dienstzeit->getOvertimeDueToEmergency() ? $l->t('Ja') : $l->t('Nein')); ?>
                                <?php if ($dienstzeit->getOvertimeDueToEmergency() && !empty($dienstzeit->getEmergencyNumber())): ?>
                                    (<?php p($l->t('Einsatznummer:')); ?> <?php p($dienstzeit->getEmergencyNumber()); ?>)
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <div class="detail-group">
                            <h4><?php p($l->t('Unterschrift')); ?></h4>
                            <?php if (!empty($dienstzeit->getSignature())): ?>
                                <div class="signature-image">
                                    <img src="<?php p($dienstzeit->getSignature()); ?>" alt="<?php p($l->t('Unterschrift')); ?>">
                                </div>
                            <?php else: ?>
                                <p><?php p($l->t('Keine Unterschrift vorhanden.')); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="approval-form">
                    <h3><?php p($l->t('Genehmigungsentscheidung')); ?></h3>
                    
                    <form id="approval-form" method="post" action="<?php p(\OC::$server->getURLGenerator()->linkToRoute('dienstzeiten_app.dienstzeiten.processApproval', ['id' => $dienstzeit->getId(), 'token' => $token])); ?>">
                        <div class="form-group">
                            <label for="action"><?php p($l->t('Entscheidung')); ?> *</label>
                            <div class="radio-container">
                                <input type="radio" id="action-approve" name="action" value="approve" required>
                                <label for="action-approve"><?php p($l->t('Genehmigen')); ?></label>
                            </div>
                            <div class="radio-container">
                                <input type="radio" id="action-reject" name="action" value="reject" required>
                                <label for="action-reject"><?php p($l->t('Ablehnen')); ?></label>
                            </div>
                        </div>
                        
                        <div class="form-group hidden" id="rejection-reason-group">
                            <label for="rejection_reason"><?php p($l->t('Grund für die Ablehnung')); ?> *</label>
                            <textarea id="rejection_reason" name="rejection_reason" rows="4"></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="primary"><?php p($l->t('Absenden')); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ein- und Ausblenden des Feldes für den Ablehnungsgrund
            var actionApprove = document.getElementById('action-approve');
            var actionReject = document.getElementById('action-reject');
            var rejectionReasonGroup = document.getElementById('rejection-reason-group');
            var rejectionReason = document.getElementById('rejection_reason');
            
            function updateRejectionReasonVisibility() {
                if (actionReject.checked) {
                    rejectionReasonGroup.classList.remove('hidden');
                    rejectionReason.setAttribute('required', 'required');
                } else {
                    rejectionReasonGroup.classList.add('hidden');
                    rejectionReason.removeAttribute('required');
                }
            }
            
            actionApprove.addEventListener('change', updateRejectionReasonVisibility);
            actionReject.addEventListener('change', updateRejectionReasonVisibility);
            
            // Formular-Validierung
            var form = document.getElementById('approval-form');
            form.addEventListener('submit', function(event) {
                var isValid = true;
                
                // Prüfen, ob eine Entscheidung ausgewählt wurde
                if (!actionApprove.checked && !actionReject.checked) {
                    isValid = false;
                    alert('<?php p($l->t('Bitte wählen Sie eine Entscheidung aus.')); ?>');
                }
                
                // Wenn "Ablehnen" ausgewählt, prüfen, ob ein Grund angegeben wurde
                if (actionReject.checked && !rejectionReason.value.trim()) {
                    isValid = false;
                    alert('<?php p($l->t('Bitte geben Sie einen Grund für die Ablehnung an.')); ?>');
                }
                
                if (!isValid) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
