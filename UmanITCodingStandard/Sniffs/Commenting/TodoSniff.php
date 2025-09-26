<?php

declare(strict_types=1);

namespace UmanITCodingStandard\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class TodoSniff implements Sniff
{
    /**
     * Forbidden words (case-insensitive), except "@todo" form.
     *
     * @var list<string>
     */
    public array $forbiddenWords = ['TODO', 'FIXME', 'XXX'];

    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_COMMENT, T_DOC_COMMENT_TAG, T_DOC_COMMENT_STRING];
    }

    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];
        $content = $token['content'];

        // Normalize tag case in docblock comment blocks
        if (T_DOC_COMMENT_TAG === $token['code']) {
            if ('@todo' !== $content && 0 === strcasecmp($content, '@todo')) {
                $shouldBeFixed = true === $phpcsFile
                        ->addFixableError(
                            'Use lowercased "@todo".',
                            $stackPtr,
                            'TodoTagCase',
                        )
                ;

                if ($shouldBeFixed) {
                    $phpcsFile->fixer->beginChangeset();
                    $phpcsFile->fixer->replaceToken($stackPtr, '@todo');
                    $phpcsFile->fixer->endChangeset();
                }
            }

            return;
        }

        // Detecting and banning other forms
        $words = array_map('preg_quote', $this->forbiddenWords);
        $pattern = '/(?<!@)\b(' . implode('|', $words) . ')\b(?!\w)/i';

        if (1 === preg_match($pattern, $content)) {
            $shouldBeFixed = true === $phpcsFile
                    ->addFixableError(
                        'Use "@todo" only. "%s" is not allowed.',
                        $stackPtr,
                        'ForbiddenTodoForm',
                        [$this->matchFirst($pattern, $content)],
                    )
            ;

            if ($shouldBeFixed) {
                $phpcsFile->fixer->beginChangeset();
                $fixed = preg_replace_callback($pattern, static fn(): string => '@todo', $content);
                $phpcsFile->fixer->replaceToken($stackPtr, $fixed);
                $phpcsFile->fixer->endChangeset();
            }
        }

        // Normalize tag case in non-docblock comments
        $uppercaseTodo = '/@[Tt][Oo][Dd][Oo]\b/';
        if (1 === preg_match($uppercaseTodo, $content) && !str_contains($content, '@todo')) {
            $shouldBeFixed = true === $phpcsFile
                    ->addFixableError(
                        'Use lowercased "@todo".',
                        $stackPtr,
                        'TodoCaseInComment',
                    )
            ;

            if ($shouldBeFixed) {
                $phpcsFile->fixer->beginChangeset();
                $phpcsFile->fixer->replaceToken($stackPtr, preg_replace($uppercaseTodo, '@todo', $content));
                $phpcsFile->fixer->endChangeset();
            }
        }
    }

    private function matchFirst(string $pattern, string $subject): string
    {
        if (1 === preg_match($pattern, $subject, $m)) {
            return $m[1];
        }

        return '';
    }
}
