<?php

namespace Jaxon\Laravel;

use Jaxon\Contracts\Session as SessionContract;

class Session implements SessionContract
{
    /**
     * Get the current session id
     *
     * @return string           The session id
     */
    public function getId()
    {
        return session()->getId();
    }

    /**
     * Generate a new session id
     *
     * @param bool          $bDeleteData         Whether to delete data from the previous session
     *
     * @return void
     */
    public function newId($bDeleteData = false)
    {
        if($bDeleteData)
        {
            session()->flush();
        }
        session()->regenerate();
    }

    /**
     * Save data in the session
     *
     * @param string        $sKey                The session key
     * @param string        $xValue              The session value
     *
     * @return void
     */
    public function set($sKey, $xValue)
    {
        session()->set($sKey, $xValue);
    }

    /**
     * Check if a session key exists
     *
     * @param string        $sKey                The session key
     *
     * @return bool             True if the session key exists, else false
     */
    public function has($sKey)
    {
        return session()->has($sKey);
    }

    /**
     * Get data from the session
     *
     * @param string        $sKey                The session key
     * @param string        $xDefault            The default value
     *
     * @return mixed|$xDefault             The data under the session key, or the $xDefault parameter
     */
    public function get($sKey, $xDefault = null)
    {
        return session($sKey, $xDefault);
    }

    /**
     * Get all data in the session
     *
     * @return array             An array of all data in the session
     */
    public function all()
    {
        return session()->all();
    }

    /**
     * Delete a session key and its data
     *
     * @param string        $sKey                The session key
     *
     * @return void
     */
    public function delete($sKey)
    {
        session()->forget($sKey);
    }

    /**
     * Delete all data in the session
     *
     * @return void
     */
    public function clear()
    {
        session()->flush();
    }
}
