<?php
use AlAdhanApi\Helper\Request;

class EmojiTest extends \PHPUnit\Framework\TestCase
{


    public function testEmojis()
    {
        $text = "Hello 👍🏼 World 👨‍👩‍👦‍";

        $this->assertTrue(Request::containsEmoji($text));
        $this->assertFalse(Request::isValidAddress($text));

        $text = "London, UK";

        $this->assertFalse(Request::containsEmoji($text));
        $this->assertTrue(Request::isValidAddress($text));
    }

}
