<?php

namespace Bayeux\Api;

// ========================================================================
// Copyright 2007 Mort Bay Consulting Pty. Ltd.
// ------------------------------------------------------------------------
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
// http://www.apache.org/licenses/LICENSE-2.0
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.
//========================================================================

/**
 * Holder of a channel ID broken into path segments
 */
class ChannelId
{
    const WILD = "*";
    const DEEPWILD = "**";

    private $_name;
    public $_segments = array();
    private $_wild;
    private $_wilds = array();
    private $_parent;

    public function __construct($name)
 {
        if (! is_string($name)) {
            // FIXME: correção
            throw new \Exception('Alterar para a exception correta de argumetno');
        }

        $this->_name = $name;

        if ($name == null || strlen($name) == 0 || $name[0] != '/' || '/' == $name) {
            throw new \InvalidArgumentException($name);
        }

        $wilds = array();

        $name = trim($name, '/');
        $this->_segments = explode('/', $name);

        $b = '/';
        $countSegments = $this->depth();
        for ($i=0; $i<$countSegments; $i++) {

            if (empty($this->_segments[$i])) {
                throw new \InvalidArgumentException($name);
            }
            if ($i > 0) {
                $b .= "{$this->_segments[$i-1]}/";
            }
            $wilds[$countSegments-$i] = $b . '**';
        }
        $wilds[0] = "{$b}*";
        $this->_parent = count($this->_segments) == 1 ? null : rtrim($b, '/');

        if ($countSegments == 0) {
            $this->_wild = 0;

        } else if (self::WILD == $this->_segments[$countSegments - 1]) {
            $this->_wild = 1;

        } else if (self::DEEPWILD == $this->_segments[$countSegments - 1]) { // FIXME: talves de problema aqui
            $this->_wild = 2;

        } else {
            $this->_wild = 0;
        }

        if ($this->_wild == 0) {
            $this->_wilds = $wilds;
        } else {
            $this->_wilds = array();
        }
    }

    public function isWild()
    {
        return $this->_wild > 0;
    }

    public function isDeepWild()
    {
        return $this->_wild > 1;
    }

    public static function staticIsMeta($channelId) {
        return self::_staticTypeSegment('meta', $channelId);
    }

    public static function staticIsService($channelId) {
        return self::_staticTypeSegment('service', $channelId);
    }

    private static function _staticTypeSegment($segment, $channelId) {
        return $channelId != '' && stripos($channelId, "/{$segment}/") === 0;
    }

    public function isMeta()
    {
        return $this->_typeSegment('meta');
    }

    public function isService()
    {
        return $this->_typeSegment('service');
    }

    private function _typeSegment($segment) {
        return count($this->_segments) > 0 && $segment == $this->_segments[0];
    }

    //@Override
    // FIXME: otimizar esse metodo pode removelo  ou alterar a forma de comparação retirado o for e pode coloar array() === array()
    public function equals($obj)
    {
        if ($this === $obj) {
            return true;
        }

        if ($obj instanceof ChannelId)
        {
            $id = $obj;
            if ($id->depth() == $this->depth())
            {
                for ($i = $id->depth(); $i-- > 0;) {
                    if (! ($id->getSegment($i) == $this->getSegment($i))) {
                        return false;
                    }
                }
                return true;
            }
        }

        return false;
    }

    /* ------------------------------------------------------------ */
    /** Match channel IDs with wildcard support
     * @param name
     * @return true if this channelID matches the passed channel ID. If this channel is wild, then matching is wild.
     * If the passed channel is wild, then it is the same as an equals call.
     */
    public function matches(ChannelId $name)
    {
        if ($name->isWild()) {
            return $this->equals($name);
        }

        switch($this->_wild)
        {
            case 0:
                return $this->equals($name);
            case 1:
                if ($name->depth() != $this->depth()) {
                    return false;
                }
                for ($i=$this->depth() - 1; $i-- > 0;) {
                    if (!$this->getSegment($i) == $name->getSegment($i)) {
                        return false;
                    }
                }
                return true;

            case 2:
                if ($name->depth() < $this->depth()) {
                    return false;
                }
                for ($i=count($this->_segments) - 1; $i-- > 0;) {
                    if (! ($this->getSegment($i) == $name->getSegment($i))) {
                        return false;
                    }
                }
                return true;
        }
        return false;
    }

    //@Override
    public function hashCode()
    {
        return $this->_name.hashCode();
    }

    //@Override
    public function toString()
    {
        return $this->_name;
    }

    public function depth()
    {
        return count($this->_segments);
    }

    /* ------------------------------------------------------------ */
    public function isAncestorOf(ChannelId $id)
    {
        if ($this->isWild() || $this->depth() >= $id->depth()) {
            return false;
        }

        for ($i = $this->depth(); $i-- > 0;)
        {
            if (! ($this->getSegment($i) == $id->getSegment($i))) {
                return false;
            }
        }
        return true;
    }

    /* ------------------------------------------------------------ */
    public function isParentOf(ChannelId $id)
    {
        if ($this->isWild() || $this->depth() != $id->depth()-1) {
            return false;
        }

        for ($i = $this->depth(); $i-- > 0; ) {
            if (! ($this->getSegment($i) == $id->getSegment($i))) {
                return false;
            }
        }

        return true;
    }

    /* ------------------------------------------------------------ */
    public function getParent()
    {
        return $this->_parent;
    }

    /* ------------------------------------------------------------ */
    // FIXME: utilizar o empty ver ou issset ou cont ver qual tem melhor desempenho
    public function getSegment($i)
    {
        if ($i > $this->depth()) {
            return null;
        }
        if (isset($this->_segments[$i])) {
            return $this->_segments[$i];
        }
        return null;
    }

    /* ------------------------------------------------------------ */
    /**
     * @return The list of wilds channels that match this channel, or
     * the empty list if this channel is already wild.
     */
    public function getWilds()
    {
        return $this->_wilds;
    }

}
