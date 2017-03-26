<?php

/* Note: This license has also been called the "New BSD License" or "Modified
 * BSD License". See also the 2-clause BSD License.
 * 
 * Copyright 2015 The Moose Team
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 * 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 * 
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Symfony\Component\Validator\Constraints;

use JsonSchema\Validator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author madgaksha
 */
class JsonValidator extends ConstraintValidator {
    public function validate($value, Constraint $constraint) {
        if (!$constraint instanceof Json) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\JsonValidator');
        }
        if (null === $value || '' === $value) {
            return;
        }
        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $stringValue = (string) $value;
        
        $json = $this->validateSyntax($stringValue, $constraint);
        if ($json === null) {
            return;
        }
        
        if (!$this->validateSchema($json, $constraint)) {
            return;
        }
    }
    
    private function buildSchema(Json $jsonConstraint) {
        $schema = [];
        if ($jsonConstraint->type !== null) {
            $schema['type'] = $jsonConstraint->type;
        }
        if ($jsonConstraint->properties !== null) {
            $schema['properties'] = $jsonConstraint->properties;
        }
        if ($jsonConstraint->patternProperties !== null) {
            $schema['patternProperties'] = $jsonConstraint->patternProperties;
        }
        if ($jsonConstraint->additionalProperties !== null) {
            $schema['additionalProperties'] = $jsonConstraint->additionalProperties;
        }
        if ($jsonConstraint->required !== null) {
            $schema['required'] = $jsonConstraint->required;
        }
        if ($jsonConstraint->definitions !== null) {
            $schema['definitions'] = $jsonConstraint->definitions;
        }
        return $schema;
    }

    private function validateSchema(& $json, Json $jsonConstraint) : bool {
        $schema = $this->buildSchema($jsonConstraint);
        if ($schema !== null && sizeof($schema) > 0) {
            $validator = new Validator();
            $validator->validate($json, $schema);
            if (!$validator->isValid()) {
                $errors = $validator->getErrors();
                $this->context->buildViolation($jsonConstraint->schemaMessage)
                    ->setParameter('{{ schemaError }}', $this->formatValue($errors[0]['message']))
                    ->setInvalidValue($json)
                    ->setCode(Json::SCHEMA_INVALID_ERROR)
                    ->addViolation();
                return false;
            }
        }
        return true;
    }

    private function validateSyntax(string & $stringValue, Json $jsonConstraint) {
        $json = json_decode($stringValue);
        if (json_last_error() !== JSON_ERROR_NONE || $json === null) {
            $this->context->buildViolation($jsonConstraint->syntaxMessage)
                ->setParameter('{{ syntaxError }}', $this->formatValue(json_last_error_msg()))
                ->setInvalidValue($stringValue)
                ->setCode(Json::SYNTAX_INVALID_ERROR)
                ->addViolation();
            return null;
        }
        return $json;
    }
}