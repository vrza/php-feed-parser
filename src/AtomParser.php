<?php

namespace FeedParser;

class AtomParser extends AbstractXmlFeedParser
{
    protected const ITEMTAG = 'ENTRY';
    protected const LINKTAG = 'ID';
    protected const TITLETAG = 'TITLE';
    protected const MSGTAG = 'SUMMARY';
    protected const PUBDATETAG = 'UPDATED';
}
