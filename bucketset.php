<?php
$host = "";
$keyid = "";
$secret = "";

//clear mc storage
$cmd = "mv ~/.mc/ ~/.mcbak/";
echo shell_exec($cmd);
$cmd = "rm -rf policytmp";
echo shell_exec($cmd);
$cmd = "rm -f mc";
echo shell_exec($cmd);
$cmd = "mkdir policytmp";
echo shell_exec($cmd);

if (exec("uname") == "Darwin") {
    $cmd = "wget https://dl.min.io/client/mc/release/darwin-amd64/mc";
    echo shell_exec($cmd);
    $cmd = "chmod +x mc";
    echo shell_exec($cmd);
} else {
    $cmd = "wget https://dl.min.io/client/mc/release/linux-amd64/mc";
    echo shell_exec($cmd);
    $cmd = "chmod +x mc";
    echo shell_exec($cmd);
}

$cmd = "./mc alias set s3 $host $keyid $secret";
echo shell_exec($cmd);

//get buckets
$cmd = "./mc ls s3";
$output = shell_exec($cmd);
$outputarr = explode("\n", $output);
$buckets = array();
foreach ($outputarr as $key => $value) {
    $output2arr = explode(" ", $value);
    $bucket = $output2arr[sizeof($output2arr) - 1];
    $bucket = str_replace("/", "", $bucket);
    if ($bucket != "") {
        array_push($buckets, $bucket);
    }
}

foreach ($buckets as $key => $bucket) {
    generatePolicyFile($bucket);
    $cmd = "./mc policy set-json policytmp/" . $bucket . ".json s3/" . $bucket;
    echo $cmd . "\n";
    echo shell_exec($cmd);
}

$cmd = "rm -f mc";
echo shell_exec($cmd);
$cmd = "rm -rf policytmp";
echo shell_exec($cmd);
$cmd = "rm -rf ~/.mc/";
echo shell_exec($cmd);
$cmd = "mv ~/.mcbak/ ~/.mc/";
echo shell_exec($cmd);

function generatePolicyFile($bucket) {
    $cmd = "rm -f policytmp/" . $bucket . ".json";
    echo shell_exec($cmd);
    $output = '{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Action": [
        "s3:GetObject"
      ],
      "Effect": "Allow",
      "Principal": {
        "AWS": [
          "*"
        ]
      },
      "Resource": [
        "arn:aws:s3:::' . $bucket . '/*"
      ],
      "Sid": ""
    }
  ]
}';
    file_put_contents("policytmp/" . $bucket . ".json", $output);
}
