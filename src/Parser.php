<?php

namespace FeedParser;

interface Parser
{
    public function parseFile(string $file): array;
    public function parseString(string $json): array;
}
