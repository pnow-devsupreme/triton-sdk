<?php

namespace Triton\Entities\Event;


class EventField {

    /**
     * Unique event name
     */
    const EVENT_ID = 'eid';

    /**
     * Recipient data
     */
    const RECIPIENT = 'rcp';

    /**
     * Variables
     */
    const VARS = 'v';

    /**
     * Event time
     */
    const TIMESTAMP = 't';

    /**
     * Var type check mode
     */
    const STRICT_MODE = 's';


    /**
     * Returns all event fields
     *
     * @return array
     */
    public static function all()
    {
        return [
            self::EVENT_ID,
            self::RECIPIENT,
            self::VARS,
            self::TIMESTAMP,
            self::STRICT_MODE
        ];
    }
}