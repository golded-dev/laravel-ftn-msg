# Laravel FTN MSG

FTN/FidoNet `.MSG` message-base reader for PHP 8.4.

This package reads classic `.MSG` message directories and returns normalized `ParsedMessage` objects from `golded-dev/laravel-ftn`.

It does not read Squish, JAM, Hudson, or packet files. It does not add Laravel service providers, config files, models, commands, queues, or framework bootstrapping. The package name says Laravel because it belongs to the GoldED.dev Laravel package family. The runtime code is plain PHP.

## Installation

```bash
composer require golded-dev/laravel-ftn-msg:^1.0
```

Requires PHP 8.4+.

## Reading A Message Area

```php
<?php

declare(strict_types=1);

use Golded\Ftn\Msg\MsgReader;

$reader = new MsgReader();

foreach ($reader->read('/path/to/messages/MSG/THE_SAFE') as $message) {
    echo $message->msgno.PHP_EOL;
    echo $message->fromName.' -> '.$message->toName.PHP_EOL;
    echo $message->subject.PHP_EOL;
    echo $message->bodyText.PHP_EOL;
}
```

`MsgReader::read()` accepts a directory path and reads files ending in `.msg` or `.MSG`.

Files are sorted naturally by filename. Non-numeric filenames and message numbers below `1` are skipped.

## Reader Options

```php
use Golded\Ftn\Msg\MsgReader;
use Golded\Ftn\ReaderOptions;

$messages = new MsgReader()->read(
    path: '/path/to/messages/MSG/NETMAIL',
    options: new ReaderOptions(fallbackCharset: 'CP437'),
);
```

The fallback charset is used when the message body does not declare a usable FTN charset control line. The default comes from `golded-dev/laravel-ftn`.

## What Gets Parsed

The reader extracts:

- message number from the `.MSG` filename
- from name
- to name
- subject
- body text converted to UTF-8
- raw attribute bitfield
- posted date when the header date can be parsed
- external ID from `MSGID` when present

When a message has no `MSGID`, the reader creates a stable synthetic ID from the parsed message fields. Old message bases are rarely polite. This keeps downstream imports sane anyway.

## What You Do Not Get

- No area discovery.
- No packet parsing.
- No Squish, JAM, Hudson, or other message-base readers.
- No Laravel service provider.
- No database models.
- No queues, commands, config publishing, or framework bootstrapping.

Pair this package with your own source locator or import pipeline.

## Development

Install dependencies:

```bash
composer install
```

Run tests:

```bash
composer test
```

Run static analysis:

```bash
composer test:types
```

Run Rector dry-run:

```bash
composer test:refactor
```

Run everything:

```bash
composer test:all
```

## Versioning

This package starts at `1.0.0` and uses semantic versioning.

Breaking changes include:

- changing `MsgReader::read()` behavior in a way that drops messages previously yielded
- changing parsed field semantics
- changing charset fallback behavior
- changing the required PHP version
- changing the `golded-dev/laravel-ftn` public contract this reader returns

Adding support for more `.MSG` header fields is usually a minor release when existing fields keep their meaning.

## Contributing

Contributions are welcome when they make `.MSG` parsing more correct without turning this into a framework package. See [CONTRIBUTING.md](CONTRIBUTING.md).

## Security

Do not report security issues in public tickets. See [SECURITY.md](SECURITY.md).

## Code Of Conduct

Be direct, useful, and not a pain on purpose. See [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md).

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

Released under the MIT License. See [LICENSE](LICENSE).
