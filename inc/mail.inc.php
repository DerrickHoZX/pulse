<?php
require_once __DIR__ . '/mail_config.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

function pulseSendMail(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): array {
    if (!MAIL_ENABLED) {
        return ['success' => false, 'message' => 'Mail is disabled in configuration.'];
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->Port = MAIL_PORT;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody ?: strip_tags(str_replace(['<br>', '<br/>', '<br />'], PHP_EOL, $htmlBody));
        $mail->send();

        return ['success' => true, 'message' => 'Mail sent.'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $mail->ErrorInfo ?: $e->getMessage()];
    }
}

function buildBookingConfirmationMail(array $booking): array {
    $subject = 'Your PULSE booking is confirmed - ' . $booking['booking_ref'];
    $seatText = !empty($booking['seats']) ? htmlspecialchars(implode(', ', $booking['seats'])) : 'Auto-assigned';

    $html = '
        <div style="font-family:Arial,sans-serif;background:#0d0d0d;color:#f8f4ee;padding:24px;">
            <div style="max-width:640px;margin:0 auto;background:#171717;border:1px solid #2a2a2a;padding:28px;">
                <h1 style="margin:0 0 12px;font-size:24px;">Booking Confirmed</h1>
                <p style="margin:0 0 20px;color:#b8b0a7;">Thanks for booking with PULSE. Your tickets are confirmed.</p>
                <div style="padding:16px;border:1px solid #2a2a2a;background:#101010;margin-bottom:20px;">
                    <div style="font-size:12px;letter-spacing:0.12em;text-transform:uppercase;color:#9d96ff;margin-bottom:8px;">Booking Reference</div>
                    <div style="font-size:20px;font-weight:700;">' . htmlspecialchars($booking['booking_ref']) . '</div>
                </div>
                <table style="width:100%;border-collapse:collapse;color:#f8f4ee;">
                    <tr><td style="padding:8px 0;color:#b8b0a7;">Event</td><td style="padding:8px 0;text-align:right;">' . htmlspecialchars($booking['event_title']) . '</td></tr>
                    <tr><td style="padding:8px 0;color:#b8b0a7;">Venue</td><td style="padding:8px 0;text-align:right;">' . htmlspecialchars($booking['venue_name']) . '</td></tr>
                    <tr><td style="padding:8px 0;color:#b8b0a7;">Date</td><td style="padding:8px 0;text-align:right;">' . htmlspecialchars($booking['event_date']) . '</td></tr>
                    <tr><td style="padding:8px 0;color:#b8b0a7;">Time</td><td style="padding:8px 0;text-align:right;">' . htmlspecialchars($booking['event_time']) . '</td></tr>
                    <tr><td style="padding:8px 0;color:#b8b0a7;">Seats</td><td style="padding:8px 0;text-align:right;">' . $seatText . '</td></tr>
                    <tr><td style="padding:8px 0;color:#b8b0a7;">Payment</td><td style="padding:8px 0;text-align:right;">' . htmlspecialchars($booking['payment_label']) . '</td></tr>
                    <tr><td style="padding:8px 0;color:#b8b0a7;">Total</td><td style="padding:8px 0;text-align:right;">S$' . htmlspecialchars(number_format((float) $booking['total'], 2)) . '</td></tr>
                </table>
                <p style="margin:24px 0 0;color:#b8b0a7;">You can review your booking any time from your PULSE dashboard.</p>
            </div>
        </div>';

    $text = "Booking Confirmed\n"
        . "Reference: {$booking['booking_ref']}\n"
        . "Event: {$booking['event_title']}\n"
        . "Venue: {$booking['venue_name']}\n"
        . "Date: {$booking['event_date']}\n"
        . "Time: {$booking['event_time']}\n"
        . "Seats: " . (!empty($booking['seats']) ? implode(', ', $booking['seats']) : 'Auto-assigned') . "\n"
        . "Payment: {$booking['payment_label']}\n"
        . "Total: S$" . number_format((float) $booking['total'], 2) . "\n";

    return ['subject' => $subject, 'html' => $html, 'text' => $text];
}
