<?php

namespace FeedParser;

class FeedFormatDetector
{
    /**
     * @throws ParsingException
     */
    public static function createParserForFile(string $file): Parser
    {
        if (!($fp = @fopen($file, 'r'))) {
            throw new ParsingException("Could not open input file: $file");
        }
        $head = fread($fp, 409);
        $format = FeedFormatDetector::regexDetect($head);
        if (empty($format)) {
            throw new ParsingException("Unknown feed format: $file");
        } else {
            return new $format;
        }
    }

    /**
     * @throws ParsingException
     */
    public static function createParserForString(string $xml): Parser
    {
        $format = FeedFormatDetector::regexDetect($xml);
        if (empty($format)) {
            throw new ParsingException('Unknown feed format');
        } else {
            return new $format;
        }
    }

    /**
     * Using regex magic for fast format detection without parsing
     * the entire file
     *
     * # TODO look into cleaner ways to do detection
     *
     * @param $str
     * @return string
     */
    public static function regexDetect(string $str): string
    {
        $magic = [
            AtomParser::class => '#<feed\b[^>]+\bxmlns="http://www.w3.org/2005/Atom"#i',
            RssParser::class  => '#<rss\b[^>]+\bversion="2.0"#i',
            JsonFeedParser::class => '#"https:\\\/\\\/jsonfeed\.org\\\/version\\\/1"#i'
        ];

        foreach ($magic as $format => $re) {
            if (preg_match($re, $str)) {
                //fwrite (STDOUT, "Format is $format\n");
                return $format;
            } else {
                //fwrite(STDOUT, "Failed to match $format" . PHP_EOL);
            }
        }

        return '';
    }
}
