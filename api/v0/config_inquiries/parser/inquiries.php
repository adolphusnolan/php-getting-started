<?php

// Maximum file size (e.g., 2MB)
define('MAX_FILE_SIZE', 2 * 1024 * 1024);

/**
 * Class FileUploader
 * Handles file validation and reading.
 */
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

/**
 * Class HTMLParser
 * Parses the HTML content and extracts inquiry records.
 */
class HTMLParser
{
    private $htmlContent;
    private $reference;
    private $reportDate;
    private $inquiries = [];

    public function __construct($htmlContent)
    {
        $this->htmlContent = $htmlContent;
    }

    public function parse()
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($this->htmlContent);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // Extract Reference
        $reference_node = $xpath->query("//p[@class='ng-binding']")->item(0);
        if ($reference_node) {
            $this->reference = trim($reference_node->nodeValue);
        }

        // Extract Report Date
        $report_date_node = $xpath->query("//div[@class='re-data']//h3[text()='Report Date:']/following-sibling::p/ng")->item(0);
        if ($report_date_node) {
            $this->reportDate = trim($report_date_node->nodeValue);
        }

        if (empty($this->reference) || empty($this->reportDate)) {
            throw new Exception('Reference or Report Date not found in the HTML file.');
        }

        // Select the Inquiries table
        $table_nodes = $xpath->query("//div[@id='Inquiries']//table[contains(@class, 'rpt_content_table')]");
        if ($table_nodes->length == 0) {
            return; // No inquiries found
        }

        // Iterate over table rows
        $rows = $table_nodes->item(0)->getElementsByTagName('tr');
        foreach ($rows as $row_index => $row) {
            // Skip header row
            if ($row_index === 0) {
                continue;
            }

            $cells = $row->getElementsByTagName('td');
            if ($cells->length < 4) {
                continue;
            }

            $creditor_name = trim($cells->item(0)->nodeValue);
            $type_of_business = trim($cells->item(1)->nodeValue);
            $date_of_inquiry = trim($cells->item(2)->nodeValue);
            $credit_bureau = trim($cells->item(3)->nodeValue);

            $this->inquiries[] = [
                'reference' => $this->reference,
                'report_date' => $this->reportDate,
                'creditor_name' => $creditor_name,
                'type_of_business' => $type_of_business,
                'date_of_inquiry' => $date_of_inquiry,
                'credit_bureau' => $credit_bureau,
            ];
        }
    }

    public function getInquiries()
    {
        return $this->inquiries;
    }
}
?>
