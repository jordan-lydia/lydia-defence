<?php
session_start();
require_once 'includes/db.php';
// IMPORTANT : Assurez-vous que le chemin vers tcpdf.php est correct
require_once 'tcpdf/tcpdf.php';

// Sécurité
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') {
    exit('Accès non autorisé.');
}

// Classe PDF personnalisée pour un en-tête et un pied de page stylés
class PrestigePDF extends TCPDF {
    private $generatorName = '';

    public function setGeneratorName($name) {
        $this->generatorName = $name;
    }

    public function Header() {
        // Logo (ajuster le chemin si nécessaire)
        // @ est utilisé pour supprimer les erreurs si l'image n'est pas trouvée
        @$this->Image('images/logo_sante.png', 15, 10, 20, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        
        $this->SetY(10);
        $this->SetFont('helvetica', 'B', 18);
        $this->SetTextColor(40, 50, 70);
        $this->Cell(0, 15, 'Rapport Général de Mortalité', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    public function Footer() {
        $this->SetY(-20);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(128);
        
        // Ligne de séparation
        $this->Line(15, $this->GetY() - 2, $this->getPageWidth() - 15, $this->GetY() - 2);

        $this->MultiCell(0, 10, "Généré par : {$this->generatorName}\nDate de génération : " . date('d/m/Y à H:i'), 0, 'L', 0, 0, '', '', true);
        
        $this->SetY(-15);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

// Récupération des données
try {
    $sql = "SELECT zs.nom_zone, cd.nom_cause, dd.date_deces, dd.age_annees, dd.sexe 
            FROM declarations_deces dd 
            JOIN zones_sante zs ON dd.zone_sante_id = zs.id 
            JOIN causes_deces cd ON dd.cause_deces_id = cd.id 
            WHERE dd.statut_validation = 'valide' 
            ORDER BY dd.date_deces DESC";
    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll();
} catch (PDOException $e) { 
    die("Erreur de récupération des données: " . $e->getMessage()); 
}

// Création du document PDF
$pdf = new PrestigePDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

// Définition des métadonnées du document
$pdf->setGeneratorName($_SESSION['user_nom_complet']);
$pdf->SetCreator('STDM Platform');
$pdf->SetAuthor($_SESSION['user_nom_complet']);
$pdf->SetTitle('Rapport Général Complet de Mortalité');
$pdf->SetSubject('Rapport consolidé de toutes les déclarations validées');

// Définition des marges
$pdf->SetMargins(15, 30, 15);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Ajout d'une page
$pdf->AddPage();

// Introduction du rapport
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(0);
$html_summary = '<p>Ce document est un rapport consolidé de toutes les déclarations de décès validées au sein de la plateforme STDM. <br><b>Total des cas inclus dans ce rapport : ' . count($data) . '</b></p>';
$pdf->writeHTML($html_summary, true, false, true, false, '');
$pdf->Ln(5);

// En-tête du tableau
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetFillColor(45, 85, 150); // Un bleu plus sobre
$pdf->SetTextColor(255);
$pdf->SetDrawColor(200, 200, 200);
$pdf->SetLineWidth(0.3);

$pdf->Cell(35, 8, 'Date Décès', 1, 0, 'C', 1);
$pdf->Cell(50, 8, 'Zone de Santé', 1, 0, 'C', 1);
$pdf->Cell(65, 8, 'Cause du Décès', 1, 0, 'C', 1);
$pdf->Cell(15, 8, 'Âge', 1, 0, 'C', 1);
$pdf->Cell(15, 8, 'Sexe', 1, 1, 'C', 1);

// Données du tableau
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(0);
$fill = false;
foreach ($data as $row) {
    // --- CORRECTION DE L'ERREUR ---
    if ($fill) {
        $pdf->SetFillColor(245, 245, 245);
    } else {
        $pdf->SetFillColor(255, 255, 255);
    }

    // Protection contre les textes trop longs qui débordent
    $causeText = htmlspecialchars($row['nom_cause']);
    if ($pdf->getStringWidth($causeText) > 60) {
        $causeText = substr($causeText, 0, 45) . '...';
    }
    
    $pdf->Cell(35, 7, date('d/m/Y', strtotime($row['date_deces'])), 'LR', 0, 'C', true);
    $pdf->Cell(50, 7, htmlspecialchars($row['nom_zone']), 'R', 0, 'L', true);
    $pdf->Cell(65, 7, $causeText, 'R', 0, 'L', true);
    $pdf->Cell(15, 7, $row['age_annees'], 'R', 0, 'C', true);
    $pdf->Cell(15, 7, $row['sexe'], 'R', 1, 'C', true);
    
    $fill = !$fill;
}
$pdf->Cell(180, 0, '', 'T');

// Section Signature
$pdf->Ln(20);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 10, 'Signature de l\'Administrateur :', 0, 1);
$pdf->Ln(15);
$pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX() + 70, $pdf->GetY());
$pdf->Cell(0, 10, htmlspecialchars($_SESSION['user_nom_complet']), 0, 1);

// Fermeture et sortie du document PDF
$pdf->Output('rapport_general_'.date('Y-m-d').'.pdf', 'I');
?>