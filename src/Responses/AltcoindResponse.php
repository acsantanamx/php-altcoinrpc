<?php

declare(strict_types=1);

namespace DOne\Altcoin\Responses;

use DOne\Altcoin\Traits\Collection;
use DOne\Altcoin\Traits\ImmutableArray;
use DOne\Altcoin\Traits\SerializableContainer;

class AltcoindResponse extends Response implements
    \ArrayAccess,
    \Countable,
    \Serializable,
    \JsonSerializable
{
    use Collection;
    use ImmutableArray;
    use SerializableContainer;

    /**
     * Gets array representation of response object.
     *
     * @return array
     */
    public function toArray(): array
    {
        return (array) $this->result();
    }

    /**
     * Gets root container of response object.
     *
     * @return array
     */
    public function toContainer(): array
    {
        return $this->container;
    }
}
