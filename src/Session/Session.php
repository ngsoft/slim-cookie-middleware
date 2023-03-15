<?php

declare(strict_types=1);

namespace NGSOFT\Session;

/**
 * Writes directly into session data
 */
final class Session extends SessionSegment
{

    public function __construct(string $identifier, private bool $initializeSession = true)
    {
        parent::__construct($identifier);

        $this->initializeSession = php_sapi_name() !== 'cli' && $initializeSession;

        if ($this->initializeSession)
        {
            /**
             * Session data will not be recorded
             */
            if (PHP_SESSION_DISABLED === @session_status())
            {
                return;
            }


            /**
             * we close an active session with bad identifier
             */
            if (PHP_SESSION_ACTIVE === @session_status())
            {
                if ($identifier !== @session_id())
                {
                    @session_abort();
                }
                else
                {
                    @session_write_close();
                }
            }

            /**
             * PHP_SESSION_NONE === session_status();
             */
            @session_id($identifier);

            if (@session_start(['use_cookies' => false, 'use_only_cookies' => true]))
            {
                $this->data = &$_SESSION;
            }
        }
    }

}
