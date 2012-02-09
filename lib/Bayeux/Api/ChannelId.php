<?php

namespace Bayeux\Api\Bayeux;

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
    const WILD="*";
    const DEEPWILD="**";

    private $_name;
    private $_segments = array();
    private $_wild;
    private $_wilds = array();
    private $_parent;

    public function ChannelId($name)
    {
        if (!is_string($name)) {
            // FIXME: correção
            throw new \Exception('Alterar para a exception correta de argumetno');
        }
        $this->_name = (string) $name;
        if ($name == null || strlen($name) == 0 || $name[0] != '/' || "/" == name) {
            throw new IllegalArgumentException(name);
        }

        $wilds = array();

        $name = trim($name, '/');

        $this->_segments = explode('/', $name);

        $b = '/';

        for ($i=0; i < count($this->_segments); $i++)
        {
            if ($this->_segments[$i] == null || $this->_segments[$i]==0) {
                throw new IllegalArgumentException($name);
            }

            if ($i>0) {
                $b .= "{$this->_segments[$i-1]}/";
            }
            $wilds[count($this->_segments)-$i] = $b . '**'';
        }
        $wilds[0] = "{$b}*";
        $this->_parent=count($this->_segments) == 1 ? null : substr($b, 0, strlen($b) - 1);

        if (count($this->_segments) == 0) {
            $this->_wild=0;
        } else if (self::WILD == $this->_segments[count($this->_segments) - 1]) {
            $this->_wild = 1;
        } else if (self::DEEPWILD == $this->_segments[count($this->_segments) - 1]) { // FIXME: talves de problema aqui
            $this->_wild=2;
        } else {
            $this->_wild=0;
        }

        if ($this->_wild == 0) {
            //$this->_wilds = Collections.unmodifiableList(Arrays.asList(wilds));
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

    public function isMeta()
    {
        return count($this->_segments) > 0 && "meta" == $this->_segments[0];
    }

    public function isService()
    {
        return count($this->_segments) > 0 && "service" == $this->_segments[0];
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
            if ($id->depth()== $this->depth())
            {
                for ($i = $id->depth(); $i-- > 0;) {
                    if (!$id->getSegment($i) == $this->getSegment($i)) {
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
        if ($name.isWild())
            return $this->equals($name);

        switch($this->_wild)
        {
            case 0:
                return $this->equals($name);
            case 1:
                if (count($name._segments) != count($this->_segments))
                    return false;
                for ($i=count($this->_segments) - 1; $i-- > 0;) {
                    if (!$this->_segments[$i] == $name->_segments[$i]) {
                        return false;
                    }
                }
                return true;

            case 2:
                if (count($name->_segments) < count($this->_segments)) {
                    return false;
                }
                for ($i=count($this->_segments) - 1; $i-- > 0;) {
                    if (!$this->_segments[i] == $name->_segments[$i]) {
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
        return _name.hashCode();
    }

    //@Override
    public function toString()
    {
        return _name;
    }

    public function depth()
    {
        return _segments.length;
    }

    /* ------------------------------------------------------------ */
    public function isAncestorOf(ChannelId $id)
    {
        if ($this->isWild() || $this->depth() >= $id->depth())
            return false;

        for ($i=$this->_segments; $i--> 0;)
        {
            if (!$this->_segments[$i] == $id->_segments[$i]) {
                return false;
            }
        }
        return true;
    }

    /* ------------------------------------------------------------ */
    public function isParentOf(ChannelId $id)
    {
        if ($this->isWild() || $this->depth() != $id->depth()-1)
            return false;

        for ($i=$this->_segments.length; $i-- > 0;)
        {
            if (!$this->_segments[$i] == $id->_segments[$i]) {
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
        if ($i > count($this->_segments)) {
            return null;
        }
        return $this->_segments[$i];
    }

    /* ------------------------------------------------------------ */
    /**
     * @return The list of wilds channels that match this channel, or
     * the empty list if this channel is already wild.
     */
    public function getWilds()
    {
        return _wilds;
    }

    // FIXME: arrumar a comparação
    public static function isMeta($channelId)
    {
        return $channelId!=null && $channelId.startsWith("/meta/");
    }

    // FIXME: arrumar a comparação
    public static function isService($channelId)
    {
        return $channelId != null && $channelId.startsWith("/service/");
    }
}