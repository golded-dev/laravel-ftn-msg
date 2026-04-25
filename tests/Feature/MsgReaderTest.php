<?php

use Golded\Ftn\Msg\MsgReader;
use Golded\Ftn\ParsedMessage;

function msgFixtureArea(string $area = 'THE_SAFE'): string
{
    return __DIR__.'/../../../archive/messages/MSG/'.$area;
}

it('reads header fields from real MSG files', function (): void {
    $messages = array_values(iterator_to_array(new MsgReader()->read(msgFixtureArea())));
    $first = firstMsgMessage($messages);

    expect($first->fromName)->toBe("Odinn Sorensen's ME2")
        ->and($first->toName)->toBe('Gregory ThroatWobbler')
        ->and($first->subject)->toBe('Keep on the good work..')
        ->and($first->msgno)->toBe(1);
});

it('keeps body kludges and assigns stable external ids', function (): void {
    $messages = array_values(iterator_to_array(new MsgReader()->read(msgFixtureArea())));
    $first = firstMsgMessage($messages);

    expect($first->bodyText)->toContain("\x01")
        ->and($first->bodyText)->toContain('want')
        ->and($first->externalId)->not->toBeNull()
        ->and($first->externalId)->not->toStartWith('hash:');
});

/**
 * @param list<ParsedMessage> $messages
 */
function firstMsgMessage(array $messages): ParsedMessage
{
    if ($messages === []) {
        throw new RuntimeException('Expected at least one parsed MSG message.');
    }

    return $messages[0];
}
