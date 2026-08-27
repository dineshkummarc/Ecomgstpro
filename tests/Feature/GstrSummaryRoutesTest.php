<?php

namespace Tests\Feature;

use Tests\TestCase;

class GstrSummaryRoutesTest extends TestCase
{
    public function test_doc_issue_csv_route_is_registered(): void
    {
        $route = $this->app['router']->getRoutes()->getByName('gstr.summary.downloadDocIssueCSV');

        $this->assertNotNull($route);
        $this->assertSame('gstr/summary/download-doc-issue-csv', $route->uri());
    }
}
