<?php
require_once('./lib/parsedown.php');

function parseCSVList($csvFilePath = null, $delimiter = ',') {
    //parses links.csv file into an array
    if ($csvFilePath === null) {
        $csvFilePath = __DIR__ . '/links.csv';
    }

    $csvFile = fopen($csvFilePath, "r");
    $csvContent = fread($csvFile, filesize($csvFilePath));
    fclose($csvFile);

    return explode($delimiter, $csvContent);
}

function sortLinks($links, $linkOrder = null) {
    if(!empty($linkOrder) && count($links) == count($linkOrder)) {
        //remember home is equal to index
        $linksSorted = [];
        foreach($linkOrder as $link) {
            foreach ($links as $l) {
                if($link == pathinfo($l, PATHINFO_FILENAME)){
                    
                    $linksSorted[] .= $l;
                }
            }
        }
        return $linksSorted;
    }
    else return $links;
}

function generateStaticSite($sourceDir, $outputDir = './output')
{
    //some colors to help user understand
    $colorRed = "\033[31m";
    $colorGreen = "\033[32m";
    $colorReset = "\033[0m";

    $currentDirectory = getcwd();
    $sourceDir = __DIR__.'/pages/';

    echo $colorGreen."$sourceDir : $outputDir ".$colorReset.PHP_EOL;

    // Get all markdown files from the source directory
    $headerFile = $sourceDir . 'partials/header.md';
    $footerFile = $sourceDir . 'partials/footer.md';
    $cssFile = $sourceDir . 'css/main.css';

    $images = array_filter(scandir($sourceDir."images"), function($file) {
        return $file != '.' && $file != '..';
    });
    $bodyFiles = array_filter(scandir($sourceDir), fn($file) => pathinfo($file, PATHINFO_EXTENSION) === 'md');
    
    // Create a new Parsedown instance
    $parsedown = new Parsedown();
    
    // Create the output directory if it doesn't exist
    if (!file_exists($outputDir)) {
        mkdir($outputDir, 0777, true);
        
    }
    //create images output directory if it doesn't exist
    if (!file_exists($outputDir."/images")) {
        mkdir($outputDir."/images", 0777, true);
    }
    // Generate the links array dynamically
    $links = [];
    $csvList = parseCSVList();

    echo json_encode($csvList);
    foreach ($bodyFiles as $file) {
        $filename = pathinfo($file, PATHINFO_FILENAME);
        $links[] = "/$filename.html";
    }
    $links = sortLinks($links,$csvList);
    echo json_encode($links);
    // Generate HTML for header
    $headerContent = file_get_contents($headerFile);
    $htmlHeader = $parsedown->text($headerContent);

    // Generate HTML for footer
    $footerContent = file_get_contents($footerFile);
    $htmlFooter = $parsedown->text($footerContent);

    // Iterate through each markdown file
    foreach ($bodyFiles as $file) {
        echo "$file\n";
        $filename = pathinfo($file, PATHINFO_FILENAME);
        $htmlBodyTitle = $filename;

        // Generate HTML pages for body
        $bodyContent = file_get_contents(__DIR__.'/pages/'.$file);
        $htmlBody = $parsedown->text($bodyContent);

        // Generate the links directly in HTML
        $htmlLinks = '<ul>';
        foreach ($links as $link) {
            $name = pathinfo($link, PATHINFO_FILENAME);
            if ($name == 'index') {
                $name = 'home';
            }
            $htmlLinks .= "<li><a href=\"$link\">$name</a></li>";
        }
        $htmlLinks .= '</ul>';

        // Combine the sections into a complete HTML page

        /*
            this is the head of each html page you generate, edit this 
            when you need to 
        */
        $htmlContent = <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"
        rel="stylesheet">
                <!-----meta data----->
                <link rel="stylesheet" type="text/css" href="/main.css">
                <title>$htmlBodyTitle</title>
            </head>
            <body>
                $htmlHeader
                $htmlLinks
                $htmlBody
                $htmlFooter
            </body>
            </html>
            <!--do not edit below this line-->
        HTML;

        // Generate the output HTML file path
        $outputFile = $outputDir . '/'.$filename.'.html';

        // Write the complete HTML content to the output file
        file_put_contents($outputFile, $htmlContent);

        echo "Generated: $outputFile\n";
    }
    //move css file to output
    $cssOutput = file_get_contents($cssFile);
    file_put_contents($outputDir . '/'.'main.css', $cssOutput);
    echo "Generated: main.css\n";

    //move images to output
    foreach ($images as $image) {
        $sourceDir = $sourceDir.'images/';
        echo "$sourceDir\n";
        $outputDir = __DIR__.'/'.$outputDir.'/images';
        echo "$outputDir\n";
        echo $sourceDir . $image;
        // Check if it's a file and not a directory
        if (is_file($sourceDir . $image)) {
            // Move the file to the destination directory
            $success = copy($sourceDir . $image, $outputDir .'/'. $image);

            if ($success) {
                echo "File '$image' moved successfully.\n";
            } else {
                echo "Error moving file '$image'.\n";
            }
        }
    }
}

// Example usage
generateStaticSite('content', 'output');
?>
