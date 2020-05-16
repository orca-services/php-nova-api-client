<?php

namespace OrcaServices\NovaApi\Result;

/**
 * Trait.
 */
trait NovaMessageTrait
{
    /**
     * Messages.
     *
     * @var NovaMessage[]
     */
    public $messages = [];

    /**
     * Set value.
     *
     * @param NovaMessage $message The value
     *
     * @return void
     */
    public function addMessage(NovaMessage $message)
    {
        $this->messages[] = $message;
    }
}
