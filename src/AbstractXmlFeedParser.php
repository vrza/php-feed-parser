<?php

namespace FeedParser;

use DateTime;
use Exception;

class AbstractXmlFeedParser implements Parser
{
    // self-referential constants must be 
    // defined by the extending classes
    protected const ITEMTAG = self::ITEMTAG;
    protected const LINKTAG = self::LINKTAG;
    protected const TITLETAG = self::TITLETAG;
    protected const MSGTAG = self::MSGTAG;
    protected const PUBDATETAG = self::PUBDATETAG;

    private const LINKKEY = 'link';
    private const TITLEKEY = 'title';
    private const MSGKEY = 'message';
    private const PUBDATEKEY = 'published';

    private $parser;
    private $items = [];
    private $item = [];

    private $inItem = false;
    private $inLink = false;
    private $inTitle = false;
    private $inMsg = false;
    private $inPubDate = false;

    private function startElement($parser, string $name, $attrs)
    {
        //echo "START\n";
        //var_dump($name, $attrs);
        if (strcasecmp($name, $this::ITEMTAG) === 0) {
            $this->inItem = true;
            $this->item = [];
        }
        if ($this->inItem && strcasecmp($name, $this::LINKTAG) === 0) {
            //echo "ENTERING LINK TAG\n";
            $this->inLink = true;
        }
        if ($this->inItem && strcasecmp($name, $this::TITLETAG) === 0) {
            //echo "ENTERING TITLE TAG\n";
            $this->inTitle = true;
        }
        if ($this->inItem && strcasecmp($name, $this::MSGTAG) === 0) {
            //echo "ENTERING MSG TAG\n";
            $this->inMsg = true;
        }
        if ($this->inItem && strcasecmp($name, $this::PUBDATETAG) === 0) {
            //echo "ENTERING PUBDATE TAG\n";
            $this->inPubDate = true;
        }
    }

    private function endElement($parser, string $name)
    {
        //echo "END\n";
        //var_dump($name);
        if (strcasecmp($name, $this::ITEMTAG) === 0) {
            $this->items[] = $this->item;
            $this->inItem = false;
            //var_dump($this->item);
        }
        if (strcasecmp($name, $this::LINKTAG) === 0) {
            //echo "EXITING LINK TAG\n";
            $this->inLink = false;
        }
        if (strcasecmp($name, $this::TITLETAG) === 0) {
            //echo "EXITING TITLE TAG\n";
            $this->inTitle = false;
        }
        if (strcasecmp($name, $this::MSGTAG) === 0) {
            //echo "EXITING MSG TAG\n";
            $this->inMsg = false;
        }
        if (strcasecmp($name, $this::PUBDATETAG) === 0) {
            //echo "EXITING PUBDATE TAG\n";
            $this->inPubDate = false;
        }
    }

    private function characterData($parser, string $data)
    {
        //echo "CHAR\n";
        if ($this->inLink) {
            //echo "IN LINK TAG\n";
            $this->appendCharacterData(self::LINKKEY, $data);
        }
        if ($this->inTitle) {
            //echo "IN TITLE TAG\n";
            $this->appendCharacterData(self::TITLEKEY, $data);
        }
        if ($this->inMsg) {
            //echo "IN MSG TAG\n";
            $this->appendCharacterData(self::MSGKEY, $data);
        }
        if ($this->inPubDate) {
            //echo "IN PUBDATE TAG\n";
            $this->appendCharacterData(self::PUBDATEKEY, $data);
        }
    }

    private function appendCharacterData(string $key, string $data): void
    {
        if (array_key_exists($key, $this->item)) {
            $this->item[$key] .= $data;
        } else {
            $this->item[$key] = $data;
        }
    }

    public function __construct()
    {
        $this->parser = xml_parser_create();
        xml_set_element_handler(
            $this->parser,
            function ($parser, $name, $attrs) {
                return $this->startElement($parser, $name, $attrs);
            },
            function ($parser, $name) {
                return $this->endElement($parser, $name);
            }
        );
        xml_set_character_data_handler(
            $this->parser,
            function ($parser, $data) {
                return $this->characterData($parser, $data);
            }
        );
    }

    public function __destruct()
    {
        //fwrite(STDOUT, 'XmlFeedParser destructor' . PHP_EOL);
        xml_parser_free($this->parser);
    }

    private function parseDates(): void
    {
        for ($i = 0; $i < count($this->items); $i++) {
            $item = $this->items[$i];
            if (array_key_exists('published', $item)) {
                $published = $item['published'];
                try {
                    $dt = new DateTime($published);
                    $this->items[$i]['datetime'] = $dt;
                } catch (Exception $e) {
                }
            }
        }
    }

    public function parseFile(string $file): array
    {
        if (!($fp = fopen($file, 'r'))) {
            throw new ParsingException("Could not open input file: $file");
        }

        while ($data = fread($fp, 4096)) {
            if (!xml_parse($this->parser, $data, feof($fp))) {
                throw new ParsingException(
                    sprintf("XML error: %s at line %d",
                            xml_error_string(xml_get_error_code($this->parser)),
                            xml_get_current_line_number($this->parser))
                );
            }
        }

        fclose($fp);

        $this->parseDates();
        //var_dump($this->items);
        return $this->items;
    }

    public function parseString(string $xml): array
    {
        if (!xml_parse($this->parser, $xml)) {
            throw new ParsingException(
                sprintf("XML error: %s at line %d",
                        xml_error_string(xml_get_error_code($this->parser)),
                        xml_get_current_line_number($this->parser))
            );
        }
        $this->parseDates();
        //var_dump($this->items);
        return $this->items;
    }

}
