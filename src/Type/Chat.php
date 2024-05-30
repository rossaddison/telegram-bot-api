<?php

declare(strict_types=1);

namespace Vjik\TelegramBot\Api\Type;

use Vjik\TelegramBot\Api\ParseResult\ValueHelper;

/**
 * @see https://core.telegram.org/bots/api#chat
 */
final readonly class Chat
{
    public function __construct(
        public int $id,
        public string $type,
        public ?string $title,
        public ?string $username,
        public ?string $firstName,
        public ?string $lastName,
        public ?true $isForum,
    ) {
    }

    public static function fromTelegramResult(mixed $result): self
    {
        ValueHelper::assertArrayResult($result);
        return new self(
            ValueHelper::getInteger($result, 'id'),
            ValueHelper::getString($result, 'type'),
            ValueHelper::getStringOrNull($result, 'title'),
            ValueHelper::getStringOrNull($result, 'username'),
            ValueHelper::getStringOrNull($result, 'first_name'),
            ValueHelper::getStringOrNull($result, 'last_name'),
            ValueHelper::getTrueOrNull($result, 'is_forum'),
        );
    }
}
