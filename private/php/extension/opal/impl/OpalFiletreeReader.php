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
use DateTimeZone;
use DOMElement;
use Moose\Extension\Opal\OpalFileNodeInterface;
use Moose\Extension\Opal\OpalFiletreeReaderInterface;
use Moose\Log\Logger;
use Moose\Util\MonoPredicate as M;
use Requests_IRI;
use Symfony\Component\DomCrawler\Crawler;
use const MB_CASE_LOWER;
use function mb_convert_case;

/**
 * For reading the directory structure and files from OPAL.
 * The constructor is public, but no instance can be instantiated directly,
 * as it requires an OpalSession, whose constructor is private. Use the
 * factory method OpalSession::open to access.
 * 
 * The combined ID of the different node types is as follows:
 * <ul>
 *   <li>Catalog: "c@catalog_id", eg. "c@313084". Children are catalog or repository entry.</li>
 *   <li>Repository entry: "re@repository_entry_id", eg. "re@3928304431". Children are course node.</li>
 *   <li>Course node: "cn@repository_entry_id@course_node_id", eg "cn@3892943109@19329984398439". Children are directory or file.</li>
 *   <li>Directory: "d@repository_entry_id@course_node_id@directory_path, eg. "d@9898321431@87874587430799@Uebung/Zweite/2014". Children are directory or file.</li>
 *   <li>File: "f@repository_entry_id@course_node_id@file_path, eg. "d@9898321431@87874587430799@Uebung/Zweite/2014/test.pdf". No children</li>
 * </url>
 * @author madgaksha
 */
class OpalFiletreeReader implements OpalFiletreeReaderInterface {
    const TYPE_CATALOG = 'c';
    const TYPE_REPOSITORY_ENTRY = 're';
    const TYPE_COURSE_NODE = 'cn';
    const TYPE_DIRECTORY = 'd';
    const TYPE_FILE = 'f';

    const PATH_CATALOG = '/opal/auth/repository/catalog';
    const PATH_REPOSITORY_ENTRY = '/opal/auth/RepositoryEntry';
    const PATH_COURSE_NODE = '/opal/auth/RepositoryEntry';

    const URL_CATALOG = OpalSession::URL_OPAL . OpalFiletreeReader::PATH_CATALOG;
    const URL_REPOSITORY_ENTRY = OpalSession::URL_OPAL . OpalFiletreeReader::PATH_REPOSITORY_ENTRY;
    const URL_COURSE_NODE = OpalSession::URL_OPAL . OpalFiletreeReader::PATH_COURSE_NODE;

    const SELECTOR_CATALOG_LIST = 'form[action*=listPanelForm] .list-group-item';
    const SELECTOR_CATALOG_NAME = '.list-group-item-heading';
    const SELECTOR_CATALOG_DESCRIPTION = '.list-group-item-text';
    const SELECTOR_CATALOG_LINK = 'a.list-group-item-link';

    const SELECTOR_REPOSITORY_ENTRY_LIST = '.course-toc .course-toc-entry';
    const SELECTOR_REPOSITORY_ENTRY_LINK = '.course-toc-header a';
    const SELECTOR_REPOSITORY_ENTRY_DESCRIPTION = '.course-toc-content > div';
    const SELECTOR_REPOSITORY_ENTRY_SCRIPT = 'script:not([src])';

    const SELECTOR_COURSE_NODE_LIST = '.course-node tbody tr';
    const SELECTOR_COURSE_NODE_LINK = 'a';
    const SELECTOR_COURSE_NODE_NAME = 'span';
    const SELECTOR_COURSE_NODE_DIRECTORY = '.icon-folder';
    const SELECTOR_COURSE_NODE_SIZE = 'td:nth-child(4)';
    const SELECTOR_COURSE_NODE_DATE = 'td:nth-child(5)';
    
    const REGEX_CATALOG_LINK = '/\/(\w+)\/(\d+)\/?$/';
    const REGEX_REPOSITORY_ENTRY_LINK = '/\/CourseNode\/(\d+)\/?$/';
    const REGEX_REPOSITORY_ENTRY_LIST = '/initial_data\s*=\s*(\[.+?\])\s*;/i';
    const REGEX_COURSE_NODE_LINK = '/\/CourseNode\/.+\/(.+?)$/';
    const REGEX_FILESIZE = '/([\d,\.]+)\s*(bytes|K|M|G)/i';

    // 19.05.2017 um 10:45 Uhr
    // 19/05/2017 at 10:45 AM
    const DATE_FORMAT_FILE = [
        'd.m.Y \u\m G:i \U\h\r',
        'd/m/Y \a\t g:i a'
    ];
    
    const NAME_REPOSITORY_ENTRY = 'RepositoryEntry';
    const NAME_CATALOG = 'catalog';
    const NAME_COURSE_NODE = 'CourseNode';

    /** Currently Opal uses 1kB = 1024 bytes. */
    const UNIT_FILESIZE = 1024;
    
    private static $TIMEZONE;

    /** @var OpalSession */
    private $session;
    
    public function __construct(OpalSession $session) {
        $this->session = $session;
    }
    
    public function listChildrenById(string $id = null): array {
        return $this->session->tryAgainIfFailure(function() use ($id) {
            if (empty($id)) {
                $path = [];
                return $this->listEntries(self::URL_CATALOG, $path, self::SELECTOR_CATALOG_LIST, [$this, 'mapCatalog']);
            }
            return $this->internalList($id);
        });
    }

    /**
     * @param OpalFileNodeInterface|null $node
     * @return OpalFileNode[]
     */
    public function listChildren(OpalFileNodeInterface $node = null): array {
        return $this->session->tryAgainIfFailure(function() use ($node) {
            if ($node === null) {
                $path = [];
                return $this->listEntries(self::URL_CATALOG, $path, self::SELECTOR_CATALOG_LIST, [$this, 'mapCatalog']);
            }
            if (!$node->getIsDirectory()) {
                throw new OpalException('Can only list paths.');
            }
            return $this->internalList($node->getId());
        });
    }
    
    private function internalList(string $id) : array {
        $path = $this->deserializePath($id);
        switch ($path[0]) {
            case OpalFiletreeReader::TYPE_CATALOG:
                $url = self::URL_CATALOG . '/' . $path[1];
                return $this->listEntries($url, $path, self::SELECTOR_CATALOG_LIST, [$this, 'mapCatalog']);
            case OpalFiletreeReader::TYPE_REPOSITORY_ENTRY:
                $url = self::URL_REPOSITORY_ENTRY . '/' . $path[1];
                $this->session->getBot()->get($url);
                $this->session->getLogger()->debug($path, "Attempting listing for url $url");
                return $this->listRepositoryEntry($path);
            case OpalFiletreeReader::TYPE_COURSE_NODE:
                $url = self::URL_COURSE_NODE . '/' . $path[1] . '/' . self::NAME_COURSE_NODE . '/' . $path[2];
                return $this->listEntries($url, $path, self::SELECTOR_COURSE_NODE_LIST, [$this, 'mapCourseNodeOrDirectory']);
            case OpalFiletreeReader::TYPE_DIRECTORY:
                $url = self::URL_COURSE_NODE . '/' . $path[1] . '/' . self::NAME_COURSE_NODE . '/' . $path[2] . '/' . $path[3];
                return $this->listEntries($url, $path, self::SELECTOR_COURSE_NODE_LIST, [$this, 'mapCourseNodeOrDirectory']);
            case OpalFiletreeReader::TYPE_FILE:
                throw new OpalException('Cannot list file');
            default:
                throw new OpalException("Unknown node type: $path[0]");
        }
    }
    
    public function loadFileData(OpalFileNodeInterface $node) : OpalFileDataInterface {
        if ($node->getIsDirectory()) {
                throw new OpalException('Cannot fetch content of directory');
        }
        return $this->loadFileDataById($node->getId());
    }
    
    public function loadFileDataById(string $id) : OpalFileDataInterface {
        $path = $this->deserializePath($id);
        if ($path[0] !== OpalFiletreeReader::TYPE_FILE) {
            throw new OpalException('Can only fetch content of files');
        }
        $url = self::URL_COURSE_NODE . '/' . $path[1] . '/' . self::NAME_COURSE_NODE . '/' . $path[2] . '/' . $path[3];
        return $this->session->tryAgainIfFailure(function() use ($url) {
            $body = $this->session->getBot()
                    ->get($url)
                    ->assertResponseCode(M::equals(200))
                    ->getResponseBody();
            $mimeType = $this->session->getBot()->getResponseHeader('content-type');
            $contentDisposition = $this->session->getBot()->getResponseContentDisposition();
            return new OpalFileData($mimeType ?? 'application/octet-stream',
                    \strlen($body), $body, $contentDisposition['filename']);
        });
    }
    
    public function getSession() : OpalSession {
        return $this->session;
    }

    private function listEntries(string $url, array $path, string $selector, $mapper) : array {
        $this->session->getLogger()->log($path, "Attempting listing for url $url with selector $selector", Logger::LEVEL_DEBUG);
        return $this->session->getBot()
                ->get($url)
                ->selectMulti($selector, function(Crawler $nodes) use ($mapper, $path) {
                    $this->session->getLogger()->log($nodes->count(), 'Number of found listing items', Logger::LEVEL_DEBUG);
                    return \array_filter(\array_map(function(DOMElement $node) use ($mapper, $path) {
                        return \call_user_func($mapper, new Crawler($node), $path);
                    }, $nodes->getIterator()->getArrayCopy()));
                })
                ->getReturn() ?? [];
    }
    
    private function mapCatalog(Crawler $node, array $catalogPath) {
        $linkElement = $node->filter(self::SELECTOR_CATALOG_LINK);
        if ($linkElement->count() !== 1) {
            $this->session->getLogger()->log('Could not process catalog node, did not find link element', null, Logger::LEVEL_ERROR);
            return null;
        }
        $href = $linkElement->attr('href');
        if (empty($href)) {
            $this->session->getLogger()->log('Could not process catalog node, link element does not specify action', null, Logger::LEVEL_ERROR);
            return null;
        }
        $this->session->getLogger()->log($href, 'Found catalog entry with href', Logger::LEVEL_DEBUG);
        $iri = new Requests_IRI($href);
        // href="https://bildungsportal.sachsen.de/opal/auth/repository/catalog/11641749504"
        $matches = [];
        if (1 !== \preg_match(self::REGEX_CATALOG_LINK, $iri->ipath, $matches)) {
            $this->session->getLogger()->log($href, 'Could not process file node, link not recognized', Logger::LEVEL_ERROR);
            return null;
        }
        switch ($matches[1]) {
            case self::NAME_REPOSITORY_ENTRY:
                $type = OpalFiletreeReader::TYPE_REPOSITORY_ENTRY;
                break;
            case self::NAME_CATALOG:
                $type = OpalFiletreeReader::TYPE_CATALOG;
                break;
            default:
                $this->session->getLogger()->log($matches[1], 'Unrecognized catalog node type', Logger::LEVEL_ERROR);
                return null;
        }
        $nameElement = $linkElement->filter(self::SELECTOR_CATALOG_NAME);
        $descriptionElement = $linkElement->filter(self::SELECTOR_CATALOG_DESCRIPTION);
        $name = $nameElement->count() > 0 ? $nameElement->text() : 'n/a';
        $description = $descriptionElement->count() > 0 ? $descriptionElement->text() : 'n/a';
        return OpalFilePathNode::create($this,
                $this->serializePath($type, $matches[2]), $name, $description);
    }
    
    /**
     * @param Crawler $html
     * @param string[] $repositoryEntryPath
     * @return OpalFileNodeInterface
     */
    private function listRepositoryEntry(array $repositoryEntryPath) : array  {
        $result = [];
        $this->session->getBot()->selectMulti(self::SELECTOR_REPOSITORY_ENTRY_SCRIPT, function(Crawler $scripts) use ($repositoryEntryPath, & $result) {
            $this->session->getLogger()->debug($scripts->count(), 'Number of scripts found for repository entry');
            $scripts->each(function(Crawler $script) use ($repositoryEntryPath, & $result) {
                $matches = [];
                if (1 === \preg_match(self::REGEX_REPOSITORY_ENTRY_LIST, $script->text(), $matches)) {
                    $initialData = \json_decode($matches[1]);
                    if ($initialData === null) {
                        $this->session->getLogger()->log($matches[1], 'Could not process repository entry, initial data json invalid', Logger::LEVEL_ERROR);
                        return;
                    }
                    foreach ($initialData as $entry) {
                        $children = $entry->children ?? null;
                        if ($children === null) {
                            $this->session->getLogger()->log($entry, 'Could not process repository entry, data does not have children property', Logger::LEVEL_ERROR);
                            continue;
                        }
                        foreach ($children as $item) {
                            $data = $item->a_attr ?? null;
                            if ($data === null) {
                                $this->session->getLogger()->log($item, 'Could not process repository entry, item does not contain data', Logger::LEVEL_ERROR);
                                continue;
                            }
                            $href = $data->href ?? null;
                            if (empty($href)) {
                                $this->session->getLogger()->log('Could not process repository entry, link entry does not specify href', null, Logger::LEVEL_ERROR);
                                continue;
                            }                        
                            $this->session->getLogger()->log($href, 'Found repository entry with href', Logger::LEVEL_DEBUG);
                            $iri = new Requests_IRI($href);
                            if (1 !== \preg_match(self::REGEX_REPOSITORY_ENTRY_LINK, $iri->ipath, $matches)) {
                                $this->session->getLogger()->log($href, 'Could not process repository entry node, link not recognized', Logger::LEVEL_ERROR);
                                continue;
                            }

                            $name = \html_entity_decode($data->title ?? '');
                            $result []= OpalFilePathNode::create($this,
                                    $this->serializePath(OpalFiletreeReader::TYPE_COURSE_NODE,
                                            $repositoryEntryPath[1], $matches[1]),
                                    $name, 'n/a');
                        }
                    }
                }
            });
        });
        return $result;
    }
    
    private function mapCourseNodeOrDirectory(Crawler $node, array $courseNodePath) {
        $linkElement = $node->filter(self::SELECTOR_COURSE_NODE_LINK);
        if ($linkElement->count() !== 1) {
            $this->session->getLogger()->log('Could not process course node, did not find link element', null, Logger::LEVEL_ERROR);
        }
        $href = $linkElement->attr('href');
        if (empty($href)) {
            $this->session->getLogger()->log('Could not process course node, link element does not specify action', null, Logger::LEVEL_ERROR);
            return null;
        }
        $this->session->getLogger()->log($href, 'Found course node entry with href', Logger::LEVEL_DEBUG);
        $iri = new Requests_IRI($href);
        $matches = [];
        if (1 !== \preg_match(self::REGEX_COURSE_NODE_LINK, $iri->ipath, $matches)) {
            $this->session->getLogger()->log($href, 'Could not process course node, link not recognized', Logger::LEVEL_ERROR);
            return null;
        }
        $isDirectory = $node->filter(self::SELECTOR_COURSE_NODE_DIRECTORY)->count() > 0;
        $nameElement = $linkElement->filter(self::SELECTOR_COURSE_NODE_NAME);
        $sizeElement = $node->filter(self::SELECTOR_COURSE_NODE_SIZE);
        $dateElement = $node->filter(self::SELECTOR_COURSE_NODE_DATE);
        $name = $nameElement->count() > 0 ? $nameElement->text() : 'n/a';
        $size = $sizeElement->count() > 0 ? $this->getSize($sizeElement->text()) : 0;
        $date = $sizeElement->count() > 0 ? $this->getDate($dateElement->text()) : time();
        $nodeType = $isDirectory ? OpalFiletreeReader::TYPE_DIRECTORY : OpalFiletreeReader::TYPE_FILE;
        $filePath = isset($courseNodePath[3]) ? $courseNodePath[3] . '/' . $matches[1] : $matches[1];
        $nodePath = $this->serializePath($nodeType, $courseNodePath[1], $courseNodePath[2], $filePath);
        if ($isDirectory) {
            return OpalFilePathNode::create($this, $nodePath, $name, '');
        }
        else {
            return OpalFileNode::create($this, $nodePath, $name, 'n/a', $size, $date);
        }
    }
    
    private function serializePath() : string {
        return \implode('@', \func_get_args());
    }
    
    private function deserializePath(string $merged) : array {
        $typeRestArray = \explode('@', $merged, 2);
        if (\sizeof($typeRestArray) != 2) {
            $this->session->getLogger()->log($merged, "Node id does not contain a type", Logger::LEVEL_ERROR);
            throw new OpalException("Node id does not contain a type: $merged");
        }
        switch ($typeRestArray[0]) {
            case OpalFiletreeReader::TYPE_CATALOG:
                $expectedLength = 2;
                break;
            case OpalFiletreeReader::TYPE_REPOSITORY_ENTRY:
                $expectedLength = 2;
                break;
            case OpalFiletreeReader::TYPE_COURSE_NODE:
                $expectedLength = 3;
                break;
            case OpalFiletreeReader::TYPE_DIRECTORY:
            case OpalFiletreeReader::TYPE_FILE:
                $expectedLength = 4;
                break;
            default:
                $this->session->getLogger()->log($merged, 'Unknown node type', Logger::LEVEL_ERROR);
                throw new OpalException("Node type is not recognized: $merged");                
        }
        $array = \explode('@', $merged, $expectedLength);
        if (\sizeof($array) !== $expectedLength) {
            $this->session->getLogger()->log($merged, "Node path does not contain the expected number of entries", Logger::LEVEL_ERROR);
            throw new OpalException("Invalid node path $merged");
        }
        return $array;
    }
    
    private function getSize(string $size = null) : int {
        if (empty($size)) {
            return 0;
        }
        $matches = [];
        if (1 !== \preg_match(self::REGEX_FILESIZE, $size, $matches)) {
            $this->session->getLogger()->log($size, 'Unable to extract size', Logger::LEVEL_WARNING);
            return 0;
        }
        $base = \floatval($matches[1]);
        switch (mb_convert_case($matches[2], MB_CASE_LOWER)) {
            case 'bytes':
                return (int)$base;
            case 'k':
                return (int)($base*self::UNIT_FILESIZE);
            case 'm':
                return (int)($base*self::UNIT_FILESIZE*self::UNIT_FILESIZE);
            case 'g':
                return (int)($base*self::UNIT_FILESIZE*self::UNIT_FILESIZE*self::UNIT_FILESIZE);
            default:
                $this->session->getLogger()->log($matches[2], 'Unable to extract size, unrecognized suffix', Logger::LEVEL_WARNING);
                return 0;
        }
    }
    
    private function getDate(string $date = null) : DateTime {
        /* @var $dateTime DateTime */
        if (empty($date)) {
            return new DateTime();
        }
        $date = \trim($date);
        foreach (self::DATE_FORMAT_FILE as $format) {
            $dateTime = DateTime::createFromFormat($format, $date, self::$TIMEZONE ?? self::$TIMEZONE = new DateTimeZone('Europe/Berlin'));
            if ($dateTime !== false) {
                return $dateTime;
            }
        }
        $this->session->getLogger()->log($date, 'Unable to extract time, unrecognized format', Logger::LEVEL_WARNING);
        return new DateTime();
    }
}