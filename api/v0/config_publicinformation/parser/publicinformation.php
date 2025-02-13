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
 * Parses the HTML content and extracts the required data.
 */

class HTMLParser
{
    private $dom;
    private $xpath;

    public function __construct($htmlContent)
    {
        libxml_use_internal_errors(true);
        $this->dom = new DOMDocument();
        $this->dom->loadHTML($htmlContent);
        libxml_clear_errors();
        $this->xpath = new DOMXPath($this->dom);
    }

    public function parsePublicInformation()
    {
        $records = [];
        $reference = $this->extractSingleNode("//div[@class='re-data']/div[1]/p");
        $reportDate = $this->formatDate($this->extractSingleNode("//div[@class='re-data']/div[2]/p"));
        if (!$reference || !$reportDate) {
            throw new Exception("Reference or Report Date not found.");
        }

        $tableRows = $this->xpath->query("//*[@id='PublicInformation']//table[contains(@class, 'rpt_table4column')][1]/tbody/tr");
        if (!$tableRows || $tableRows->length === 0) {
            return []; // No records to process
        }

        foreach ($tableRows as $row) {
            $cells = $row->getElementsByTagName('td');
            if ($cells->length < 4) continue;

            // Clean the text from each column
            $type = $this->cleanText($cells->item(0)->nodeValue);
            $chesterPA = $this->cleanText($cells->item(1)->nodeValue);
            $allenTX = $this->cleanText($cells->item(2)->nodeValue);
            $atlantaGA = $this->cleanText($cells->item(3)->nodeValue);

            // Skip the record if ChesterPA, AllenTX, and AtlantaGA are all empty
            if (empty($chesterPA) && empty($allenTX) && empty($atlantaGA)) {
                continue;
            }

            $record = [
                'Reference' => $reference,
                'ReportDate' => $reportDate,
                'Type' => rtrim($type, ':'),
                'ChesterPA' => $chesterPA,
                'AllenTX' => $allenTX,
                'AtlantaGA' => $atlantaGA,
            ];

            $records[] = $record;
        }

        return $records;
    }

    private function extractSingleNode($query)
    {
        $node = $this->xpath->query($query)->item(0);
        return $node ? trim($node->nodeValue) : null;
    }

    private function formatDate($date)
    {
        $dateObj = DateTime::createFromFormat('m/d/Y', $date);
        return $dateObj ? $dateObj->format('Y-m-d') : null;
    }

    private function cleanText($text)
    {
        return trim(preg_replace('/\s+/', ' ', $text));
    }
}


?>
