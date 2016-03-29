<?php
namespace Cerad\Bundle\TournBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

/* =============================================================
 * Read in an accounts.yml file and load the accounts tables
 * Try to minimize processing as much as possible
 */
class TwigCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName       ('twig:paths')
            ->setDescription('Register')
            ->addArgument   ('username', InputArgument::OPTIONAL, 'username')
       ;
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
      //$env = $this->getService('twig');
      //$loader = $env->getLoader();
        
        $loader = $this->getService('twig.loader');
        $namespaces = $loader->getNamespaces();
        foreach($namespaces as $namespace)
        {
            $paths = $loader->getPaths($namespace);
            foreach($paths as $path)
            {
              //if (substr($namespace,0,5) == 'Cerad') echo sprintf("NS %-12s %s\n",$namespace,$path);
                echo sprintf("NS %-12s %s\n",$namespace,$path);
            }
        }
        return;
        $paths = $loader->getPaths();
        foreach($paths as $path)
        {
            echo $path . "\n";
        }
        $exists = $loader->exists('@CeradTourn/Tourn/Welcome/TournWelcomeInde.html.twig');
        if ($exists) echo "Found it\n";
        else         echo "Not found\n";
    }   
    protected function executex(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        if (!$username) $username = 'ahundiak01';
        $usernum = '01';
        
        /* ==================================
         * Delete any existing user
         */
        $userManager = $this->getService('cerad_account.user_manager');
        $user = $userManager->findUserByUsernameOrEmail($username);
        
        if ($user)
        {
            $personId = $user->getPersonId();
            $userManager->deleteUser($user);
            $user = null;
        }
        /* ======================
         * Create user
         */
        $user = $userManager->createUser();
        $user->setUsername($username);
        $user->setEmail   ($username . '@gmail.com');
        $user->setName('Art Hundiak');
        $user->setEnabled(true);
        $user->setPlainPassword('zzz');
           
        $userManager->updateUser($user);
                
        /* ============================================
         * Remove any existing person
         */
        $personRepo = $this->getService('cerad_person.repository');
        
        if ($personId)
        {
            $person = $personRepo->find($personId);
            if ($person)
            {
                $personRepo->remove($person);
                $personRepo->flush();
                $personRepo->clear();
                $person = null;
            }
            $personId = null;
        }
        
        /* ===============================
         * Create a new person
         */
        $person = $personRepo->newPerson();
        
        $person->setName     ('Art Hundiak');
        $person->setFirstName('Arthur');
        $person->setLastName ('Hundiak');
        $person->setNickName ('Hondo');
        
        $person->setEmail('ahundiak@gmail.com');
        $person->setPhone('2564575943');
        $person->setCity ('Huntsville');
        $person->setState('AL');
        $person->setGender('M');
        $person->setDob   (new \DateTime('1958-06-05'));
        
        $person->getPersonPersonPrimary();
        
        // USSF Info
        $personFed = $person->getFedUSSFC();
        $personFed->setId('USSFC12341234123400' . $usernum);
        
        $cert = $personFed->getCertReferee();
        $cert->setBadgex   ('Grade_8');
                
        $org = $personFed->getOrgState();
        $org->setOrgId('USSFS_AL');
        
        // AYSI Info
        $personFed = $person->getFedAYSOV();
        $personFed->setId('AYSOV123400' . $usernum);
        
        $cert = $personFed->getCertReferee();
        $cert->setBadgex   ('Advanced');
                
        $org = $personFed->getOrgRegion();
        $org->setOrgId('AYSOR0894');
        
        $personRepo->persist($person);
        $personRepo->flush();
        
        /* =================================
         * Link account to person
         */
        $user->setPersonId($person->getId());
        $userManager->updateUser($user);
    }
}
?>
