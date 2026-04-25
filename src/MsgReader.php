<?php

declare(strict_types=1);

namespace Golded\Ftn\Msg;

use DateTimeImmutable;
use Golded\Ftn\Contracts\MessageBaseReader;
use Golded\Ftn\ParsedMessage;
use Golded\Ftn\ReaderOptions;
use Golded\Ftn\Support\CharsetDetector;
use Golded\Ftn\Support\ControlLines;
use Golded\Ftn\Support\Text;

final class MsgReader implements MessageBaseReader
{
    private const int HEADER_SIZE = 190;

    /**
     * @return iterable<ParsedMessage>
     */
    public function read(string $path, ?ReaderOptions $options = null): iterable
    {
        $options ??= new ReaderOptions();
        $files = glob(rtrim($path, '/\\').'/*.msg') ?: glob(rtrim($path, '/\\').'/*.MSG') ?: [];

        sort($files, SORT_NATURAL);

        foreach ($files as $file) {
            $msgno = (int) pathinfo($file, PATHINFO_FILENAME);

            if ($msgno < 1) {
                continue;
            }

            $message = $this->readFile($file, $msgno, $options);

            if ($message instanceof ParsedMessage) {
                yield $message;
            }
        }
    }

    private function readFile(string $file, int $msgno, ReaderOptions $options): ?ParsedMessage
    {
        $raw = file_get_contents($file);

        if ($raw === false || strlen($raw) < self::HEADER_SIZE) {
            return null;
        }

        $attr = $this->readUnsignedShort(substr($raw, 186, 2));
        $bodyRaw = substr($raw, self::HEADER_SIZE);
        $charset = CharsetDetector::detect($bodyRaw, $options->fallbackCharset);

        $fromName = Text::toUtf8(Text::readNullPaddedField($raw, 0, 36), $charset);
        $toName = Text::toUtf8(Text::readNullPaddedField($raw, 36, 36), $charset);
        $subject = Text::toUtf8(Text::readNullPaddedField($raw, 72, 72), $charset);
        $body = Text::parseBody($bodyRaw);
        $postedAt = $this->parseDate(Text::readNullPaddedField($raw, 144, 20));
        $externalId = ControlLines::extractMsgid($bodyRaw)
            ?? Text::syntheticId($fromName, $toName, $subject, $postedAt?->format(DATE_ATOM), $body);

        return new ParsedMessage(
            msgno: $msgno,
            fromName: $fromName,
            toName: $toName,
            subject: $subject,
            bodyText: Text::toUtf8($body, $charset),
            attributesRaw: $attr,
            postedAt: $postedAt,
            externalId: $externalId,
        );
    }

    private function parseDate(string $date): ?DateTimeImmutable
    {
        $date = trim($date);

        if ($date === '') {
            return null;
        }

        foreach (['d M y  H:i:s', 'd M y H:i:s'] as $format) {
            $parsed = DateTimeImmutable::createFromFormat($format, $date);

            if ($parsed !== false) {
                return $parsed;
            }
        }

        return null;
    }

    private function readUnsignedShort(string $raw): int
    {
        $value = unpack('vvalue', $raw);

        if ($value === false) {
            return 0;
        }

        $unsignedShort = $value['value'] ?? 0;

        return is_int($unsignedShort) ? $unsignedShort : 0;
    }
}
