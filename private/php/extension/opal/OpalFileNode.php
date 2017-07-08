<?php
declare(strict_types = 1);

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

use DateTime;
use Dflydev\ApacheMimeTypes\PhpRepository;

/**
 * Description of OpalFileNode
 *
 * @author madgaksha
 */
class OpalFileNode implements OpalFileNodeInterface {
       
    private $filetreeReader;
    private $id;
    /** @var DateTime */
    private $modificationDate;
    private $byteSize;
    private $data;
    private $name;
    private $description;
    private $mimeType;

    private function __construct(OpalFiletreeReader $filetreeReader) {
        $this->filetreeReader = $filetreeReader;
        $this->modificationDate = time();
    }
    
    public function getByteSize(): int {
        return $this->byteSize;
    }

    /*
    [2017-07-08 18:14:01 Europe/Berlin] DEBUG - Moose\Web\HttpBot->logResponse(439): <<< HTTP/1.1 200 OK
  date: Sat, 08 Jul 2017 16:14:01 GMT
  server: Apache
  strict-transport-security: max-age=31536000; includeSubDomains
  last-modified: Fri, 19 May 2017 12:02:22 GMT
  expires: Sun, 08 Jul 2018 16:14:01 GMT
  cache-control: private,max-age=31536000
  pragma: cache
  content-disposition: attachment; filename="Klausurschwerpunkte.txt"; filename*=UTF-8''Klausurschwerpunkte.txt
  content-length: 718
  vary: User-Agent
  content-type: text/plain; charset=UTF-8
Cookie JAR:
  JSESSIONID: 50B169A6CDC57F2C77250CFF8C9BE352.opalN7 @ bildungsportal.sachsen.de /opal/  (max-age: 0, expires: 0) [http-only,secure]
  idpsite-presel: BA+Dresden @ bildungsportal.sachsen.de /opal  (max-age: 1508170426, expires: 1508170426) []
  _shibsession_61707064666e68747470733a2f2f62696c64756e6773706f7274616c2e7361636873656e2e6465: _04014922a2905d27883bd48bc4f69111 @ bildungsportal.sachsen.de /  (max-age: 0, expires: 0) [http-only]
  authenticated-marker: Shib @ bildungsportal.sachsen.de /opal  (max-age: 1499789631, expires: 1499789630) []
  JSESSIONID: 4E700ED52A6C3F4E01C11D2DF1CE77B4 @ idp.ba-dresden.de /idp  (max-age: 0, expires: 0) [secure]
  _idp_authn_lc_key: 15b18371-d97c-423f-b8df-44ad98084dbe @ idp.ba-dresden.de /idp  (max-age: 0, expires: 10) []
  _idp_session: NzkuMjUxLjE4OC43Mw%3D%3D%7CYzBkYjBhMmQ2MjNmOTk1NWJiYzY1MjU0OGJmMTFlNTgyZGVlMTRiMjZkYjMwNmU2NzdiYmMyZDcxOTIxYTYxMQ%3D%3D%7Cg90MDOhsZcfu2dW2KwxsRexKi%2Bc%3D @ idp.ba-dresden.de /idp  (max-age: 0, expires: 0) [secure]
*/
    public function getData() {
        if ($this->data === null) {
            $this->filetreeReader->loadFile($this);
            $bot = $this->filetreeReader->getSession()->getBot();
            $this->data = $bot->getResponseBody();
            $this->byteSize =\strlen($this->data);
            $this->mimeType = $bot->getResponseHeader('content-type') ?? $this->mimeType;
        }
        return $this->data;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getModificationDate(): DateTime {
        return $this->getModificationDate();
    }

    public function getName(): string {
        return $this->name;
    }

    public static function create(OpalFiletreeReader $filetreeReader,
            string $id, string $name, string $description, int $size,
            DateTime $date) : OpalFileNodeInterface {
        $node = new OpalFileNode($filetreeReader);
        $node->id = $id;
        $node->name = $name;
        $node->description = $description;
        $node->byteSize = $size;
        $node->modificationDate = $date;
        $node->guessMime();
        return $node;
    }
    
    public function isDirectory(): bool {
        return false;
    }

    public function listChildren(): array {
        throw new OpalException('Cannot list file.');
    }
    
    public function getDescription(): string {
        return $this->description;
    }

    public function getMimeType(): string {
        return $this->mimeType;
    }

    private function guessMime() {
        $matches = [];
        if (1 === \preg_match('/\.(\w+)$/', $this->name, $matches)) {
            $this->mimeType = (new PhpRepository())->findType($matches[1]);
        }
        if ($this->mimeType === null) {
            $this->mimeType = 'application/octet-stream';
        }        
    }

    public function __toString(): string {
        $date = $this->modificationDate->format(DateTime::W3C);
        return "OpalFile($this->id,$this->name,$this->mimeType,$this->byteSize,$date)";
    }
}