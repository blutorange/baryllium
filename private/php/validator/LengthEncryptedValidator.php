<?php

namespace AppBundle\Validator\Constraints;

/**
 * @author madgaksha
 */
class LengthEncryptedValidator extends \Symfony\Component\Validator\Constraints\LengthValidator {
    public function validate($value, Constraint $constraint) {
        $value = ($value instanceof \Doctrine\DBAL\Types\ProtectedString) ? $value->getString() : $value;
        try {
            parent::validate($value, $constraint);
        }
        catch (Throwable $e) {
            $class = get_class($e);
            throw new $class($e->getMessage());
        }
    }
}
