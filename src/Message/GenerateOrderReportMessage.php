<?php

namespace App\Message;

class GenerateOrderReportMessage
{
    private $reportId;

    public function __construct(string $reportId)
    {
        $this->reportId = $reportId;
    }

    public function getReportId(): string
    {
        return $this->reportId;
    }
}
