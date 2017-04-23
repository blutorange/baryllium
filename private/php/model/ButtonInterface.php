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

namespace Moose\ViewModel;

/**
 * A button, used for dialog etc.
 *
 * @author madgaksha
 */
interface ButtonInterface {

    const TYPE_DEFAULT = 0;
    const TYPE_PRIMARY = 1;
    const TYPE_SUCCESS = 2;
    const TYPE_INFO = 3;
    const TYPE_WARNING = 4;
    const TYPE_DANGER = 5;
    const TYPE_LINK = 6;
   
    public function __toString();
    
    /**
     * @return string A unique ID for this type of button. There may be several
     * buttons of the same type with the same ID, but possibly different data.
     */
    public function getId() : string;
    
    /**
     * @return string A unique ID that may be given when there are multiple
     * copies of this button on a page.
     */
    public function getPartialId();
    
    /**
     * @return string Null when there is no label.
     */
    public function getLabel();
    
    /**
     * @return string Null when there is no title.
     */
    public function getTitle();
    
    /**
     * @return string Null or empty when there is no glyphicon.
     */
    public function getGlyphicon();

    public function getBootstrapClass() : string;
    
    public function getType() : int;
    
    /**
     * @return string Whether a client-side callback function is called when the
     * button is clicked. The JavaScript function must be defined in
     * <code>window.Moose.Buttons</code>.
     */
    public function hasCallbackOnClick() : bool;
    
    /**
     * @return array Additional HTML attributes to render. Usually <code>data-</code>
     * attributes.
     */
    public function getHtmlAttributes() : array;
    
    /**
     * @return string Additional HTML classes to render.
     */
    public function getHtmlClasses() : string;
      
    /**
     * @return array An associative array with stringifyable data passed to to
     * the callback function.
     */
    public function getCallbackOnClickData() : array;
    
    /**
     * @return string The link opened when this button is clicked. Null when
     * this button does not redirect to another page.
     */
    public function getLink();
}