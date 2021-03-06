<?php
namespace Cerad\Bundle\PersonBundle\ValidatorConstraint\AYSO;

use Symfony\Component\Validator\Constraint;

class VolunteerIdConstraint extends Constraint
{
    public $message = 'Must be 8-digits';
    public $pattern = '/^(AYSOV)?\d{8}$/';
    public $match   = true;
    
    public function validatedBy()
    {
        return 'Symfony\Component\Validator\Constraints\RegexValidator';
    }
}

?>
