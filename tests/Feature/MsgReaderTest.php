<?php

use Golded\Ftn\Msg\MsgReader;
use Golded\Ftn\ParsedMessage;

function msgFixtureArea(string $area = 'THE_SAFE'): string
{
    $path = sys_get_temp_dir().'/laravel-ftn-msg-tests/'.$area;

    if (! is_dir($path)) {
        mkdir($path, recursive: true);
    }

    file_put_contents($path.'/1.MSG', msgFixture(
        fromName: "Odinn Sorensen's ME2",
        toName: 'Gregory ThroatWobbler',
        subject: 'Keep on the good work..',
        date: '01 Jan 24  12:34:56',
        body: "\x01MSGID: 2:230/150 12345678\r\nI want this message body preserved.\r\n",
    ));

    return $path;
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

it('attaches control metadata and provenance', function (): void {
    $messages = array_values(iterator_to_array(new MsgReader()->read(msgFixtureArea())));
    $first = firstMsgMessage($messages);

    expect($first->controlLines?->msgid)->toBe('2:230/150 12345678')
        ->and($first->provenance?->sourceType)->toBe('msg')
        ->and($first->provenance?->sourcePath)->toEndWith('/1.MSG')
        ->and($first->provenance?->sourceId)->toBe('1')
        ->and($first->provenance?->sourceOffset)->toBeNull();
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

function msgFixture(
    string $fromName,
    string $toName,
    string $subject,
    string $date,
    string $body,
): string {
    $header = str_pad($fromName, 36, "\0")
        .str_pad($toName, 36, "\0")
        .str_pad($subject, 72, "\0")
        .str_pad($date, 20, "\0")
        .str_repeat("\0", 22)
        .pack('v', 0)
        .str_repeat("\0", 2);

    return $header.$body;
}
