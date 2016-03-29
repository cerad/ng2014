<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Results\Export;

use Cerad\Bundle\CoreBundle\Excel\Export as ExcelExport;

class ResultsExportXLS extends ExcelExport
{
    protected $counts = array();
    
    protected $widths = array
    (
        'Game' =>  6, 'Game#' =>  6,

        'DOW' =>  5, 'Date' =>  12, 'Time' => 10,
        
        'Venue' => 16, 'Field' =>  6, 'Type' => 5, 'Group' => 12, 'Level' => 12,
            
        'Home Team Group' => 26, 'Away Team Group' => 26,
        'Home Team Name'  => 26, 'Away Team Name'  => 26,
        
        'Referee' => 26, 'AR1' => 26, 'AR2' => 26,
        
        'Name' => 26, 'Pos' => 6, 'YC' => 3, 'RC' => 3,
    );
    protected $center = array
    (
        'Game',
    );
    protected function setHeaders($ws,$map,$row = 1)
    {
        $col = 0;
        foreach(array_keys($map) as $header)
        {
            $ws->getColumnDimensionByColumn($col)->setWidth($this->widths[$header]);
            $ws->setCellValueByColumnAndRow($col++,$row,$header);
            
            if (in_array($header,$this->center) == true)
            {
                // Works but not for multiple sheets?
                // $ws->getStyle($col)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            }
        }
        return $row;
    }
    /* =======================================================================
     * Process a pool
     */
    protected function processPool($ws,$level,&$poolCount,$poolKey,$games,$teams)
    {
        $ws->setCellValueByColumnAndRow(0,$poolCount,$level->getKey());
        $ws->setCellValueByColumnAndRow(1,$poolCount,$poolCount);
        $ws->setCellValueByColumnAndRow(2,$poolCount,$poolKey);
        $ws->setCellValueByColumnAndRow(3,$poolCount,count($games));
        $ws->setCellValueByColumnAndRow(4,$poolCount,count($teams));
        $poolCount++;
    }
     /* =======================================================================
     * Process each level
     */
    protected function processLevel($ss,&$sheetNum,$model,$level)
    {
        // Ignore vip
        $levelKey = $level->getKey();
        if (strpos($levelKey,'VIP')) return;
        
        $ws = $ss->createSheet($sheetNum++);
        
        $pageSetup = $ws->getPageSetup();
        
        $pageSetup->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $pageSetup->setPaperSize  (\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $pageSetup->setFitToPage(true);
        $pageSetup->setFitToWidth(1);
        $pageSetup->setFitToHeight(0);
        
        $ws->setPrintGridLines(true);
        
        $ws->setTitle($levelKey);
        
        $ws->setCellValueByColumnAndRow(0,1,$levelKey);
        
        // Pools (each pool has has games and teams)
        $pools = $model->loadPools($levelKey);
        $poolCount = 1;
        foreach($pools as $poolKey => $pool)
        {
            $gamesPP = $pool['games'];
            $teamsPP = $pool['teams'];
            
            $this->processPool($ws,$level,$poolCount,$poolKey,$gamesPP,$teamsPP);
        }
        
        // Medal rounds
        $gamesQF = $model->loadGames('QF',$levelKey);
        $gamesSF = $model->loadGames('SF',$levelKey);
        $gamesFM = $model->loadGames('FM',$levelKey);
        
    }
    /* =======================================================================
     * Main entry point
     */
    public function generate($model)
    {
        // Spreadsheet
        $ss = $this->createSpreadsheet(); 
        $sheetNum = 0;
        
        $levels = $model->getLevels();
        foreach($levels as $level)
        {
            $this->processLevel($ss,$sheetNum,$model,$level);
        }
        // Output
        $ss->setActiveSheetIndex(0);
        $objWriter = $this->createWriter($ss);

        ob_start();
        $objWriter->save('php://output'); // Instead of file name
        return ob_get_clean();
    }
    public function getFileExtension() { return 'xlsx'; }
    public function getContentType()   { return 'application/vnd.ms-excel'; }
}
?>
