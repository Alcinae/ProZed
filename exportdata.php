<?php
require_once("conf/config.php");


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

require_once("vendor/php/autoload.php");
require_once("includes/sys_account.php");
session_start();

$db = getDB();

$data = $db->query("SELECT members.id, members.family_size, members.lname, EXTRACT(MONTH FROM challenge.date) as month, SUM(d_tri) as d_tri, SUM(d_ordure) as d_ordure, SUM(d_verre) as d_verre, SUM(d_compost) as d_compost FROM challenge INNER JOIN members ON challenge.member=members.id GROUP BY members.id, EXTRACT(MONTH FROM challenge.date);");
 
$month_data = $db->query("SELECT DISTINCT(EXTRACT(MONTH FROM date)) as month FROM challenge ORDER BY month ASC;");


$styleArray = [
    'borders' => [
        'vertical' => [
            'borderStyle' => Border::BORDER_THICK,
            'color' => ['argb' => '87725b'],
        ],'outline' => [
            'borderStyle' => Border::BORDER_THICK,
            'color' => ['argb' => '87725b'],
        ],
    ],
];
 
$spreadsheet = new Spreadsheet();
$Excel_writer = new Xlsx($spreadsheet);

$i = []; //line counter

$months = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre","Octobre", "Novembre", "Décembre"];
$months_mapping = []; //map month number to position in array (worksheet list)

$j = 0; //month counter
foreach($month_data->fetchAll() as $month)
{
    
    if($j != 0)
        $spreadsheet->createSheet();
        
    $spreadsheet->setActiveSheetIndex($j);
    //$dateObj = DateTime::createFromFormat('!m', $j+1);
    $activeSheet = $spreadsheet->getActiveSheet();
    $activeSheet->setTitle($months[((int) $month["month"])-1]);
    
    $activeSheet->setCellValue('A2', 'Nom');
    $activeSheet->setCellValue('B2', 'Habitants');
    $activeSheet->setCellValue('C2', 'OM');
    $activeSheet->setCellValue('D2', 'Tri');
    $activeSheet->setCellValue('E2', 'Verre');
    $activeSheet->setCellValue('F2', 'Compost');
    $temp_j = $j+1;
    $i[$month["month"]] = 3;
    $months_mapping[$month["month"]] = $j;
    $j++;
}
 //var_dump($i);


while($row = $data->fetch()) {
    $spreadsheet->setActiveSheetIndex($months_mapping[$row["month"]]);
    $activeSheet = $spreadsheet->getActiveSheet();
    
    $activeSheet->setCellValue('A'.$i[$row["month"]] , $row['lname']);
    $activeSheet->setCellValue('B'.$i[$row["month"]] , $row['family_size']);
    $activeSheet->setCellValue('C'.$i[$row["month"]] , $row['d_ordure']);
    $activeSheet->setCellValue('D'.$i[$row["month"]] , $row['d_tri']);
    $activeSheet->setCellValue('E'.$i[$row["month"]] , $row['d_verre']);
    $activeSheet->setCellValue('F'.$i[$row["month"]] , $row['d_compost']);
    $i[$row["month"]]++;
}



foreach($months_mapping as $month => $index){
    $spreadsheet->setActiveSheetIndex($index);
    $activeSheet = $spreadsheet->getActiveSheet();
    
    $activeSheet->setCellValue('A'.$i[$month] , "Total");
    $activeSheet->setCellValue('B'.$i[$month] , "=SUM(B3:B{$i[$month]})");
    $activeSheet->setCellValue('C'.$i[$month] , "=SUM(C3:C{$i[$month]})");
    $activeSheet->setCellValue('D'.$i[$month] , "=SUM(D3:D{$i[$month]})");
    $activeSheet->setCellValue('E'.$i[$month] , "=SUM(E3:E{$i[$month]})");
    $activeSheet->setCellValue('F'.$i[$month] , "=SUM(F3:F{$i[$month]})");
    
    
    $activeSheet->mergeCells("A".($i[$month]+1).":B".($i[$month]+1));
    $activeSheet->setCellValue('A'.($i[$month]+1) , "Moy. annuelle FAMILLES DEFI par habitant");
    $activeSheet->setCellValue('C'.($i[$month]+1) , "=(C{$i[$month]}/B{$i[$month]})*12");
    $activeSheet->setCellValue('D'.($i[$month]+1) , "=(D{$i[$month]}/B{$i[$month]})*12");
    $activeSheet->setCellValue('E'.($i[$month]+1) , "=(E{$i[$month]}/B{$i[$month]})*12");
    $activeSheet->setCellValue('F'.($i[$month]+1) , "=(F{$i[$month]}/B{$i[$month]})*12");
    
    //$activeSheet->getStyle('A2:F2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('87725b'); //d62359
    $activeSheet->getStyle('A1:F'.($i[$month]-1))->applyFromArray($styleArray);
    $activeSheet->getStyle('A1')->applyFromArray($styleArray);
    $activeSheet->getStyle('A'.($i[$month]).':F'.($i[$month]))->applyFromArray($styleArray);
    $activeSheet->getStyle('A'.(($i[$month]+1)).':F'.(($i[$month]+1)))->applyFromArray($styleArray);

    $activeSheet->mergeCells("A1:F1");
    $activeSheet->setCellValue("A1", $months[((int) $month)-1]);
    $activeSheet->getStyle('A1')->getFont()->getColor()->setARGB('ffffff');
    $activeSheet->getStyle('A1:F1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('87725b'); //d62359
    $activeSheet->getStyle('A2:F'.($i[$month]+1))->getFont()->getColor()->setARGB('87725b'); //d62359
    $activeSheet->getStyle('A2:A'.($i[$month]+1))->getFont()->getColor()->setARGB('d62359');
    $activeSheet->getRowDimension(($i[$month]+1))->setRowHeight(25);
    //$activeSheet->getRowDimension($i[$month])->setRowHeight(30);
    //$activeSheet->getColumnDimensionByColumn('A')->setWidth(30);
    //$activeSheet->getColumnDimensionByColumn('A')->setAutoSize(true);
    
    
}
 
$filename = 'defi famille.xlsx';
 
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'. $filename);
header('Cache-Control: max-age=0');
$Excel_writer->save('php://output');
?>
