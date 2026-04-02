<?php

/**
 * Generates a PDF e-ticket for a confirmed PULSE booking.
 *
 * @param array  $booking  Keys: booking_ref, event_title, venue_name, event_date,
 *                         event_time, seats (string[]), total, ticket_category (optional)
 * @param string $userName Full name of the ticket holder
 * @return string          Raw PDF binary (suitable for email attachment)
 */
function generateTicketPDF(array $booking, string $userName): string
{
    $autoloadPath = __DIR__ . '/../vendor/autoload.php';
    if (!is_file($autoloadPath)) {
        throw new RuntimeException('Composer autoload file is missing. Run composer install on the server.');
    }

    require_once $autoloadPath;

    if (!class_exists('FPDF')) {
        throw new RuntimeException('FPDF dependency is not available.');
    }
    // ── PDF Canvas (A5 landscape: 210 × 148 mm) ───────────────────────────────
    $pdf = new FPDF('L', 'mm', 'A5');
    $pdf->SetMargins(0, 0, 0);
    $pdf->SetAutoPageBreak(false);
    $pdf->AddPage();

    $W  = 210;
    $H  = 148;
    $lW = 132;
    $rW = $W - $lW;

    // ── Backgrounds ───────────────────────────────────────────────────────────
    $pdf->SetFillColor(13, 13, 13);
    $pdf->Rect(0, 0, $W, $H, 'F');

    $pdf->SetFillColor(82, 71, 184);
    $pdf->Rect(0, 0, $W, 13, 'F');

    $pdf->SetFillColor(23, 23, 23);
    $pdf->Rect($lW, 13, $rW, $H - 23, 'F');

    $pdf->SetFillColor(28, 24, 65);
    $pdf->Rect(0, $H - 10, $W, 10, 'F');

    // ── Header ────────────────────────────────────────────────────────────────
    $pdf->SetFont('Helvetica', 'B', 13);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetXY(5, 2.5);
    $pdf->Cell(28, 8, 'PULSE', 0, 0, 'L');

    $pdf->SetFont('Helvetica', '', 6);
    $pdf->SetTextColor(210, 205, 255);
    $pdf->SetXY(35, 3.5);
    $pdf->Cell($W - 40, 6, str_repeat('*** THIS IS YOUR TICKET ', 8), 0, 0, 'R');

    // Divider
    $pdf->SetDrawColor(50, 44, 110);
    $pdf->Line($lW, 13, $lW, $H - 10);

    // ── LEFT PANEL ────────────────────────────────────────────────────────────
    $pad = 6;

    $title = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', strtoupper($booking['event_title'] ?? ''));
    $pdf->SetFont('Helvetica', 'B', 15);
    $pdf->SetTextColor(248, 244, 238);
    $pdf->SetXY($pad, 16);
    $pdf->MultiCell($lW - $pad * 2, 8, $title, 0, 'L');

    $y = min($pdf->GetY() + 3, 60);

    // Venue
    $pdf->SetFont('Helvetica', '', 7);
    $pdf->SetTextColor(157, 150, 255);
    $pdf->SetXY($pad, $y);
    $pdf->Cell($lW - $pad * 2, 4, 'VENUE', 0, 2, 'L');
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetTextColor(248, 244, 238);
    $pdf->SetX($pad);
    $pdf->Cell($lW - $pad * 2, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $booking['venue_name'] ?? ''), 0, 2, 'L');

    $y = $pdf->GetY() + 3;

    // Date & Time
    $half = ($lW - $pad * 2) / 2;
    $pdf->SetFont('Helvetica', '', 7);
    $pdf->SetTextColor(157, 150, 255);
    $pdf->SetXY($pad, $y);
    $pdf->Cell($half, 4, 'DATE', 0, 0, 'L');
    $pdf->Cell($half, 4, 'TIME', 0, 2, 'L');
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetTextColor(248, 244, 238);
    $pdf->SetX($pad);
    $pdf->Cell($half, 5, $booking['event_date'] ?? '', 0, 0, 'L');
    $pdf->Cell($half, 5, $booking['event_time'] ?? '', 0, 2, 'L');

    $y = $pdf->GetY() + 3;

    // Seats
    $seatText = !empty($booking['seats']) ? implode(', ', (array)$booking['seats']) : 'Auto-assigned';
    $pdf->SetFont('Helvetica', '', 7);
    $pdf->SetTextColor(157, 150, 255);
    $pdf->SetXY($pad, $y);
    $pdf->Cell($lW - $pad * 2, 4, 'SEAT(S)', 0, 2, 'L');
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetTextColor(248, 244, 238);
    $pdf->SetX($pad);
    $pdf->MultiCell($lW - $pad * 2, 5, $seatText, 0, 'L');

    // ── RIGHT PANEL ───────────────────────────────────────────────────────────
    $rx  = $lW + 4;
    $ry  = 15;
    $rcw = $rW - 7;

    $fields = [
        ['NAME',        iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $userName)],
        ['BOOKING REF', $booking['booking_ref'] ?? ''],
        ['TICKET TYPE', $booking['ticket_category'] ?? 'General Admission'],
        ['TOTAL PRICE', 'S$' . number_format((float)($booking['total'] ?? 0), 2)],
    ];

    foreach ($fields as [$label, $value]) {
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetTextColor(157, 150, 255);
        $pdf->SetXY($rx, $ry);
        $pdf->Cell($rcw, 4, $label, 0, 2, 'L');
        $pdf->SetFont('Helvetica', 'B', 8.5);
        $pdf->SetTextColor(248, 244, 238);
        $pdf->SetX($rx);
        $pdf->Cell($rcw, 5, $value, 0, 2, 'L');
        $ry = $pdf->GetY() + 2;
    }

    // ── "Present at entrance" notice ──────────────────────────────────────────
    $ry += 4;
    $pdf->SetFillColor(82, 71, 184);
    $pdf->Rect($rx - 1, $ry, $rcw + 2, 14, 'F');
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetXY($rx, $ry + 1.5);
    $pdf->Cell($rcw, 4, 'PRESENT THIS TICKET', 0, 2, 'C');
    $pdf->SetFont('Helvetica', '', 6.5);
    $pdf->SetTextColor(210, 205, 255);
    $pdf->SetX($rx);
    $pdf->Cell($rcw, 4, 'AT THE ENTRANCE', 0, 0, 'C');

    // ── Bottom Strip ──────────────────────────────────────────────────────────
    $pdf->SetFont('Helvetica', '', 7);
    $pdf->SetTextColor(157, 150, 255);
    $pdf->SetXY(5, $H - 8);
    $ref = $booking['booking_ref'] ?? '';
    $pdf->Cell($W - 10, 6, "*** THIS IS YOUR TICKET *** THIS IS YOUR TICKET *** $ref", 0, 0, 'C');

    return $pdf->Output('S');
}




