<?php

namespace Pagarme\Core\Payment\Aggregates\Payments\Authentication;

abstract class AuthenticationStatusEnum
{
    const TRANSACTION_ACCEPTED = 'Y';

    const TRANSACTION_NOT_AUTHENTICATED = 'N';

    const CHALLENGE_REQUEST = 'C';

    const AUTHENTICATION_UNAVAILABLE = 'U';

    const AUTHENTICATION_ATTEMPT = 'A';

    const AUTHENTICATION_DENIED_BY_ISSUER = 'R';

    const JUST_INFORMATION = 'I';

    /**
     * @return array
     */
    public static function doesNotNeedToUseAntifraudStatuses()
    {
        return [
          self::TRANSACTION_ACCEPTED,
          self::AUTHENTICATION_ATTEMPT,
        ];
    }

    /**
     * @return array
     */
    public static function needToUseAntifraudStatuses()
    {
        return [
          self::TRANSACTION_NOT_AUTHENTICATED,
          self::CHALLENGE_REQUEST,
          self::AUTHENTICATION_UNAVAILABLE,
          self::AUTHENTICATION_DENIED_BY_ISSUER,
          self::JUST_INFORMATION,
        ];
    }
}
