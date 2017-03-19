<?php

/* The 3-Clause BSD License
 * 
 * SPDX short identifier: BSD-3-Clause
 *
 * Note: This license has also been called the "New BSD License" or "Modified
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

namespace ViewModel;

use ReflectionCache;
use ReflectionProperty;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;
use Ui\Message;
use Ui\PlaceholderTranslator;
use View\FormFieldViewInterface;
use ViewModel\FormAnnotation\References;

abstract class AbstractFormModel implements FormModelInterface {
    
    /**
     * @var FormFieldModel[]
     */
    private $fields;
    
    private $referenceMap = [];
    
    public function __construct() {
        $this->initialize();
    }
    
    public function getFormFields() : array {
        if ($this->fields === null) {
            $this->initialize();
        }
        return $this->fields;
    }

    public function initialize() {
        $fields = [];
        $properties = ReflectionCache::getProperties(get_class($this));
        foreach ($properties as $property) {
            $field = $this->processAnnotations($property);
            if ($field !== null) {
               array_push($fields, $field);
            }
        }
        $this->fields = $fields;
    }

    /**
     * @param ReflectionProperty $property
     * @return FormFieldModel
     */
    public function processAnnotations(ReflectionProperty $property) {
        $constraints = [];
        $annotations = ReflectionCache::getPropertyAnnoationsFor($property);
        $view = null;
        $type = null;
        $referenceClass;
        $referenceRp;
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Constraint) {
                $constraints[get_class($annotation)] = $annotation;
                if ($annotation instanceof Type) {
                    $type = $annotation->type;
                }
            }
            else if ($annotation instanceof References) {
                $rp = $annotation->getReflectionProperty();
                $referenceClass = $rp->getDeclaringClass()->getName();
                $referenceRp = $rp;
                $this->referencedConstraints($rp, $constraints);
            }
            else if ($annotation instanceof FormFieldViewInterface) {
                $view = $annotation;
            }
        }
        if ($view === null) {
            return null;
        }
        $field = new FormFieldModel($this, $view, $property, $constraints, $type);
        if ($referenceClass !== null && $referenceRp !== null) {
            if (!array_key_exists($referenceClass, $this->referenceMap)) {
                $this->referenceMap[$referenceClass] = [];
            }
            array_push($this->referenceMap[$referenceClass], [$rp, $field]);
        }
        $view->bindModel($field);
        return $field;
    }

    public function referencedConstraints(ReflectionProperty $rp, array & $constraints) {
        $annotations = ReflectionCache::getPropertyAnnoationsFor($rp);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Constraint) {
                $constraints[get_class($annotation)] = $annotation;
            }
            else if ($annotation instanceof References) {
                $refs = $annotation->getPropertyAnnotations();
                $this->referencedConstraints($refs, $constraints);
            }
        }
    }
    
    public function processPost(array & $data, PlaceholderTranslator $translator, array & $messages) : bool {
        $validator = Validation::createValidatorBuilder()->setTranslator($translator)->getValidator();
        foreach ($this->getFormFields() as $field) {
            if (array_key_exists($field->getName(), $data)) {
                $value = $data[$field->getName()];
                $field->setFromForm($value);
            }
            else {
                $field->setValue(null);
            }
            $violations = $validator->validate($field->getValue(), $field->getConstraints());
            if ($violations->count() !== 0) {
                foreach ($violations as $violation) {
                    \array_push($messages, Message::danger('error.validation', $violation->getMessage()));
                }
            }            
        }
        return sizeof($violations) === 0;
    }
    
    private function getReferenceMap() {
        var_dump("getgetget",$this->fields);
        $this->getFormFields();
        return $this->referenceMap;
    }


    public function getClassInstance(string $class) {
        $refs = @$this->getReferenceMap()[$class];
        $instance = new $class();
        if ($refs === null) {
            return $instance;
        }
        foreach ($refs as $ref) {
            $ref[0]->setValue($instance, $ref[1]->getValue());
        }
        return $instance;
    }
}