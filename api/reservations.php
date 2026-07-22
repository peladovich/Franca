<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/hours.php';

$errors = [];
$date = '';
$time = '';
$party = 2;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = t('res.err_session_expired');
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $party = max(1, (int) ($_POST['party_size'] ?? 2));
        $date = $_POST['date'] ?? '';
        $time = $_POST['time'] ?? '';
        $requests = trim($_POST['requests'] ?? '');

        if ($name === '') $errors[] = t('res.err_name');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = t('res.err_email');
        if (!$date || strtotime($date) < strtotime(date('Y-m-d'))) {
            $errors[] = t('res.err_date');
        } elseif (!$time) {
            $errors[] = t('res.err_time');
        } elseif (!in_array($time, reservation_slots_for_date($date), true)) {
            $errors[] = t('res.err_time_invalid');
        }

        if (!$errors) {
            $user = current_user();
            $stmt = db()->prepare("INSERT INTO reservations (user_id, name, email, phone, party_size, reservation_date, reservation_time, special_requests) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user['id'] ?? null, $name, $email, $phone, $party, $date, $time, $requests]);
            flash('success', t('res.success'));
            header('Location: ' . BASE_URL . '/reservations.php');
            exit;
        }
    }
}

$user = current_user();
$pageTitle = 'Franca - ' . t('res.title');
$active = 'reservations';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>

<header class="reveal mb-2xl text-center md:text-left">
  <span class="font-eyebrow text-[11px] text-accent-dark uppercase tracking-[0.2em] block mb-3"><?= e(t('res.location_name')) ?></span>
  <h1 class="font-display-lg-mobile md:font-display-lg text-display-lg-mobile md:text-display-lg text-primary mb-2"><?= e(t('res.title')) ?></h1>
  <p class="font-body-lg text-on-surface-variant max-w-2xl"><?= e(t('res.subtitle')) ?></p>
</header>

<div class="reveal-group grid grid-cols-1 lg:grid-cols-2 gap-lg mb-2xl">
  <!-- Location -->
  <div class="flex flex-col gap-lg">
    <div class="bg-surface-container-lowest rounded-xl p-md editorial-shadow">
      <h2 class="font-headline-sm text-headline-sm text-primary mb-md"><?= e(t('res.location_name')) ?></h2>
      <div class="flex items-start gap-2 mb-md text-on-surface-variant">
        <span class="material-symbols-outlined mt-1">location_on</span>
        <p class="font-body-md"><?= e(get_setting('address', 'Plaza Cagancha 1124, Montevideo, Uruguay')) ?></p>
      </div>
      <?php $mapLat = (float) get_setting('map_lat', '-34.9058'); $mapLng = (float) get_setting('map_lng', '-56.1914'); ?>
      <div class="w-full h-48 rounded-lg overflow-hidden mb-md">
        <iframe
          class="w-full h-full border-0"
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          title="Map showing Franca's location at <?= e(get_setting('address')) ?>"
          src="https://maps.google.com/maps?q=<?= $mapLat ?>,<?= $mapLng ?>&z=16&output=embed"
        ></iframe>
      </div>
      <a class="font-caption text-secondary underline" href="https://www.google.com/maps/dir/?api=1&destination=<?= $mapLat ?>,<?= $mapLng ?>" target="_blank" rel="noopener"><?= e(t('res.get_directions')) ?></a>
      <div class="grid grid-cols-2 gap-md pt-2 border-t border-outline-variant/30">
        <div>
          <h3 class="font-label-md text-primary mb-1"><?= e(t('res.hours')) ?></h3>
          <ul class="font-body-md text-on-surface-variant space-y-1 text-sm">
            <li><?= e(t('res.day_mon_fri')) ?>: <?= e(format_hours_range(get_setting('hours_mon_fri'), current_locale())) ?></li>
            <li><?= e(t('res.day_sat')) ?>: <?= e(format_hours_range(get_setting('hours_sat'), current_locale())) ?></li>
            <li><?= e(t('res.day_sun')) ?>: <?= e(format_hours_range(get_setting('hours_sun'), current_locale())) ?></li>
          </ul>
        </div>
        <div>
          <h3 class="font-label-md text-primary mb-1"><?= e(t('res.contact')) ?></h3>
          <p class="font-body-md text-on-surface-variant text-sm"><?= e(get_setting('email')) ?></p>
          <p class="font-body-md text-on-surface-variant text-sm"><?= e(get_setting('phone')) ?></p>
        </div>
      </div>
    </div>
    <div class="flex flex-wrap gap-2">
      <span class="inline-flex items-center gap-1 px-3 py-1 bg-secondary-container/50 text-on-secondary-container rounded-full font-label-md text-sm"><span class="material-symbols-outlined text-sm">diversity_3</span><?= e(t('res.lgbtq_friendly')) ?></span>
      <span class="inline-flex items-center gap-1 px-3 py-1 bg-secondary-container/50 text-on-secondary-container rounded-full font-label-md text-sm"><span class="material-symbols-outlined text-sm">laptop_mac</span><?= e(t('res.coworking_friendly')) ?></span>
      <span class="inline-flex items-center gap-1 px-3 py-1 bg-secondary-container/50 text-on-secondary-container rounded-full font-label-md text-sm"><span class="material-symbols-outlined text-sm">pets</span><?= e(t('res.pet_friendly')) ?></span>
    </div>
  </div>

  <!-- Booking form -->
  <div class="bg-surface-container-lowest rounded-xl p-md md:p-lg editorial-shadow">
    <h2 class="font-headline-md text-headline-md text-primary mb-2"><?= e(t('res.book_table')) ?></h2>
    <p class="font-body-md text-on-surface-variant mb-lg"><?= e(t('res.book_subtitle')) ?></p>

    <?php if ($errors): ?>
      <div class="bg-error-container text-on-error-container rounded-lg p-3 mb-md text-sm">
        <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
      <?= csrf_field() ?>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="font-label-md text-primary block mb-1"><?= e(t('res.full_name')) ?></label>
          <input name="name" type="text" required value="<?= e($user['name'] ?? '') ?>" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md" placeholder="<?= e(t('res.your_name_placeholder')) ?>">
        </div>
        <div>
          <label class="font-label-md text-primary block mb-1"><?= e(t('res.email')) ?></label>
          <input name="email" type="email" required value="<?= e($user['email'] ?? '') ?>" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md" placeholder="you@example.com">
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="font-label-md text-primary block mb-1"><?= e(t('res.phone')) ?></label>
          <input name="phone" type="tel" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md" placeholder="+598 ...">
        </div>
        <div>
          <label class="font-label-md text-primary block mb-1"><?= e(t('res.guests')) ?></label>
          <select name="party_size" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
            <?php for ($n = 1; $n <= 8; $n++): ?>
              <option value="<?= $n ?>" <?= $n === $party ? 'selected' : '' ?>><?= $n ?> <?= e($n === 1 ? t('res.person') : t('res.people')) ?></option>
            <?php endfor; ?>
          </select>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="font-label-md text-primary block mb-1"><?= e(t('res.date')) ?></label>
          <input id="res-date" name="date" type="date" required min="<?= date('Y-m-d') ?>" value="<?= e($date) ?>" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
        </div>
        <div>
          <label class="font-label-md text-primary block mb-1"><?= e(t('res.preferred_time')) ?></label>
          <select id="res-time" name="time" required data-selected="<?= e($time) ?>" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
            <option value=""><?= e(t('res.select_date_first')) ?></option>
          </select>
          <p id="res-closed-notice" class="font-caption text-error mt-1 hidden"><?= e(t('res.closed_notice')) ?></p>
        </div>
      </div>
      <div>
        <label class="font-label-md text-primary block mb-1"><?= e(t('res.special_requests')) ?></label>
        <textarea name="requests" rows="3" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md" placeholder="<?= e(t('res.special_requests_placeholder')) ?>"></textarea>
      </div>
      <button class="btn-lift w-full bg-accent text-on-accent py-4 rounded-full font-label-md uppercase tracking-widest" type="submit"><?= e(t('res.confirm')) ?></button>
    </form>
  </div>
</div>

<script>
(function () {
  var slotsByWeekday = <?= json_encode(reservation_slots()) ?>;
  var locale = <?= json_encode(current_locale() === 'es' ? 'es-UY' : 'en-US') ?>;
  var closedLabel = <?= json_encode(t('res.closed')) ?>;
  var selectDateFirstLabel = <?= json_encode(t('res.select_date_first')) ?>;

  var dateInput = document.getElementById('res-date');
  var timeSelect = document.getElementById('res-time');
  var closedNotice = document.getElementById('res-closed-notice');

  function formatSlot(hhmm) {
    var parts = hhmm.split(':').map(Number);
    var d = new Date(2000, 0, 1, parts[0], parts[1]);
    return d.toLocaleTimeString(locale, { hour: 'numeric', minute: '2-digit' });
  }

  function isoWeekday(dateStr) {
    // JS getDay(): 0=Sun..6=Sat. Our slots are keyed 1=Mon..7=Sun (ISO).
    var jsDay = new Date(dateStr + 'T00:00:00').getDay();
    return jsDay === 0 ? 7 : jsDay;
  }

  function refreshTimeOptions() {
    var previouslySelected = timeSelect.dataset.selected || timeSelect.value;
    timeSelect.innerHTML = '';

    if (!dateInput.value) {
      var placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.textContent = selectDateFirstLabel;
      timeSelect.appendChild(placeholder);
      closedNotice.classList.add('hidden');
      return;
    }

    var weekday = isoWeekday(dateInput.value);
    var slots = slotsByWeekday[weekday] || [];

    if (slots.length === 0) {
      var closedOpt = document.createElement('option');
      closedOpt.value = '';
      closedOpt.textContent = closedLabel;
      timeSelect.appendChild(closedOpt);
      closedNotice.classList.remove('hidden');
      return;
    }

    closedNotice.classList.add('hidden');
    slots.forEach(function (slot) {
      var opt = document.createElement('option');
      opt.value = slot;
      opt.textContent = formatSlot(slot);
      if (slot === previouslySelected) opt.selected = true;
      timeSelect.appendChild(opt);
    });
  }

  dateInput.addEventListener('change', refreshTimeOptions);
  refreshTimeOptions();
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
