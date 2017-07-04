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

namespace Moose\Extension\Opal;
use Doctrine\DBAL\Types\ProtectedString;

/**
 * <p>
 * The main interface for an OPAL session. Provides methods to access
 * various functionalities, such as retrieving folders and file, retrieving
 * text templates, or reading and changing the user profile.
 * </p>
 * <p>
 * However, since we most likely only need and want to access directories and
 * files, only this is implemented for now. It doesn't really work anyway.
 * </p>
 * @author madgaksha
 */
interface OpalSessionInterface {
    public function getFiletreeReader() : OpalFiletreeReaderInterface;
    /**
     * @return Whether this session is still valid, ie. whether we are still
     * authenticated.
     */
    public function isValid() : bool;
    /**
     * This method should not perform any encryption, this is handled by the
     * caller.
     * @return ProtectedString A string with all the required data for restoring the
     * session, such as JSESSIONID etc.
     */
    public function serializeSession() : ProtectedString ;
    /**
     * Restores a session.
     * @param ProtectedString $serializedData As returned by by serializeSession.
     * @return OpalSessionInterface The restored session.
     */
    public static function fromSerialized(ProtectedString $serializedData);
}