<?php

declare(strict_types=1);

namespace Phlex\Core;

trait SessionTrait
{
    use NameTrait;

    /**
     * Session container key.
     *
     * @var string
     */
    protected $sessionKey = '__phlex_session';

    /**
     * Create new session.
     *
     * @param array $options Options for session_start()
     */
    public function startSession(array $options = [])
    {
        switch (session_status()) {
            case \PHP_SESSION_DISABLED:
                // @codeCoverageIgnoreStart - impossible to test
                throw new Exception('Sessions are disabled on server');
                // @codeCoverageIgnoreEnd
                break;
            case \PHP_SESSION_NONE:
                session_start($options);

                break;
        }
    }

    /**
     * Destroy existing session.
     */
    public function destroySession()
    {
        if (session_status() === \PHP_SESSION_ACTIVE) {
            session_destroy();
            unset($_SESSION);
        }
    }

    /**
     * Remember data in object-relevant session data.
     *
     * @param mixed $value Value
     *
     * @return mixed $value
     */
    public function memorize(string $key, $value)
    {
        $this->startSession();

        $_SESSION[$this->sessionKey][$this->elementName][$key] = $value;

        return $value;
    }

    /**
     * Similar to memorize, but if value for key exist, will return it.
     *
     * @return mixed Previously memorized data or $default
     */
    public function learn(string $key, $default = null)
    {
        $this->startSession();

        if (!isset($_SESSION[$this->sessionKey][$this->elementName][$key])
            || $_SESSION[$this->sessionKey][$this->elementName][$key] === null
        ) {
            if ($default instanceof \Closure) {
                $default = $default($key);
            }

            return $this->memorize($key, $default);
        }

        return $this->recall($key);
    }

    /**
     * Returns session data for this object. If not previously set, then
     * $default is returned.
     *
     * @return mixed Previously memorized data or $default
     */
    public function recall(string $key, $default = null)
    {
        $this->startSession();

        if (!isset($_SESSION[$this->sessionKey][$this->elementName][$key])
            || $_SESSION[$this->sessionKey][$this->elementName][$key] === null
        ) {
            if ($default instanceof \Closure) {
                $default = $default($key);
            }

            return $default;
        }

        return $_SESSION[$this->sessionKey][$this->elementName][$key];
    }

    /**
     * Forget session data for $key. If $key is omitted will forget all
     * associated session data.
     *
     * @param string $key Optional key of data to forget
     *
     * @return $this
     */
    public function forget(string $key = null)
    {
        $this->startSession();

        if ($key === null) {
            unset($_SESSION[$this->sessionKey][$this->elementName]);
        } else {
            unset($_SESSION[$this->sessionKey][$this->elementName][$key]);
        }

        return $this;
    }
}
