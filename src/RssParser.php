<?php

namespace FeedParser;

class RssParser extends AbstractXmlFeedParser
{
    protected const ITEMTAG = 'ITEM';
    protected const LINKTAG = 'LINK';
    protected const TITLETAG = 'TITLE';
    protected const MSGTAG = 'DESCRIPTION';
    protected const PUBDATETAG = 'PUBDATE';
}
