<?php

// Maximum file size (e.g., 2MB)
define('MAX_FILE_SIZE', 2 * 1024 * 1024);

// Class FileUploader: Handles file validation and reading.
class FileUploader
{
    private $file;
    private $errors = [];

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function validate()
    {
        // Validate file size
        if ($this->file['size'] > MAX_FILE_SIZE) {
            $this->errors[] = 'File size exceeds the maximum limit of 2MB.';
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $this->file['tmp_name']);
        finfo_close($finfo);

        // Allow 'text/html' and also 'application/xhtml+xml' for better compatibility
        $allowed_mime_types = ['text/html', 'application/xhtml+xml'];
        if (!in_array($mime_type, $allowed_mime_types)) {
            $this->errors[] = 'Invalid file type. Only HTML files are allowed.';
        }

        return empty($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getContent()
    {
        return file_get_contents($this->file['tmp_name']);
    }

    public function cleanup()
    {
        if (file_exists($this->file['tmp_name'])) {
            unlink($this->file['tmp_name']);
        }
    }
}

// Class HTMLParser: Parses the HTML content and extracts the required data.
class HTMLParser
{
    private $dom;
    private $xpath;
    private $reference;
    private $reportDate;
    private $records = [];

    public function __construct($htmlContent)
    {
        libxml_use_internal_errors(true);
        $this->dom = new DOMDocument();
        $this->dom->loadHTML($htmlContent);
        libxml_clear_errors();

        $this->xpath = new DOMXPath($this->dom);
    }

    public function parse()
    {
        $this->extractReference();
        $this->extractReportDate();

        if (empty($this->reference) || empty($this->reportDate)) {
            throw new Exception('Reference or Report Date not found in the HTML file.');
        }

        $this->extractRecords();

        return $this->records;
    }

    private function extractReference()
    {
        // Use contains() to match any h3 element containing 'Reference'
        $node = $this->xpath->query("//div[@class='re-data']/div[1]/h3[contains(text(), 'Reference')]/following-sibling::p[1]")->item(0);
        if ($node) {
            $this->reference = $this->cleanField($node->nodeValue);
        } else {
            throw new Exception('Reference number not found in the HTML file.');
        }
    }

    private function extractReportDate()
    {
        // Extract Report Date
        $report_date_node = $this->xpath->query("//div[@class='re-data']/div[2]/h3[contains(text(), 'Date')]/following-sibling::p[1]")->item(0);
        if ($report_date_node) {
            $this->reportDate = $this->formatDate($report_date_node->nodeValue);
        } else {
            throw new Exception('Report Date not found in the HTML file.');
        }
    }

    private function extractRecords()
    {
        // Adjust the XPath to match your table
        $tableNodes = $this->xpath->query("//*[@id='ctrlCreditReport']//div[@id='Summary']//table[contains(@class, 'rpt_content_table rpt_content_header rpt_table4column')]");
        //$tableNodes = $this->xpath->query("//*[@id='ctrlCreditReport']//table[contains(@class, 'rpt_content_table rpt_content_header rpt_table4column')][7]");
        //$tableNodes = $this->xpath->query("//*[@id='ctrlCreditReport']//div[@id='Summary']/table[contains(@class, 'rpt_content_table rpt_content_header rpt_table4column')]");
        if ($tableNodes->length == 0) {
            return; // No records found
        }

        $table = $tableNodes->item(0);
        $rows = $table->getElementsByTagName('tr');

        // Skip the header row
        for ($i = 1; $i < $rows->length; $i++) {
            $row = $rows->item($i);
            $cells = $row->getElementsByTagName('td');

            if ($cells->length < 4) {
                continue;
            }

            $type = $this->cleanField($cells->item(0)->nodeValue);
            $chesterPA = $this->cleanField($this->cleanCell($cells->item(1)));
            $allenTX = $this->cleanField($this->cleanCell($cells->item(2)));
            $atlantaGA = $this->cleanField($this->cleanCell($cells->item(3)));

            $record = [
                'Reference'   => $this->reference,
                'ReportDate'  => $this->reportDate,
                'Type'        => rtrim($type, ':'),
                'ChesterPA'   => $chesterPA,
                'AllenTX'     => $allenTX,
                'AtlantaGA'   => $atlantaGA,
            ];

            $this->records[] = $record;
        }
    }

    private function cleanCell($cell)
    {
        // Extract text content, handling multiple lines
        $content = '';
        foreach ($cell->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $content .= trim($child->nodeValue) . ' ';
            } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                $content .= trim($child->textContent) . ' ';
            }
        }
        return trim(preg_replace('/\s+/', ' ', $content));
    }

    private function cleanField($field)
    {
        // Remove any trailing ' -' from the field and trim whitespace
        return trim(str_replace(' -', '', $field));
    }

    private function formatDate($date)
    {
        // Remove any trailing ' -' from the date string
        $date = $this->cleanField($date);

        // Convert MM/DD/YYYY to YYYY-MM-DD
        $dateObj = DateTime::createFromFormat('m/d/Y', $date);
        return $dateObj ? $dateObj->format('Y-m-d') : null;
    }
}

?>
