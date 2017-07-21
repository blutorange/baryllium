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

namespace Moose\FormModel;

use Moose\Util\PlaceholderTranslator;
use Moose\Util\UiUtil;
use Moose\ViewModel\Message;
use Moose\Web\HttpRequest;
use Moose\Web\HttpRequestInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Description of AbstractFormModel
 *
 * @author madgaksha
 */
abstract class AbstractFormModel {

    private static $VALIDATOR;
    
    /** @var PlaceholderTranslator */
    protected $translator;
    
    /** @var HttpRequestInterface */
    protected $request;
    
    /** @var array */
    private $fieldNameMap;

    protected function __construct(HttpRequestInterface $request,
            PlaceholderTranslator $translator, array $fields,
            int $fromWhere = HttpRequest::PARAM_FORM) {
        $this->translator = $translator;
        $this->request = $request;
        $this->fieldNameMap = [];
        if ($fromWhere !== null) {
            $this->setFromRequest($fields, $fromWhere);
        }
        else {
            $this->fillFieldNameMap($fields);
        }
    }
    
    public final function validate() : array {
        $groups = $this->getGroups();
        $groups []= 'Default';
        $violations = self::getValidator($this->translator)->validate($this,
                null, $groups);
        $messages = [];
        foreach ($violations as $violation) {
            /* @var $violation ConstraintViolationInterface */
            $messages []= Message::danger($this->translator->gettext('error.validation'), $violation->getMessage());
        }
        return $messages;
    }
    
    private static function getValidator(PlaceholderTranslator $translator) : ValidatorInterface {
        if (self::$VALIDATOR === null) {
            self::$VALIDATOR = Validation::createValidatorBuilder()
                    ->enableAnnotationMapping()
                    ->setTranslationDomain("validation")
                    ->setTranslator($translator)
                    ->getValidator();
        }
        return self::$VALIDATOR;
    }
    
    /**
     * <pre>
     * $this->setFromRequest([
     *     // Sets the object's field mailAddress
     *     'mailAddress' => [
     *         'name' => 'mail', // Name of the form element
     *         'default' => 'mail@example.com' // Default value
     *     ],
     *     'doSendMail' => [
     *         'name' => 'do_send',
     *         'default' => true,
     *         'type' => 'bool' // Either '', 'bool', or 'int'
     *     ]
     * ])
     * </pre>
     * <pre>
     * $this->setFromRequest([
     *     'mailAddress' => ['mail', 'mail@example.com']
     * ])
     * </pre>
     * <pre>
     * $this->setFromRequest([
     *     'mailAddress' => 'mail' // Default value is null
     * ])
     * </pre>
     * @param array $fields
     */
    private function setFromRequest(array $fields, int $fromWhere) {
        foreach ($fields as $fieldName => $options) {
            if (\is_array($options)) {
                $formName = $options[0] ?? $options['name'];
                $defaultValue = isset($options[1]) ? $options[1] : ($options['default'] ?? null); 
                $type = isset($options[2]) ? $options[2] : ($options['type'] ?? '');
            }
            else {
                $formName = (string)$options;
                $defaultValue = null;
                $type = '';
            }
            if ($type instanceof ParamConverterInterface) {
                $value = $this->request->getParam($formName, null, $fromWhere);
                if ($value === null) {
                    $value = $type->getDefault($defaultValue);
                }
                else {
                    $value = $type->convert($value);
                }
            }
            else {
                $type = UiUtil::firstToUpcase($type ?? '');                
                $getter = "getParam$type";
                $value = $this->request->$getter($formName, $defaultValue, $fromWhere);
            }
            $setter = 'set' . UiUtil::firstToUpcase($fieldName);            
            $this->$setter($value);
            $this->fieldNameMap[$fieldName] = $formName;            
        }
    }
    
    private function fillFieldNameMap(array $fields) {
        foreach ($fields as $fieldName => $options) {
            if (\is_array($options)) {
                $formName = $options[0] ?? $options['name'];
            }
            else {
                $formName = (string)$options;
            }
            $this->fieldNameMap[$fieldName] = $formName;
        }
    }
    
    public function getAll() {
        $all = [];
        foreach ($this->fieldNameMap as $fieldName => $formName) {
            $getter = 'get' . UiUtil::firstToUpcase($fieldName);
            $all[$formName] = $this->$getter();
        }
        return $all;
    }

    protected abstract function getGroups() : array;
}
