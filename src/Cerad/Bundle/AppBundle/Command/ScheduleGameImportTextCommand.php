<?php

namespace Cerad\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

class ScheduleGameImportTextCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_app__schedule_game__import_text');
        $this->setDescription('Load Text Schedule');
        $this->addArgument   ('file', InputArgument::REQUIRED, 'Schedule');
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // The request listener is of course not requesting
        $projectRepo = $this->getService('cerad_project.project_repository');
        $projectKey  = $this->getParameter('cerad_project_project_default');
        $project = $projectRepo->find($projectKey);
        
        $date = $input->getArgument('file');
        
        $fileCore = sprintf('data/ScheduleGamesCore%s.txt',$date);
        $this->processGames($project,$fileCore);
        
        
        $fileExtra = sprintf('data/ScheduleGamesExtra%s.txt',$date);
        $this->processGames($project,$fileExtra);
                
        return;
        
        $fileTeams = 'data/ScheduleGames20140520.xlsx';
        $this->processTeams($project,$fileTeams,'Teams Core 15 May');
        $this->processTeams($project,$fileTeams,'Teams Extra 15 May');
        
        return; if ($output);
    }
    protected function processGames($project,$file)
    {   
        $readGames = new ScheduleGameImportReadText();
        $games = $readGames->read($file,$project);
        
        echo sprintf("%s: %d\n",$file,count($games));
        
        file_put_contents($file . '.yml',Yaml::dump($games,10));
        
        $saveCSV = new ScheduleGameImportSaveCSV($games);
        file_put_contents($file . '.csv',$saveCSV->save($games));
        
        $saveORM = $this->getService('cerad_game__project__schedule_game__save_orm');
        $results = $saveORM->save($games,true);
        print_r($results);
        return;        
    }
    protected function processTeams($project,$file,$sheet)
    {
        $convert = $this->getService('cerad_game__convert_teams__rick_to_yaml');
        $convert->setProjectKey($project->getKey());
        
        $teams = $convert->load($file,$sheet);
        
        echo sprintf("%s: %d\n",$sheet,count($teams));
        
        file_put_contents('data/Teams.yml',Yaml::dump($teams,10));
        
        $loader = $this->getService('cerad_game__load_teams');
        $loader->process($teams);
        
        $linker = $this->getService('cerad_game__link_teams');
        $linker->process($teams);
        
        return;
    }
}
?>
