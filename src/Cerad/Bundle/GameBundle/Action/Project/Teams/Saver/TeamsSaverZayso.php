<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Saver;

use Cerad\Bundle\CoreBundle\Event\Team\ChangedTeamEvent;

class TeamsSaverZaysoResults
{
    public $commit = false;
    
    public $basename;
    
    public $total    = 0;
    public $created  = 0;
    public $updated  = 0;
    public $deleted  = 0;
}
class TeamsSaverZayso
{
    protected $results;
    
    protected $teamRepo;
        
    protected $dispatcher;
    
    public function __construct($teamRepo)
    {
        $this->teamRepo = $teamRepo;
    }
    public function setDispatcher($dispatcher) { $this->dispatcher = $dispatcher; }
    
    protected function dispatch($team,$groupSlot = null)
    {
        $event = new ChangedTeamEvent($team,$groupSlot);
        
        $this->dispatcher->dispatch(ChangedTeamEvent::Changed,$event);
    }
    /* =============================================
     * TODO: Implement delete with negative number
     */
    protected function saveTeam($item)
    {   
        $results = $this->results;
        
        $key        = $item['key'];
        $num        = (int)$item['num'];
        $levelKey   = $item['levelKey'];
        $projectKey = $item['projectKey'];
       
        $team = $this->teamRepo->findOneByKey($key);
        
        if (!$team)
        {
            // Its been deleted
            if ($num < 0) return;

            $team = $this->teamRepo->createTeam();
            $team->setKey       ($key);
            $team->setNum       ($num);
            $team->setLevelKey  ($levelKey);
            $team->setProjectKey($projectKey);
            
            $team->setName  ($item['name']);
            $team->setOrgKey($item['region']);
            $team->setPoints($item['points']);
            
            $results->created++;
            $this->teamRepo->persist($team);
            $this->dispatch($team);
            return;
        }
        if ($num < 0)
        {
            // Delete
            // TODO: Dispatch a RemovedTeam message
            $this->teamRepo->remove($team);
            $results->deleted++;
            return;
        }
        $changed = false;

        if ($item['name'] != $team->getName())
        {
            // TODO: Need to propogate name changes to the game_team
            // Or maybe send an event?
            $team->setName($item['name']);
            if (!$changed) $results->updated++;
            $changed = true;
        }
        if ($item['region'] != $team->getOrgKey())
        {
            $team->setOrgKey($item['region']);
            if (!$changed) $results->updated++;
            $changed = true;
        }
        if ($item['points'] != $team->getPoints())
        {
            $team->setPoints($item['points']);
            if (!$changed) $results->updated++;
            $changed = true;
        }
        if ($changed) $this->dispatch($team);
    }
    /* ==============================================================
     * The syncer just sends an event out for each slot
     */
    protected function syncTeam($item)
    {
        $team = $this->teamRepo->findOneByKey($item['key']);
        
        if (!$team) return;
        
        foreach($item['slots'] as $slot)
        {
            $this->dispatch($team,$slot);
        }
    }
    /* ==============================================================
     * Main entry point
     * 21 June 2014
     * update game_teams set teamKey = null, teamName = null, teamPoints = null;
     */
    public function save($teams,$commit = false,$op = 0)
    {
        $this->results = $results = new TeamsSaverZaysoResults();
        
        $results->commit = $commit;
        $results->total = count($teams);
        
        // Updated Team information
        foreach($teams as $item)
        {
            if ($op == 1) $this->saveTeam($item);
            if ($op == 2) $this->syncTeam($item);
        }
        if ($results->commit) $this->teamRepo->commit();
         
        return $results;
    }
}
?>
