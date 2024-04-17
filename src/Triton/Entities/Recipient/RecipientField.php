<?php

namespace Triton\Entities\Recipient;


class RecipientField {

    const EMAIL     = 'email';
    const USER_ID   = 'user_id';
    /**
     * @return array
     */
    public static function all()
    {
        return [
            self::EMAIL,
            self::USER_ID
        ];
    }

    /**
     * @return array
     */
    public static function allRequired()
    {
        return [
            self::EMAIL
        ];
    }
}