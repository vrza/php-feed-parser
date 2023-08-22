<?php

namespace FeedParser;

use DateTime;

class JsonFeedParser implements Parser
{
    private $rawJsonParse;
    private $finalResult;

    public function parseFile(string $file): array
    {
        return $this->parseString(file_get_contents($file));
    }

    public function parseString(string $json): array
    {
        $this->rawJsonParse = json_decode($json, true);
        $this->convert();
        return $this->finalResult;
    }

    private function convert()
    {
        foreach ($this->rawJsonParse['items'] as $rawItem) {
            $item = [
                'title' => $rawItem['title'],
                'link' => $rawItem['url'],
                'message' => $rawItem['content_html'], // or summary
                'image' => $rawItem['image'],
                'published' => $rawItem['date_published'],
                'datetime' => new DateTime($rawItem['date_published'])
            ];
            $this->finalResult[] = $item;
        }
    }
}
