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

use Doctrine\Common\Annotations\Annotation\Target;
use PhpCsFixer\DocBlock\Annotation;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 * @author madgaksha
 * @link http://json-schema.org/ JSON schema.
 */
class Json extends Constraint {
    const SYNTAX_INVALID_ERROR = 'b8a0f2b1-641c-46ac-8825-7f4497f509db';
    const SCHEMA_INVALID_ERROR = '40987cbb-b8af-4a58-b006-ba5f4abc965f';
    
    /** @var string */
    public $syntaxMessage = 'This is not a valid JSON string: {{ syntaxError }}';
    /** @var string */
    public $schemaMessage = 'This JSON string does not conform to the given schema: {{ schemaError }}';
    /** @var string */
    public $type = null;
    /** @var array */
    public $properties = null;
    /** @var array */
    public $patternProperties = null;
    /** @var bool */
    public $additionalProperties = null;
    /** @var array */
    public $required = null;
    /** @var array */
    public $definitions = null;
    
    public function __construct($options = null) {
        parent::__construct($options);
    }
}
