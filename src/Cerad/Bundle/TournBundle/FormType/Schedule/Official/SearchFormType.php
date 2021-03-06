<?php
namespace Cerad\Bundle\TournBundle\FormType\Schedule\Official;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchFormType extends AbstractType
{
    public function getName() { return 'cerad_schedule_official_search'; }

    public function __construct() { }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* ============================================
         * Maintain as an example
         */
        $subscriber = new SearchSubscriber($builder->getFormFactory());
        $builder->addEventSubscriber($subscriber);
        
        // The filters
        $builder->add('numFilter',     'text', array('required' => false, 'attr' => array('size' => 30)));
        $builder->add('teamFilter',    'text', array('required' => false, 'attr' => array('size' => 30)));
        $builder->add('officialFilter','text', array('required' => false, 'attr' => array('size' => 30)));
        
        return;
        
        // Multi select lists for my stuff
        // This will need to be moved to the subscriber to get the real teams
        $builder->add('myTeams', 'choice', array(
            'label'         => 'My Teams',
            'empty_value'   => 'My Teams',
            'required'      => false,
            'choices'       => array(1 => 'Team 1', 2 => 'Team 2', 3 => 'Team 3'),
            'multiple'      => true,  // No empty value
            'expanded'      => false,
        ));     
        $builder->add('myPersons', 'choice', array(
            'label'         => 'My Persons',
            'empty_value'   => 'My Persons',
            'required'      => false,
            'choices'       => array(1 => 'Person 1', 2 => 'Person 2', 3 => 'Person 3'),
            'multiple'      => true,  // No empty value
            'expanded'      => false,
        ));  
       
        // Time range
        $builder->add('time1', 'choice', array(
            'label'         => 'Time 1',
            'required'      => false,
            'choices'       => $this->times,
            'expanded'      => false,
            'multiple'      => false,
            'empty_value'   => 'After',
        ));
        $builder->add('time2', 'choice', array(
            'label'         => 'Time 2',
            'required'      => false,
            'choices'       => $this->times,
            'expanded'      => false,
            'multiple'      => false,
            'empty_value'   => 'Before'
        ));
        $builder->add('sortBy', 'choice', array(
            'label'         => 'Sort By',
            'required'      => false,
            'choices'       => $this->sortBys,
            'expanded'      => false,
            'multiple'      => false,
            'empty_value'   => 'DOW,Time,Field'
        ));
    }
    // Hack this in, should be from project file
    protected $times = array(
        '0600' => '06 AM', '0700' => '07 AM', '0800' => '08 AM', '0900' => '09 AM',
        '1000' => '10 AM', '1100' => '11 AM', '1200' => '12 PM', '1300' => '01 PM',
        '1400' => '02 PM', '1500' => '03 PM', '1600' => '04 PM', '1700' => '05 PM',
        '1800' => '06 PM', '1900' => '07 PM', '2000' => '08 PM', '2100' => '09 PM',
    );
    protected $sortBys = array(
        1 => 'Game Number',
        2 => 'Venue,Field',
        3 => 'Etc'
    );
}
?>
