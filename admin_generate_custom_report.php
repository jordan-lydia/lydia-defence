<?php
session_start();
require_once 'includes/db.php';
require_once 'tcpdf/tcpdf.php';

// --- CORRECTION DE PERMISSION ---
// On autorise maintenant les trois rôles à générer des rapports.
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Gestionnaire', 'Administrateur', 'Décideur'])) {
    exit('Accès non autorisé.');
}

class CustomReportPDF extends TCPDF {
    private $reportTitle = 'Rapport Personnalisé';
    private $filterSummary = '';

    public function setReportTitle($title) { $this->reportTitle = $title; }
    public function setFilterSummary($summary) { $this->filterSummary = $summary; }

    public function Header() {
        @$this->Image('images/logo_sante.png', 15, 10, 20);
        $this->SetY(10);
        $this->SetFont('helvetica', 'B', 16);
        $this->Cell(0, 15, $this->reportTitle, 0, false, 'C');
        $this->SetFont('helvetica', '', 9);
        $this->SetY(20);
        $this->Cell(0, 15, $this->filterSummary, 0, false, 'C');
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Line(15, $this->GetY(), $this->getPageWidth() - 15, $this->GetY());
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C');
    }

    public function createTable($header, $data) {
        $this->SetFont('helvetica', 'B', 9);
        $this->SetFillColor(45, 85, 150);
        $this->SetTextColor(255);
        $this->SetDrawColor(200, 200, 200);
        $w = [35, 50, 65, 15, 15];
        foreach($header as $i => $h) { $this->Cell($w[$i], 8, $h, 1, 0, 'C', 1); }
        $this->Ln();

        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(0);
        $fill = false;
        foreach ($data as $row) {
            if ($fill) { $this->SetFillColor(245, 245, 245); } else { $this->SetFillColor(255, 255, 255); }
            $this->Cell($w[0], 7, date('d/m/Y', strtotime($row['date_deces'])), 'LR', 0, 'C', true);
            $this->Cell($w[1], 7, htmlspecialchars($row['nom_zone']), 'R', 0, 'L', true);
            $this->Cell($w[2], 7, htmlspecialchars($row['nom_cause']), 'R', 0, 'L', true);
            $this->Cell($w[3], 7, $row['age_annees'], 'R', 0, 'C', true);
            $this->Cell($w[4], 7, $row['sexe'], 'R', 1, 'C', true);
            $fill = !$fill;
        }
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}

// --- TRAITEMENT DES FILTRES ET CONSTRUCTION DE LA REQUÊTE ---
$report_type = $_GET['report_type'] ?? 'full';
$params = [];
$where_clauses = ["dd.statut_validation = 'valide'"];
$filter_summary_parts = [];
$report_title = "Rapport Personnalisé";

// Gestion dynamique des types de rapports
switch ($report_type) {
    case 'daily':
        $report_title = "Rapport Journalier";
        $where_clauses[] = "dd.date_deces >= CURDATE() - INTERVAL 1 DAY";
        $filter_summary_parts[] = "pour les dernières 24 heures";
        break;
    case 'weekly':
        $report_title = "Rapport Hebdomadaire";
        $where_clauses[] = "dd.date_deces >= CURDATE() - INTERVAL 7 DAY";
        $filter_summary_parts[] = "pour les 7 derniers jours";
        break;
    case 'monthly_summary':
        $report_title = "Rapport Mensuel";
        $where_clauses[] = "YEAR(dd.date_deces) = YEAR(CURDATE() - INTERVAL 1 MONTH) AND MONTH(dd.date_deces) = MONTH(CURDATE() - INTERVAL 1 MONTH)";
        $filter_summary_parts[] = "pour le mois précédent";
        break;
    case 'annual':
        $report_title = "Rapport Annuel";
        $where_clauses[] = "YEAR(dd.date_deces) = YEAR(CURDATE())";
        $filter_summary_parts[] = "pour l'année en cours";
        break;
    case 'full':
        $report_title = "Rapport Général Complet";
        $filter_summary_parts[] = "Toutes les données";
        break;
    // Gérer les filtres personnalisés
    default:
        if (!empty($_GET['date_start'])) { $where_clauses[] = "dd.date_deces >= ?"; $params[] = $_GET['date_start']; $filter_summary_parts[] = "du " . date('d/m/Y', strtotime($_GET['date_start'])); }
        if (!empty($_GET['date_end'])) { $where_clauses[] = "dd.date_deces <= ?"; $params[] = $_GET['date_end']; $filter_summary_parts[] = "au " . date('d/m/Y', strtotime($_GET['date_end'])); }
        if (!empty($_GET['zone_id'])) { 
            $where_clauses[] = "dd.zone_sante_id = ?"; $params[] = $_GET['zone_id'];
            $stmt_zone = $pdo->prepare("SELECT nom_zone FROM zones_sante WHERE id = ?"); $stmt_zone->execute([$_GET['zone_id']]);
            if ($zone_name = $stmt_zone->fetchColumn()) $filter_summary_parts[] = "Zone: $zone_name";
        }
        break;
}

$where_sql = "WHERE " . implode(' AND ', $where_clauses);
$filter_summary = implode(' ', $filter_summary_parts);

try {
    $sql = "SELECT zs.nom_zone, cd.nom_cause, dd.date_deces, dd.age_annees, dd.sexe FROM declarations_deces dd JOIN zones_sante zs ON dd.zone_sante_id = zs.id JOIN causes_deces cd ON dd.cause_deces_id = cd.id $where_sql ORDER BY dd.date_deces DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
} catch (PDOException $e) { 
    die("Erreur de récupération des données: " . $e->getMessage()); 
}

$pdf = new CustomReportPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

$pdf->setReportTitle($report_title);
$pdf->setFilterSummary($filter_summary);
$pdf->SetCreator('STDM Platform');
$pdf->SetAuthor($_SESSION['user_nom_complet']);
$pdf->SetTitle($report_title);
$pdf->SetMargins(15, 35, 15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->AddPage();

if (empty($data)) {
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 50, 'Aucune donnée trouvée pour les critères sélectionnés.', 0, 1, 'C');
} else {
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(0, 10, 'Ce rapport a été généré avec les filtres suivants : ' . ($filter_summary ?: 'Aucun filtre appliqué.') . "\n" . '<b>Total des cas inclus : ' . count($data) . '</b>', 0, 'L', 0, 1, '', '', true, 0, true);
    $pdf->Ln(5);
    $header = ['Date Décès', 'Zone de Santé', 'Cause du Décès', 'Âge', 'Sexe'];
    $pdf->createTable($header, $data);
}

$file_name = 'rapport_'.strtolower(str_replace(' ', '_', $report_type)).'_'.date('Y-m-d').'.pdf';
$pdf->Output($file_name, 'I');
?>