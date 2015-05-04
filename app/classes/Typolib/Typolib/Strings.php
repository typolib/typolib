<?php
namespace Typolib;

/**
 * Strings class
 *
 * This class is for all the methods we need to manipulate strings
 *
 * @package Typolib
 */
class Strings
{
    public static $regex_extract_sentences = '
    /# Split sentences on whitespace between them.
    (?<=                # Begin positive lookbehind.
      [.!?]             # Either an end of sentence punct,
    | [.!?][\'"]        # or end of sentence punct and quote.
    )                   # End positive lookbehind.
    (?<!                # Begin negative lookbehind.
      Mr\.              # Skip either "Mr."
    | Mrs\.             # or "Mrs.",
    | Ms\.              # or "Ms.",
    | Jr\.              # or "Jr.",
    | Dr\.              # or "Dr.",
    | Prof\.            # or "Prof.",
    | Sr\.              # or "Sr.",
    | T\.V\.A\.         # or "T.V.A.",
                        # or... (you get the idea).
    )                   # End negative lookbehind.
    \s+                 # Split on whitespace between sentences.
    /ix';

    /**
     * Split a string into sentences
     *
     * @param  string $text Text
     * @return string array Array of sentences
     */
    public static function getSentencesFromText($text)
    {
        $sentences_array = preg_split(self::$regex_extract_sentences, $text, -1, PREG_SPLIT_NO_EMPTY);

        return $sentences_array;
    }
}
