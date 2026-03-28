<?php
session_start();
require_once 'inc/db.inc.php';
require_once 'inc/image.inc.php';

if (!isset($_SESSION['user_id'])) {
    $redirect = 'booking.php?event_id=' . intval($_GET['event_id'] ?? 0);
    header('Location: login.php?redirect=' . urlencode($redirect));
    exit;
}

$event_id = intval($_GET['event_id'] ?? 0);
if (!$event_id) {
    header('Location: events.php');
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare(
    "SELECT e.*, v.name AS venue_name, v.address AS venue_address, v.venue_id
     FROM events e
     JOIN venues v ON e.venue_id = v.venue_id
     WHERE e.event_id = ? AND e.is_active = 1"
);
$stmt->bind_param('i', $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    $conn->close();
    header('Location: events.php');
    exit;
}

$sec_stmt = $conn->prepare(
    "SELECT ss.section_id, ss.label, ss.price, ss.total_seats,
            COUNT(CASE WHEN s.status = 'available' THEN 1 END) AS avail_count
     FROM seat_sections ss
     LEFT JOIN seats s ON s.section_id = ss.section_id
     WHERE ss.event_id = ?
     GROUP BY ss.section_id
     ORDER BY ss.price DESC"
);
$sec_stmt->bind_param('i', $event_id);
$sec_stmt->execute();
$sections = $sec_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$sec_stmt->close();

$images = getAllEventImages($conn, $event_id);
$conn->close();

$dateStr = date('d M Y', strtotime($event['event_date']));
$timeStr = date('g:i A', strtotime($event['event_time']));
$mapImg = !empty($images['seatmap']) ? resolveImageSrc($images['seatmap']) : '';

function cleanLabel(string $label): string {
    $clean = preg_replace('/^Cat\s*\d+\s*[^A-Za-z0-9]*\s*/i', '', trim($label));
    return preg_replace('/^[^A-Za-z0-9]+/', '', $clean ?? '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Book Tickets &mdash; <?= htmlspecialchars($event['title']) ?> &mdash; PULSE</title>
    <?php include "inc/head.inc.php" ?>
</head>
<body>
    <?php include "inc/nav.inc.php" ?>

    <div class="booking-page-wrapper">
        <div class="container" style="max-width:1100px;">
            <div class="booking-steps" id="bookingSteps">
                <div class="booking-step active" data-step="1"><div class="step-num">1</div><span class="step-label">Tickets</span></div>
                <div class="booking-step" data-step="2"><div class="step-num">2</div><span class="step-label">Details</span></div>
                <div class="booking-step" data-step="3"><div class="step-num">3</div><span class="step-label">Payment</span></div>
                <div class="booking-step" data-step="4"><div class="step-num">4</div><span class="step-label">Confirm</span></div>
            </div>

            <div class="booking-event-bar">
                <div>
                    <div class="booking-event-name"><?= htmlspecialchars($event['title']) ?></div>
                    <div class="booking-event-meta"><?= htmlspecialchars($event['venue_name']) ?> &nbsp;&middot;&nbsp; <?= $dateStr ?>, <?= $timeStr ?></div>
                </div>
                <span class="tag-chip" style="border-color:rgba(248,244,238,0.25);color:rgba(248,244,238,0.75);background:transparent;">
                    <?= htmlspecialchars($event['category']) ?>
                </span>
            </div>

            <div class="booking-panel active" id="step1">
                <div class="booking-layout">
                    <div>
                        <div class="booking-form-section">
                            <div class="booking-form-title">Select Your Tickets</div>

                            <?php if ($mapImg): ?>
                            <div style="display:flex;justify-content:flex-end;margin-bottom:14px;">
                                <button type="button" class="btn btn-outline-accent" onclick="openMapModal()" style="display:inline-flex;align-items:center;gap:8px;padding:10px 16px;">
                                    View Seat Map
                                </button>
                            </div>
                            <?php endif; ?>

                            <div class="cat-qty-grid">
                                <?php foreach ($sections as $i => $sec):
                                    $soldOut = intval($sec['avail_count']) < 1;
                                ?>
                                <div class="cat-qty-row <?= $soldOut ? 'soldout' : '' ?>"
                                     data-section-id="<?= intval($sec['section_id']) ?>"
                                     data-price="<?= htmlspecialchars($sec['price']) ?>"
                                     data-avail="<?= intval($sec['avail_count']) ?>">
                                    <div class="cat-qty-info">
                                        <div class="cat-qty-badge">Cat <?= $i + 1 ?></div>
                                        <div class="cat-qty-name"><?= htmlspecialchars(cleanLabel($sec['label'])) ?></div>
                                        <div class="cat-qty-price">S$<?= number_format($sec['price'], 0) ?> <span>per ticket</span></div>
                                        <?php if ($soldOut): ?>
                                        <div class="cat-qty-avail soldout-text">Sold Out</div>
                                        <?php else: ?>
                                        <div class="cat-qty-avail"><?= number_format($sec['avail_count']) ?> tickets remaining</div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!$soldOut): ?>
                                    <div class="qty-control">
                                        <button type="button" class="qty-btn" onclick="changeQty(this, -1)">-</button>
                                        <span class="qty-val">0</span>
                                        <button type="button" class="qty-btn" onclick="changeQty(this, 1)">+</button>
                                    </div>
                                    <?php else: ?>
                                    <div class="soldout-badge">Sold Out</div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="booking-nav" style="justify-content:flex-end;">
                            <button class="btn-booking-next" id="toStep2" disabled>
                                Continue
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="order-summary-card">
                        <div class="order-summary-title">Order Summary</div>
                        <div id="orderLines"><div class="order-empty">Select tickets to begin</div></div>
                        <div class="order-total" id="orderTotal" style="display:none;">
                            <span>Total</span>
                            <span id="totalAmount">S$0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="booking-panel" id="step2">
                <div class="booking-layout">
                    <div>
                        <div class="booking-form-section">
                            <div class="booking-form-title">Your Details</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['fname'] ?? '') ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" placeholder="Last name">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" readonly>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phoneNum" placeholder="+65 9123 4567">
                                </div>
                            </div>
                        </div>
                        <div class="booking-nav">
                            <button class="btn-booking-back" id="back1">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M10 6H2M2 6L5 3M2 6L5 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                Back
                            </button>
                            <button class="btn-booking-next" id="toStep3">
                                Continue
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="order-summary-card">
                        <div class="order-summary-title">Order Summary</div>
                        <div id="orderLines2"></div>
                        <div class="order-total"><span>Total</span><span id="totalAmount2">S$0.00</span></div>
                    </div>
                </div>
            </div>

            <div class="booking-panel" id="step3">
                <div class="booking-layout">
                    <div>
                        <div class="booking-form-section">
                            <div class="booking-form-title">Payment Method</div>
                            <div class="payment-methods">
                                <button type="button" class="payment-pill active" data-method="paynow">PayNow</button>
                                <button type="button" class="payment-pill" data-method="inperson">Pay in Person</button>
                            </div>

                            <div id="paynowFields">
                                <div style="text-align:center;padding:28px 0 20px;">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?data=paynowsg://&size=180x180&color=5247b8&bgcolor=0d0d0d"
                                         alt="PayNow QR" style="border-radius:8px;margin-bottom:14px;">
                                    <p style="color:var(--pulse-muted);font-size:0.85rem;">Scan with your banking app.<br>Complete payment before confirming the booking.</p>
                                </div>
                            </div>

                            <div id="inpersonFields" style="display:none;">
                                <div style="text-align:center;padding:32px 0;">
                                    <p style="color:var(--pulse-muted);font-size:0.88rem;">Pay at the venue box office on the event day.<br>Your booking will be held under your account.</p>
                                </div>
                            </div>
                        </div>

                        <div class="booking-form-section">
                            <div class="booking-form-title">Booking Summary</div>
                            <div style="color:var(--pulse-muted);font-size:0.85rem;line-height:1.9;">
                                <div><strong style="color:var(--pulse-white);">Event:</strong> <?= htmlspecialchars($event['title']) ?></div>
                                <div><strong style="color:var(--pulse-white);">Venue:</strong> <?= htmlspecialchars($event['venue_name']) ?></div>
                                <div><strong style="color:var(--pulse-white);">Date:</strong> <?= $dateStr ?>, <?= $timeStr ?></div>
                                <div><strong style="color:var(--pulse-white);">Seats:</strong> Auto-assigned on confirmation</div>
                            </div>
                        </div>

                        <div class="booking-nav">
                            <button class="btn-booking-back" id="back2">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M10 6H2M2 6L5 3M2 6L5 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                Back
                            </button>
                            <button class="btn-booking-next" id="confirmBtn">
                                Confirm &amp; Book
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="order-summary-card">
                        <div class="order-summary-title">Order Summary</div>
                        <div id="orderLines3"></div>
                        <div class="order-total"><span>Total</span><span id="totalAmount3">S$0.00</span></div>
                    </div>
                </div>
            </div>

            <div class="booking-panel" id="step4">
                <div class="booking-success">
                    <div class="success-title">Booking Confirmed</div>
                    <div class="success-ref" id="bookingRef">PULSE-00000</div>
                    <div id="assignedSeatsDisplay" style="margin:12px 0 20px;font-size:0.82rem;color:var(--pulse-muted);line-height:1.8;"></div>
                    <p class="success-body">
                        Your booking has been confirmed. You can review it any time from your dashboard.
                    </p>
                    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
                        <a href="events.php" class="btn btn-accent" style="display:inline-flex;align-items:center;gap:8px;">Browse More Events</a>
                        <a href="dashboard.php" class="btn btn-outline-accent" style="display:inline-flex;align-items:center;gap:8px;">My Bookings</a>
                    </div>
                </div>
            </div>

            <form id="bookingForm" action="actions/process_booking.php" method="POST" style="display:none;">
                <input type="hidden" name="event_id" value="<?= $event_id ?>">
                <input type="hidden" name="payment" id="paymentInput" value="paynow">
                <input type="hidden" name="total" id="totalInput" value="0">
                <input type="hidden" name="selections" id="selectionsInput" value="">
            </form>
        </div>
    </div>

    <?php if ($mapImg): ?>
    <div class="seatmap-modal-overlay" id="seatMapModal" onclick="if(event.target===this)closeMapModal();">
        <div class="seatmap-modal">
            <div class="seatmap-modal-header">
                <div>
                    <div class="seatmap-modal-title"><?= htmlspecialchars($event['venue_name']) ?> &mdash; Seating Plan</div>
                    <div class="seatmap-modal-sub">For reference only. Layout may vary by event.</div>
                </div>
                <button class="seatmap-modal-close" onclick="closeMapModal()">X</button>
            </div>
            <div class="seatmap-modal-body">
                <img src="<?= htmlspecialchars($mapImg) ?>" alt="<?= htmlspecialchars($event['venue_name']) ?> seating plan" style="width:100%;height:auto;display:block;">
            </div>
        </div>
    </div>
    <style>
    .seatmap-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.88);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;opacity:0;pointer-events:none;transition:opacity 0.25s;}
    .seatmap-modal-overlay.open{opacity:1;pointer-events:all;}
    .seatmap-modal{background:var(--pulse-surface);border:1px solid var(--pulse-border);width:100%;max-width:820px;max-height:90vh;display:flex;flex-direction:column;transform:translateY(16px);transition:transform 0.25s;}
    .seatmap-modal-overlay.open .seatmap-modal{transform:translateY(0);}
    .seatmap-modal-header{display:flex;align-items:flex-start;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--pulse-border);flex-shrink:0;}
    .seatmap-modal-title{font-size:0.92rem;font-weight:600;color:var(--pulse-white);margin-bottom:3px;}
    .seatmap-modal-sub{font-size:0.7rem;color:var(--pulse-muted);}
    .seatmap-modal-close{background:none;border:none;color:var(--pulse-muted);cursor:pointer;font-size:1.1rem;padding:2px;}
    .seatmap-modal-body{overflow-y:auto;flex:1;background:#111;}
    </style>
    <?php endif; ?>

    <?php include "inc/footer.inc.php" ?>

    <script>
    const selections = {};
    let currentPayment = 'paynow';

    function currency(value) {
        return 'S$' + Number(value).toFixed(2);
    }

    function getRows() {
        return Array.from(document.querySelectorAll('.cat-qty-row'));
    }

    function totalQty() {
        return Object.values(selections).reduce((sum, item) => sum + item.qty, 0);
    }

    function totalAmount() {
        return Object.values(selections).reduce((sum, item) => sum + (item.qty * item.price), 0);
    }

    function changeQty(btn, delta) {
        const row = btn.closest('.cat-qty-row');
        const sectionId = row.dataset.sectionId;
        const avail = parseInt(row.dataset.avail, 10);
        const price = parseFloat(row.dataset.price);
        const name = row.querySelector('.cat-qty-name').textContent.trim();
        const current = selections[sectionId]?.qty || 0;
        const next = current + delta;

        if (next < 0) return;
        if (next > avail) return;
        if (delta > 0 && totalQty() >= 8) return;

        if (next === 0) {
            delete selections[sectionId];
        } else {
            selections[sectionId] = { section_id: parseInt(sectionId, 10), qty: next, price, name };
        }

        row.querySelector('.qty-val').textContent = String(next);
        renderSummary();
    }

    function renderSummary() {
        const items = Object.values(selections);
        const hasItems = items.length > 0;
        const linesHtml = hasItems
            ? items.map(item => `<div class="order-line"><span>${item.name} x ${item.qty}</span><span>${currency(item.qty * item.price)}</span></div>`).join('')
            : '<div class="order-empty">Select tickets to begin</div>';
        const total = totalAmount();

        ['orderLines', 'orderLines2', 'orderLines3'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerHTML = linesHtml;
        });

        ['totalAmount', 'totalAmount2', 'totalAmount3'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = currency(total);
        });

        const totalBlock = document.getElementById('orderTotal');
        if (totalBlock) totalBlock.style.display = hasItems ? 'flex' : 'none';

        document.getElementById('toStep2').disabled = !hasItems;
        document.getElementById('totalInput').value = total.toFixed(2);
        document.getElementById('selectionsInput').value = JSON.stringify(items.map(({ section_id, qty }) => ({ section_id, qty })));
    }

    function goToStep(step) {
        document.querySelectorAll('.booking-panel').forEach((panel, index) => {
            panel.classList.toggle('active', index + 1 === step);
        });
        document.querySelectorAll('.booking-step').forEach((item, index) => {
            const itemStep = index + 1;
            item.classList.remove('active', 'done');
            if (itemStep < step) item.classList.add('done');
            if (itemStep === step) item.classList.add('active');
        });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function initSteps() {
        document.getElementById('toStep2').addEventListener('click', () => goToStep(2));
        document.getElementById('back1').addEventListener('click', () => goToStep(1));
        document.getElementById('toStep3').addEventListener('click', () => goToStep(3));
        document.getElementById('back2').addEventListener('click', () => goToStep(2));
    }

    function initPaymentMethods() {
        document.querySelectorAll('.payment-pill').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.payment-pill').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                currentPayment = btn.dataset.method;
                document.getElementById('paymentInput').value = currentPayment;
                document.getElementById('paynowFields').style.display = currentPayment === 'paynow' ? 'block' : 'none';
                document.getElementById('inpersonFields').style.display = currentPayment === 'inperson' ? 'block' : 'none';
            });
        });
    }

    async function submitBooking() {
        const confirmBtn = document.getElementById('confirmBtn');
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Processing...';

        try {
            const form = document.getElementById('bookingForm');
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            });
            const data = await response.json();

            if (!data.success) {
                alert(data.message || 'Booking failed. Please try again.');
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = 'Confirm &amp; Book <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
                return;
            }

            document.getElementById('bookingRef').textContent = data.booking_ref || '';
            const assigned = Array.isArray(data.assigned_seats) ? data.assigned_seats : [];
            document.getElementById('assignedSeatsDisplay').innerHTML = assigned.length
                ? 'Assigned seats: ' + assigned.map(seat => `${seat.row_label}${seat.seat_num}`).join(', ')
                : '';
            goToStep(4);
        } catch (err) {
            alert('Booking failed. Please try again.');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = 'Confirm &amp; Book <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
        }
    }

    function openMapModal() {
        const modal = document.getElementById('seatMapModal');
        if (modal) modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeMapModal() {
        const modal = document.getElementById('seatMapModal');
        if (modal) modal.classList.remove('open');
        document.body.style.overflow = '';
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderSummary();
        initSteps();
        initPaymentMethods();
        document.getElementById('confirmBtn').addEventListener('click', submitBooking);
    });
    </script>
</body>
</html>
