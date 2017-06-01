<?php
/**
 * Created by PhpStorm.
 * User: sebby
 * Date: 01/06/17
 * Time: 18:51
 */

namespace OC\PlatformBundle\Validator;

use Symfony\Component\Validator\Constraint;
/**
 * @Annotation
 */
class AntiFlood extends Constraint
{
    public $message = "Vous ne pouvez pas postez deux annonces dans un délais de 15 sec";

    public function validatedBy() {
        return 'oc_platform_antiflood';
    }

}