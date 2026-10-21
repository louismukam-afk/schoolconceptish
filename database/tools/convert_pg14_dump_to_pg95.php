<?php

if (PHP_SAPI !== 'cli' || $argc !== 3) {
    fwrite(STDERR, "Usage: php convert_pg14_dump_to_pg95.php source.sql destination.sql\n");
    exit(1);
}

$input = fopen($argv[1], 'rb');
if (!$input) {
    exit(1);
}
$output = fopen($argv[2], 'xb');
if (!$output) {
    fclose($input);
    exit(1);
}

$inCopy = false;
$inSequence = false;
$settings = 0;
$sequenceTypes = 0;
$copyBlocks = 0;
$dataHash = hash_init('sha256');

while (($line = fgets($input)) !== false) {
    $statement = rtrim($line, "\r\n");
    // COPY rows are opaque data: preserve their bytes without SQL replacements.
    if ($inCopy) {
        hash_update($dataHash, $line);
        if ($statement === '\\.') {
            $inCopy = false;
        }
    } else {
        if ($statement === 'SET idle_in_transaction_session_timeout = 0;' ||
            $statement === 'SET default_table_access_method = heap;') {
            $settings++;
            continue;
        }
        if (strpos($statement, 'CREATE SEQUENCE ') === 0) {
            $inSequence = true;
        }
        if ($inSequence && $statement === '    AS integer') {
            $sequenceTypes++;
            continue;
        }
        if ($inSequence && substr($statement, -1) === ';') {
            $inSequence = false;
        }
        if (strpos($statement, 'COPY ') === 0 && substr($statement, -11) === 'FROM stdin;') {
            $inCopy = true;
            $copyBlocks++;
        }
    }
    if (fwrite($output, $line) !== strlen($line)) {
        fwrite(STDERR, "Write failed. The output file is incomplete.\n");
        exit(1);
    }
}

$complete = feof($input) && !$inCopy && !$inSequence;
fclose($input);
fclose($output);
if (!$complete) {
    fwrite(STDERR, "Incomplete dump. Do not import the output file.\n");
    exit(1);
}
echo 'Removed settings: '.$settings.PHP_EOL;
echo 'Removed sequence type clauses: '.$sequenceTypes.PHP_EOL;
echo 'Preserved COPY blocks: '.$copyBlocks.PHP_EOL;
echo 'COPY data SHA256: '.hash_final($dataHash).PHP_EOL;
