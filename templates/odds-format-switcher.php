<div class="bbfootball-odds-switcher">
  <p>Odds Format</p>
  <div class="switch">
    <input class="switch-input" id="odds-format" type="checkbox" name="odds-format">
    <label class="switch-paddle" for="odds-format">
      <span class="switch-<?php echo (get_option('bbf_odds_format') == 'decimal') ? 'in' : ''; ?>active" aria-hidden="true">Decimal</span>
      <span class="switch-<?php echo (get_option('bbf_odds_format') == 'fractional') ? 'in' : ''; ?>active" aria-hidden="true">Fractional</span>
    </label>
  </div>
  <div class="last-update">
    <span class="bbfootball-ajax" last_update="true"></span>
  </div>
</div>
