<?php
namespace Cerad\Bundle\TournBundle\Schedule\Official;

/* ============================================
 * Basic referee schedule exporter
 */
class ScheduleOfficialExportXLS
{
    protected $counts = array();
    
    protected $widths = array
    (
        'Game' =>  6, 'Game#' =>  6,

        'DOW' =>  5, 'Date' =>  12, 'Time' => 10,
        
        'Venue' =>  8, 'Field' =>  6, 'Type' => 5, 'Pool' => 12,
            
        'Home Team' => 26, 'Away Team' => 26,
        
        'Referee' => 26, 'AR1' => 26, 'AR2' => 26,
        
        'Name' => 26, 'Pos' => 6, 'YC' => 3, 'RC' => 3,
    );
    protected $center = array
    (
        'Game',
    );
    public function __construct($excel)
    {
        $this->excel = $excel;
    }
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
    protected function setRow($ws,$map,$person,&$row)
    {
        $row++;
        $col = 0;
        foreach($map as $propName)
        {
            $ws->setCellValueByColumnAndRow($col++,$row,$person[$propName]);
        }
        return $row;
    }
    /* ========================================================
     * Generates the games listing
     */
    public function generateGames($ws,$games)
    {
        // Only the keys are currently being used
        $map = array(
            'Game'     => 'game',
            'Date'     => 'date',
            'DOW'      => 'dow',
            'Time'     => 'time',
            'Venue'    => 'venue',
            'Field'    => 'field',
            'Type'     => 'type',
            'Pool'     => 'pool',
            
            'Home Team' => 'homeTeam',
            'Away Team' => 'awayTeam',
            
            'Referee' => 'referee',
            'AR1'     => 'AR1',
            'AR2'     => 'AR2',
            
            'Game#'   => 'game',
        );
        $ws->setTitle('Games');
        
        $row = $this->setHeaders($ws,$map);
        
        $timex = null;
        
        foreach($games as $game)
        {   
            $row++;
            $col = 0;
            
            // Date/Time
            $dt   = $game->getDtBeg();
            $dow  = $dt->format('D');
            $date = $dt->format('M d y');
            $time = '_' . $dt->format('H:i A'); //('g:i A');
            
            // Skip on time changes
            if ($timex != $time)
            {
                if ($timex) $row++;
                $timex = $time;
            }
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getNum());
            $ws->setCellValueByColumnAndRow($col++,$row,$date);
            $ws->setCellValueByColumnAndRow($col++,$row,$dow);
            $ws->setCellValueByColumnAndRow($col++,$row,$time);
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getField()->getVenue());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getField()->getName ());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getGroup());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getLevelId());
            
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getHomeTeam()->getName());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getAwayTeam()->getName());
            
            foreach($game->getOfficials() as $gameOfficial)
            {
                $ws->setCellValueByColumnAndRow($col++,$row,$gameOfficial->getPersonNameFull());
            }
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getNum());
        }
        return;
    }
    /* =======================================================================
     * Main entry point
     */
    public function generate($games)
    {
        // Spreadsheet
        $ss = $this->excel->newSpreadSheet(); 
        $ws = $ss->getSheet(0);
        
        $ws->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $ws->getPageSetup()->setPaperSize  (\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $ws->getPageSetup()->setFitToPage(true);
        $ws->getPageSetup()->setFitToWidth(1);
        $ws->getPageSetup()->setFitToHeight(0);
        $ws->setPrintGridLines(true);
        
        $this->generateGames($ws,$games);
        
        $ws1 = $ss->createSheet(1);
        $this->processOfficials($ws1,$games);
        
        // Output
        $ss->setActiveSheetIndex(0);
        $objWriter = $this->excel->newWriter($ss); // \PHPExcel_IOFactory::createWriter($ss, 'Excel5');

        ob_start();
        $objWriter->save('php://output'); // Instead of file name
        return ob_get_clean();
    }
    /* ===========================================================
     * Add a sheet listing current assignments for each official
     * Should probably get moved to it's own processor
     */
    protected function processOfficials($ws,$games)
    {
        // Make a sorted array of officials from games
        $officials = array();
        foreach($games as $game)
        {
            foreach($game->getOfficials() as $official)
            {
                $name = $official->getPersonNameFull();
                if ($name)
                {
                    $officials[$name][] = $official;
                }
            }
        }
        ksort($officials);
        
        // Genetare
        $this->generateOfficials($ws,$officials);
    }
    /* ========================================================
     * Generates the officials listing
     */
    public function generateOfficials($ws,$officials)
    {
        // Only the keys are currently being used
        $map = array(
            'Name'     => 'name',
            'Pos'      => 'pos',
            'YC'       => 'yc',
            'RC'       => 'rc',
            'Game'     => 'game',
            'Date'     => 'date',
            'DOW'      => 'dow',
            'Time'     => 'time',
            'Venue'    => 'venue',
            'Field'    => 'field',
            'Type'     => 'type',
            'Pool'     => 'pool',
            
            'Home Team' => 'homeTeam',
            'Away Team' => 'awayTeam',
        );
        $ws->setTitle('Officials');
        
        $row = $this->setHeaders($ws,$map);
        
        foreach($officials as $officialSlots)
        {   
            $row++;
            
            foreach($officialSlots as $official) {
                
            
            $row++;
            $col = 0;
            
            // Official
            $name = $official->getPersonNameFull();
            $pos  = $official->getRole();
            $game = $official->getGame();
            
            $ws->setCellValueByColumnAndRow($col++,$row,$name);
            $ws->setCellValueByColumnAndRow($col++,$row,$pos);
           
            // Cards
            $yc = $rc = 0;
            
            $homeTeamReport = $game->getHomeTeam()->getReport();
            $awayTeamReport = $game->getAwayTeam()->getReport();
            
            if ($homeTeamReport->getPlayerWarnings()) $yc += $homeTeamReport->getPlayerWarnings();
            if ($awayTeamReport->getPlayerWarnings()) $yc += $awayTeamReport->getPlayerWarnings();
            
            if ($homeTeamReport->getPlayerEjections()) $rc += $homeTeamReport->getPlayerEjections();
            if ($awayTeamReport->getPlayerEjections()) $rc += $awayTeamReport->getPlayerEjections();
            
            if ($yc) $ws->setCellValueByColumnAndRow($col++,$row,$yc);
            else     $col++;
            if ($rc) $ws->setCellValueByColumnAndRow($col++,$row,$rc);
            else     $col++;
            
            // Date/Time
            $dt   = $game->getDtBeg();
            $dow  = $dt->format('D');
            $date = $dt->format('M d y');
            $time = '_' . $dt->format('H:i A'); //('g:i A');
            
            // Break this out later
            // Need to follow game sheet format to support easy copy/paste
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getNum());
            $ws->setCellValueByColumnAndRow($col++,$row,$date);
            $ws->setCellValueByColumnAndRow($col++,$row,$dow);
            $ws->setCellValueByColumnAndRow($col++,$row,$time);
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getField()->getVenue());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getField()->getName ());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getGroup());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getLevelId());
            
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getHomeTeam()->getName());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getAwayTeam()->getName());
            
        }}
        return;
    }
}
?>
