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

/**
 * Description of OpalFileNode
 *
 * @author madgaksha
 */
class OpalFilePathNode implements OpalFileNodeInterface {
   
    private $filetreeReader;
    private $id;
    private $modificationDate;
    private $name = '';
    private $description = '';
    
    private function __construct(OpalFiletreeReader $filetreeReader) {
        $this->filetreeReader = $filetreeReader;
    }
    
    public function getByteSize(): int {
        return 0;
    }

    public function getData() {
        return '';
    }

    public function getId(): string {
        return $this->id;
    }

    public function getModificationDate(): DateTime {
        return $this->modificationDate;
    }

    public function getName(): string {
        return $this->name;
    }

    public static function create(OpalFiletreeReader $filetreeReader,
            string $id, string $name, string $description = '',
            DateTime $modificationDate = null) : OpalFileNodeInterface {
        $node = new OpalFilePathNode($filetreeReader);
        $node->id = $id;
        $node->name = $name;
        $node->description = $description;
        $node->modificationDate = $modificationDate ?? new DateTime();
        return $node;
    }

    public function isDirectory(): bool {
        return true;
    }
    
    public function listChildren(): array {
        return $this->filetreeReader->listChildren($this);
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getMimeType(): string {
        return 'inode/directory';
    }
}
