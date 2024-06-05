<?php

declare(strict_types=1);

namespace Vjik\TelegramBot\Api\Type;

use Vjik\TelegramBot\Api\ParseResult\ValueHelper;

/**
 * @see https://core.telegram.org/bots/api#document
 */
final readonly class Document
{
    public function __construct(
        public string $fileId,
        public string $fileUniqueId,
        public ?PhotoSize $thumbnail = null,
        public ?string $fileName = null,
        public ?string $mimeType = null,
        public ?int $fileSize = null,
    ) {
    }

    public static function fromTelegramResult(mixed $result): self
    {
        ValueHelper::assertArrayResult($result);
        return new self(
            ValueHelper::getString($result, 'file_id'),
            ValueHelper::getString($result, 'file_unique_id'),
            array_key_exists('thumbnail', $result) ? PhotoSize::fromTelegramResult($result['thumbnail']) : null,
            ValueHelper::getStringOrNull($result, 'file_name'),
            ValueHelper::getStringOrNull($result, 'mime_type'),
            ValueHelper::getIntegerOrNull($result, 'file_size'),
        );
    }
}
