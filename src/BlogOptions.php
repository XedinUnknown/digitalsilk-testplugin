<?php

declare(strict_types=1);

namespace DigitalSilk\TestPlugin;

use UnexpectedValueException;
use WpOop\Containers\Options\BlogOptions as MultisiteBlogOptions;

/**
 * Retrieves blog options for single site
 */
class BlogOptions extends MultisiteBlogOptions
{
    /**
     * @inheritDoc
     */
    protected function getOption(string $name)
    {
        $blogId = $this->blogId;
        $default = $this->default;
        /** @psalm-suppress PossiblyNullArgument */
        $value = $blogId === null
            ? get_option($name, $default)
            : parent::getOption($name);

        if ($value === $default) {
            throw new UnexpectedValueException(
                $this->__(
                    'Option "%1$s" for blog %2$s does not exist',
                    [$name, $blogId === null ? 'null' : "#$blogId"]
                )
            );
        }

        return $value;
    }
}
