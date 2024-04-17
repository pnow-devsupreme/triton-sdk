<?php

namespace Triton\Entities\Variable;

class Context {

    /**
     * Var defined in data contract (event)
     */
    const EVENT = 'event';

    /**
     * Var is a list column
     */
    const RECIPIENT_LIST = 'list';

    /**
     * Var defined in trigger by user
     */
    const TRIGGER = 'trigger';

    /**
     * Var defined in campaign by user
     */
    const CAMPAIGN = 'campaign';

    /**
     * Var defined in recipient entity
     */
    const RECIPIENT = 'recipient';

}