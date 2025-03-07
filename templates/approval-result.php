<?php
/** @var array $_ */
/** @var \OCP\IL10N $l */
/** @var \OCP\Defaults $theme */
$message = $_['message'];
$status = $_['status'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php p($l->t('Dienstzeit - Genehmigungsergebnis')); ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <link rel="stylesheet" href="<?php p(\OC::$server->getURLGenerator()->linkTo('core', 'css/styles.css')); ?>">
    <link rel="stylesheet" href="<?php p(\OC::$server->getURLGenerator()->linkToRoute('dienstzeiten_app.static.style')); ?>">
</head>
<body>
    <div id="app-content">
        <div id="app-content-wrapper">
            <div class="section">
                <h2><?php p($l->t('Genehmigungsergebnis')); ?></h2>
                
                <div class="result-container <?php p($status); ?>">
                    <div class="result-icon">
                        <?php if ($status === 'approved'): ?>
                            <span class="icon-checkmark"></span>
                        <?php elseif ($status === 'rejected'): ?>
                            <span class="icon-error"></span>
                        <?php else: ?>
                            <span class="icon-info"></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="result-message">
                        <p><?php p($message); ?></p>
                    </div>
                </div>
                
                <div class="button-container">
                    <a href="<?php p(\OC::$server->getURLGenerator()->getBaseUrl()); ?>" class="button">
                        <?php p($l->t('Zurück zu Nextcloud')); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
