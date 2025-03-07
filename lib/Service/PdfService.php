<?php

namespace OCA\DienstzeitenApp\Service;

use OCA\DienstzeitenApp\Db\Dienstzeit;
use OCP\IURLGenerator;
use OCP\IL10N;

class PdfService {

    private $urlGenerator;
    private $l10n;
    private $appName;

    public function __construct(
        string $appName,
        IURLGenerator $urlGenerator,
        IL10N $l10n
    ) {
        $this->appName = $appName;
        $this->urlGenerator = $urlGenerator;
        $this->l10n = $l10n;
    }

    /**
     * Erstellt ein PDF-Dokument für einen Dienstzeit-Eintrag
     *
     * @param Dienstzeit $dienstzeit Der Dienstzeit-Eintrag
     * @return string Das PDF-Dokument als Binärdaten
     */
    public function generatePdfForDienstzeit(Dienstzeit $dienstzeit): string {
        // Wir verwenden hier TCPDF, das in Nextcloud eingebunden ist
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Metadaten setzen
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Dienstzeiten-App');
        $pdf->SetTitle('Dienstzeit ' . $dienstzeit->getId());
        $pdf->SetSubject('Dienstzeit-Eintrag');
        $pdf->SetKeywords('Dienstzeit, Nextcloud, App');
        
        // Header und Footer entfernen
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Standard-Schriftart setzen
        $pdf->SetFont('helvetica', '', 11);
        
        // Seitenränder
        $pdf->SetMargins(15, 15, 15);
        
        // Automatisches Seitenumbruchverhalten
        $pdf->SetAutoPageBreak(true, 15);
        
        // Neue Seite hinzufügen
        $pdf->AddPage();
        
        // Titel
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Dienstzeit-Nachweis', 0, 1, 'C');
        $pdf->Ln(5);
        
        // Mitarbeiter-Informationen
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Mitarbeiter-Informationen', 0, 1);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(50, 8, 'Name:', 0, 0);
        $pdf->Cell(0, 8, $dienstzeit->getFirstName() . ' ' . $dienstzeit->getLastName(), 0, 1);
        $pdf->Cell(50, 8, 'E-Mail:', 0, 0);
        $pdf->Cell(0, 8, $dienstzeit->getEmail(), 0, 1);
        $pdf->Ln(5);
        
        // Dienstzeit-Informationen
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Dienstzeit-Details', 0, 1);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(50, 8, 'Datum:', 0, 0);
        $pdf->Cell(0, 8, $dienstzeit->getServiceDate()->format('d.m.Y'), 0, 1);
        $pdf->Cell(50, 8, 'Beginn:', 0, 0);
        $pdf->Cell(0, 8, $dienstzeit->getStartTime()->format('H:i') . ' Uhr', 0, 1);
        $pdf->Cell(50, 8, 'Ende:', 0, 0);
        $pdf->Cell(0, 8, $dienstzeit->getEndTime()->format('H:i') . ' Uhr', 0, 1);
        $pdf->Cell(50, 8, 'Wache:', 0, 0);
        $pdf->Cell(0, 8, $dienstzeit->getStation(), 0, 1);
        
        // Wenn Sonstiges ausgewählt wurde, Details anzeigen
        if ($dienstzeit->getStation() === 'Sonstiges' && !empty($dienstzeit->getOtherDetails())) {
            $pdf->Cell(50, 8, 'Details zur Wache:', 0, 0);
            $pdf->Cell(0, 8, $dienstzeit->getOtherDetails(), 0, 1);
        }
        
        // Mehrarbeit durch Einsatz
        $pdf->Cell(50, 8, 'Mehrarbeit durch Einsatz:', 0, 0);
        $pdf->Cell(0, 8, $dienstzeit->getOvertimeDueToEmergency() ? 'Ja' : 'Nein', 0, 1);
        
        // Wenn Mehrarbeit durch Einsatz, Einsatznummer anzeigen
        if ($dienstzeit->getOvertimeDueToEmergency() && !empty($dienstzeit->getEmergencyNumber())) {
            $pdf->Cell(50, 8, 'Einsatznummer:', 0, 0);
            $pdf->Cell(0, 8, $dienstzeit->getEmergencyNumber(), 0, 1);
        }
        
        $pdf->Ln(5);
        
        // Genehmigungsinformationen
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Genehmigung', 0, 1);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(50, 8, 'Status:', 0, 0);
        $pdf->Cell(0, 8, $this->translateStatus($dienstzeit->getStatus()), 0, 1);
        
        if ($dienstzeit->getStatus() === 'approved') {
            $pdf->Cell(50, 8, 'Genehmigt am:', 0, 0);
            $pdf->Cell(0, 8, $dienstzeit->getApprovedAt()->format('d.m.Y H:i') . ' Uhr', 0, 1);
            $pdf->Cell(50, 8, 'Genehmigt von:', 0, 0);
            $pdf->Cell(0, 8, $dienstzeit->getApprovedBy(), 0, 1);
        } elseif ($dienstzeit->getStatus() === 'rejected' && !empty($dienstzeit->getRejectionReason())) {
            $pdf->Cell(50, 8, 'Grund für Ablehnung:', 0, 0);
            $pdf->MultiCell(0, 8, $dienstzeit->getRejectionReason(), 0, 'L');
        }
        
        $pdf->Ln(5);
        
        // Unterschrift
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Unterschrift', 0, 1);
        
        // Unterschriftenbild einfügen
        if (!empty($dienstzeit->getSignature())) {
            // Base64-decodieren (wir gehen davon aus, dass die Unterschrift als Daten-URL gespeichert ist)
            $signatureData = $dienstzeit->getSignature();
            if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $signatureData, $matches)) {
                $imageType = $matches[1];
                $imageData = base64_decode($matches[2]);
                
                // Temporäre Datei für das Bild erstellen
                $tempFile = tempnam(sys_get_temp_dir(), 'signature_');
                file_put_contents($tempFile, $imageData);
                
                // Bild einfügen
                $pdf->Image($tempFile, 15, $pdf->GetY(), 60, 30, $imageType);
                
                // Temporäre Datei löschen
                unlink($tempFile);
                
                $pdf->Ln(35); // Platz für das Bild
            }
        }
        
        // Fußzeile mit Informationen zur PDF-Erstellung
        $pdf->SetY(-25);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, 'Erstellt mit der Dienstzeiten-App am ' . date('d.m.Y H:i:s'), 0, 1, 'C');
        $pdf->Cell(0, 5, 'Dieses Dokument wurde elektronisch erstellt und ist ohne Unterschrift gültig.', 0, 1, 'C');
        
        // PDF als String zurückgeben
        return $pdf->Output('', 'S');
    }
    
    /**
     * Übersetzt den Status in eine benutzerfreundliche Bezeichnung
     *
     * @param string $status Status-Code
     * @return string Benutzerfreundliche Bezeichnung
     */
    private function translateStatus(string $status): string {
        switch ($status) {
            case 'pending':
                return 'Offen';
            case 'approved':
                return 'Genehmigt';
            case 'rejected':
                return 'Abgelehnt';
            default:
                return $status;
        }
    }
}
