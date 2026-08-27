<?php

declare(strict_types=1);

namespace AssoConnect\LinxoClient\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Every catalogue must cover the reference locale's messages, each in a wording of its own. Neither a
 * missing catalogue nor an untranslated label fails at runtime, so this test is what guards them.
 */
class TranslationCatalogueTest extends TestCase
{
    private const string TRANSLATIONS_DIR = __DIR__ . '/../translations';
    private const string DOMAIN = 'linxo+intl-icu';
    private const string REFERENCE_LOCALE = 'en_US';

    /** Every locale the catalogues are expected to cover. */
    private const array EXPECTED_LOCALES = ['en_US', 'fr_FR', 'es_ES'];

    /** @return iterable<string, array{locale: string}> */
    public static function provideLocales(): iterable
    {
        foreach (self::EXPECTED_LOCALES as $locale) {
            yield $locale => ['locale' => $locale];
        }
    }

    #[DataProvider('provideLocales')]
    public function testCatalogueExists(string $locale): void
    {
        self::assertFileExists(self::cataloguePath($locale));
    }

    #[DataProvider('provideLocales')]
    public function testCatalogueCoversEveryReferenceMessage(string $locale): void
    {
        self::assertSame(
            array_keys($this->messages(self::REFERENCE_LOCALE)),
            array_keys($this->messages($locale)),
            sprintf('The %s catalogue does not cover the same messages as the %s one.', $locale, self::REFERENCE_LOCALE)
        );
    }

    #[DataProvider('provideLocales')]
    public function testEveryMessageIsTranslated(string $locale): void
    {
        $reference = $this->messages(self::REFERENCE_LOCALE);

        foreach ($this->messages($locale) as $key => $message) {
            self::assertNotSame(
                '',
                trim($message),
                sprintf('Message "%s" has no %s translation.', $key, $locale)
            );

            if (self::REFERENCE_LOCALE === $locale) {
                continue;
            }

            // A catalogue copied from the reference locale and left untranslated clears the emptiness check
            self::assertNotSame(
                $reference[$key],
                $message,
                sprintf('Message "%s" still reads as its %s wording.', $key, self::REFERENCE_LOCALE)
            );
        }
    }

    private static function cataloguePath(string $locale): string
    {
        return sprintf('%s/%s.%s.yml', self::TRANSLATIONS_DIR, self::DOMAIN, $locale);
    }

    /** @return array<string, string> */
    private function messages(string $locale): array
    {
        $catalogue = Yaml::parseFile(self::cataloguePath($locale));
        self::assertIsArray($catalogue, sprintf('The %s catalogue is not a YAML mapping.', $locale));

        $messages = self::flatten($catalogue);
        ksort($messages);

        return $messages;
    }

    /**
     * @param array<mixed> $catalogue
     *
     * @return array<string, string>
     */
    private static function flatten(array $catalogue, string $prefix = ''): array
    {
        $messages = [];

        foreach ($catalogue as $key => $value) {
            $path = '' === $prefix ? (string) $key : $prefix . '.' . $key;

            if (is_array($value)) {
                $messages += self::flatten($value, $path);
                continue;
            }

            $messages[$path] = (string) $value;
        }

        return $messages;
    }
}
