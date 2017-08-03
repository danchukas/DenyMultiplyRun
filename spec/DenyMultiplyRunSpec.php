<?php

namespace spec\DanchukAS\DenyMultiplyRun;

use DanchukAS\DenyMultiplyRun\DenyMultiplyRun;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DenyMultiplyRunSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DenyMultiplyRun::class);
    }
}
