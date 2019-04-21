<?php


namespace MacFJA\PharBuilder\CodeSniffer\Sniffs\Commenting;


use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class EnsureFileHeaderSniff implements Sniff
{
    public $yearPattern = '20\d{2}';
    public $holderPattern = '[\\w\\s_-]+';
    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * An example return value for a sniff that wants to listen for whitespace
     * and any comments would be:
     *
     * <code>
     *    return array(
     *            T_WHITESPACE,
     *            T_DOC_COMMENT,
     *            T_COMMENT,
     *           );
     * </code>
     *
     * @return mixed[]
     * @see    Tokens.php
     */
    public function register()
    {
        return [T_OPEN_TAG];
    }

    /**
     * Called when one of the token types that this sniff is listening for
     * is found.
     *
     * The stackPtr variable indicates where in the stack the token was found.
     * A sniff can acquire information this token, along with all the other
     * tokens within the stack by first acquiring the token stack:
     *
     * <code>
     *    $tokens = $phpcsFile->getTokens();
     *    echo 'Encountered a '.$tokens[$stackPtr]['type'].' token';
     *    echo 'token information: ';
     *    print_r($tokens[$stackPtr]);
     * </code>
     *
     * If the sniff discovers an anomaly in the code, they can raise an error
     * by calling addError() on the \PHP_CodeSniffer\Files\File object, specifying an error
     * message and the position of the offending token:
     *
     * <code>
     *    $phpcsFile->addError('Encountered an error', $stackPtr);
     * </code>
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The PHP_CodeSniffer file where the
     *                                               token was found.
     * @param int                         $stackPtr  The position in the PHP_CodeSniffer
     *                                               file's token stack where the token
     *                                               was found.
     *
     * @return void|int Optionally returns a stack pointer. The sniff will not be
     *                  called again on the current file until the returned stack
     *                  pointer is reached. Return (count($tokens) + 1) to skip
     *                  the rest of the file.
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Find the next non whitespace token.
        $commentStart = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);

        // Allow declare() statements at the top of the file.
        if ($tokens[$commentStart]['code'] === T_DECLARE) {
            $semicolon    = $phpcsFile->findNext(T_SEMICOLON, ($commentStart + 1));
            $commentStart = $phpcsFile->findNext(T_WHITESPACE, ($semicolon + 1), null, true);
        }

        $errorToken = ($stackPtr + 1);
        if (isset($tokens[$errorToken]) === false) {
            $errorToken--;
        }

        if ($tokens[$commentStart]['code'] === T_CLOSE_TAG) {
            // We are only interested if this is the first open tag.
            return ($phpcsFile->numTokens + 1);
        } else if ($tokens[$commentStart]['code'] !== T_COMMENT) {
            $phpcsFile->addError('Missing file doc comment', $errorToken, 'Missing');
            $phpcsFile->recordMetric($stackPtr, 'File has doc comment', 'no');
            return ($phpcsFile->numTokens + 1);
        }

//        $commentEnd = $phpcsFile->findNext();

        $commentEnd = $phpcsFile->findNext(
            T_WHITESPACE,
            ($commentStart + 1),
            null,
            false
        );

        $ignore = [
            T_CLASS,
            T_INTERFACE,
            T_TRAIT,
            T_FUNCTION,
            T_CLOSURE,
            T_PUBLIC,
            T_PRIVATE,
            T_PROTECTED,
            T_FINAL,
            T_STATIC,
            T_ABSTRACT,
            T_CONST,
            T_PROPERTY,
        ];



        if (in_array($tokens[$commentEnd]['code'], $ignore, true) === true) {
            $phpcsFile->addError('Missing file doc comment', $stackPtr, 'Missing');
            $phpcsFile->recordMetric($stackPtr, 'File has doc comment', 'no');
            return ($phpcsFile->numTokens + 1);
        }

        $phpcsFile->recordMetric($stackPtr, 'File has doc comment', 'yes');

        $content = preg_quote($this->expectedComment, '/');
        $content = str_replace(['%YEAR%', '%HOLDER%'], [$this->yearPattern, $this->holderPattern], $content);

        if (preg_match('/' . $content . '/m', $phpcsFile->getTokensAsString($commentStart, $commentEnd)) !== 1) {
            $phpcsFile->addError('Invalid file comment (not a MIT comment)', $stackPtr, 'Invalid');
            $phpcsFile->recordMetric($stackPtr, 'File has doc comment', 'no');
            return ($phpcsFile->numTokens + 1);
        }

        // Ignore the rest of the file.
        return ($phpcsFile->numTokens + 1);
    }

    private $expectedComment = <<<Header
/* Copyright (C) %YEAR% %HOLDER%
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */
Header;

}
