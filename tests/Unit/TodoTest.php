<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class TodoTest extends TestCase
{
    public function testValidStatesConstant(): void
    {
        $expectedStates = ['draft', 'todo', 'doing', 'done', 'trash'];
        $this->assertEquals($expectedStates, \VALID_TODOS_STATES);
    }

    public function testTodoStateValidation(): void
    {
        $this->assertTrue(in_array('draft', \VALID_TODOS_STATES, true));
        $this->assertTrue(in_array('doing', \VALID_TODOS_STATES, true));
        $this->assertFalse(in_array('invalid_state', \VALID_TODOS_STATES, true));
    }
}
