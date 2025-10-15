<?php
// Quick script to check what test numbers are available
$token = 'EAAPZBZCzuP5DoBPXFdRVooaupNjJIWepEMgtQOcZAA5KfuldbOmrQNow7CwBaO9sNfckEXx3J1M4AyJZBR4DdILwogdZARGUPAIlkYYbbjiPZAPuxCmj2duNcXzrvS9SsnSL0qDwtfWZAlWJgmPBljYWvA4PCtfVE1sky2vppng0IG9oNjxaji2jiUZAtGNSg1j85QZDZD';
$wabaId = '779490248013026';

$url = "https://graph.facebook.com/v20.0/{$wabaId}/phone_numbers";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer {$token}"
]);

$response = curl_exec($ch);
curl_close($ch);

echo "Available phone numbers:\n";
echo $response;
?>