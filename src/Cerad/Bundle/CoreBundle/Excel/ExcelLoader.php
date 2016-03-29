<?php
namespace Cerad\Bundle\CoreBundle\Excel;

/* ========================================================
 * 23 Sep 2013
 * This is intened to be extended by import routines
 * 
 * Might be metter to just use $excel directly
 * 
 * 03 June 2014
 * This is always consfusing whenever I look at it
 * Excel should be reader
 * Do I really need Loader and Reader?
 * 
 * 12 June 2014
 * Copied from loader 
 * Wanted to deal better with optional columns such as Referee
 */
class ExcelLoader
{
    protected $excel;
    protected $items  = array();
    protected $errors = array();
    
    protected $record = array(
      // 'region'     => array('cols' => 'Region',         'req' => true,  'default' => 0),
    );
    protected $map = array();
    
    public function __construct()
    {
        $this->excel = new Reader();
    }
    protected function processDataRow($row)
    {
        $item = array();
        foreach($this->record as $name => $params)
        {
            if (isset($params['default'])) $default = $params['default'];
            else                           $default = null;
            $item[$name] = $default; // Even if not found, item will get an entry
        }
        foreach($row as $index => $value)
        {
            if (isset($this->map[$index]))
            {
                $name = $this->map[$index];
                $item[$name] = trim($value); // If found always get something not null
            }
        }
        return $item;
    }
    protected function processHeaderRow($row)
    {
        $found  = array();
        $record = $this->record;
        foreach($row as $index => $colName)
        {
            $colName = trim($colName);
            foreach($record as $name => $params)
            {
                if (is_array($params['cols'])) $cols = $params['cols'];
                else                           $cols = array($params['cols']);
                foreach($cols as $col)
                {
                    if ($col == $colName)
                    {
                        if (isset($params['plus'])) $plus = $params['plus'];
                        else                        $plus = 0;
                        
                        $this->map[$index + $plus] = $name;
                        $found[$name] = true;
                    }
                }
            }
        }

        // Make sure all required attributes found
        foreach($record as $name => $params)
        {
            if (isset($params['req']) && $params['req'])
            {
                if (!isset($found[$name]))
                {
                    if (is_array($params['cols'])) $cols = $params['cols'];
                    else                           $cols = array($params['cols']);
                    $cols = implode(' OR ',$cols);
                    $this->errors[] = "Missing $cols";
                }
            }
        }
    }
    public function load($inputFileName, $worksheetName = null)
    {
        $reader = $this->excel->load($inputFileName);

        if ($worksheetName) $ws = $reader->getSheetByName($worksheetName);
        else                $ws = $reader->getSheet(0);
        
        $rows = $ws->toArray();
        
        $header = array_shift($rows);
        
        $this->processHeaderRow($header);
        
        // Insert each record
        foreach($rows as $row)
        {
            $item = $this->processDataRow($row);
            
            $this->processItem($item);
        }
        return $this->items;
    }
    protected function processItem($item)
    {
        print_r($item); die("\n");
    }
    protected function processTime($time)
    {
        return $this->excel->processTime($time);
    }
    protected function processDate($date)
    {
        return $this->excel->processDate($date);
    }
    protected function processDateTime($dateTime)
    {
        return $this->excel->processDateTime($dateTime);
    }
}
?>
