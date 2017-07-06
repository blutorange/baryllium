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

namespace Moose\Web;

use Requests_Hooker;

/**
 * Similar to Requests_Hooks, but actually respects the priority by sorting
 * the array.
 */
class HttpBotHooks implements Requests_Hooker {
	/**
	 * Registered callbacks for each hook
	 *
	 * @var array
	 */
	private $hooks = [];
    
    /**
     * Keeps track of the arrays we need to sort.
     * @var array
     */
    private $dirty = [];

    /**
	 * Constructor
	 */
	public function __construct() {
		// pass
	}

	/**
	 * Register a callback for a hook
	 *
	 * @param string $hook Hook name
	 * @param callback $callback Function/method to call on event
	 * @param int $priority Priority number. <0 is executed earlier, >0 is executed later
	 */
	public function register($hook, $callback, $priority = 0) {
		if (!isset($this->hooks[$hook])) {
			$this->hooks[$hook] = [];
		}
		if (!isset($this->hooks[$hook][$priority])) {
			$this->hooks[$hook][$priority] = [];
		}
		$this->hooks[$hook][$priority][] = $callback;
        $this->dirty[$hook] = true;
	}

	/**
	 * Dispatch a message
	 *
	 * @param string $hook Hook name
	 * @param array $parameters Parameters to pass to callbacks
	 * @return boolean Successfulness
	 */
	public function dispatch($hook, $parameters = array()) {
		if (empty($this->hooks[$hook])) {
			return false;
		}
        if ($this->dirty[$hook]) {
            \ksort($this->hooks[$hook]);
            unset($this->dirty[$hook]);
            
        }
		foreach ($this->hooks[$hook] as $priority => $hooked) {
			foreach ($hooked as $callback) {
				call_user_func_array($callback, $parameters);
			}
		}
		return true;
	}
    
    public function clearHooks(string $hook = null) {
        if ($hook === null) {
            $this->hooks = [];
            $this->dirty = [];
        }
        else {
            unset($this->hooks[$hook]);
            unset($this->dirty[$hook]);
        }
    }
}