<?php

/**
 * Email Test Script for BuySelles
 *
 * This script tests if the SMTP email configuration is working correctly.
 * Run this from command line: php test_email.php your-email@example.com
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

// Get test email from command line argument
$testEmail = $argv[1] ?? 'test@example.com';

echo "===========================================\n";
echo "  BuySelles Email Configuration Test\n";
echo "===========================================\n\n";

// Display current mail configuration
echo "Current Mail Configuration:\n";
echo "---------------------------\n";
echo 'Driver: '.Config::get('mail.driver')."\n";
echo 'Host: '.Config::get('mail.host')."\n";
echo 'Port: '.Config::get('mail.port')."\n";
echo 'Encryption: '.Config::get('mail.encryption')."\n";
echo 'Username: '.Config::get('mail.username')."\n";
echo 'From: '.Config::get('mail.from.address')."\n";
echo 'From Name: '.Config::get('mail.from.name')."\n\n";

// Test 1: Simple email send
echo "Test 1: Sending simple test email...\n";
try {
    Mail::raw('This is a test email from BuySelles. If you receive this, the SMTP configuration is working correctly.', function ($message) use ($testEmail) {
        $message->to($testEmail)
            ->subject('BuySelles Email Test - Simple');
    });
    echo "✓ Simple email sent successfully to: $testEmail\n\n";
} catch (\Exception $e) {
    echo "✗ Failed to send simple email\n";
    echo 'Error: '.$e->getMessage()."\n\n";
}

// Test 2: Email with HTML
echo "Test 2: Sending HTML test email...\n";
try {
    Mail::send([], [], function ($message) use ($testEmail) {
        $message->to($testEmail)
            ->subject('BuySelles Email Test - HTML')
            ->html('
                    <h1>BuySelles Email Test</h1>
                    <p>This is an <strong>HTML</strong> test email.</p>
                    <p>If you receive this, your email configuration is working!</p>
                    <hr>
                    <p style="color: #666;">Sent from BuySelles - '.date('Y-m-d H:i:s').'</p>
                ');
    });
    echo "✓ HTML email sent successfully to: $testEmail\n\n";
} catch (\Exception $e) {
    echo "✗ Failed to send HTML email\n";
    echo 'Error: '.$e->getMessage()."\n\n";
}

echo "===========================================\n";
echo "  Test Complete!\n";
echo "===========================================\n\n";

echo "Please check your inbox at: $testEmail\n";
echo "You should receive 2 test emails.\n\n";

echo "Troubleshooting Tips:\n";
echo "--------------------\n";
echo "1. Check spam/junk folder\n";
echo "2. Verify SMTP credentials in .env file\n";
echo "3. Check if your SMTP server allows connections from this IP\n";
echo "4. Verify firewall is not blocking port 465 (SSL)\n";
echo "5. Check Laravel logs: storage/logs/laravel.log\n\n";
