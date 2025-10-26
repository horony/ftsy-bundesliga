// Allows users to change rounds on spieltag-page

document.addEventListener('DOMContentLoaded', function() {
    var picker = document.getElementById('spieltag-picker');
    var trigger = document.getElementById('spieltag-trigger');
    var grid = document.getElementById('spieltag-grid');
    var current = document.getElementById('spieltag-current');
    var hiddenSelect = document.getElementById('spieltag-select');

    // Check if elements exist before proceeding
    if (!picker || !trigger || !grid || !current || !hiddenSelect) {
        console.warn('Spieltag picker elements not found');
        return;
    }

    function openGrid(){ grid.style.display='grid'; grid.setAttribute('aria-hidden','false'); trigger.setAttribute('aria-expanded','true'); }
    function closeGrid(){ grid.style.display='none'; grid.setAttribute('aria-hidden','true'); trigger.setAttribute('aria-expanded','false'); }

    // initial state
    closeGrid();

    trigger.addEventListener('click', function(e){
      e.stopPropagation();
      if (typeof e.detail === 'number' && e.detail === 0) return;
      if (grid.style.display === 'grid') closeGrid(); else openGrid();
    });

    // cell click
    grid.addEventListener('click', function(e){
      var btn = e.target.closest('.spieltag-cell');
      if (!btn) return;
      var val = btn.getAttribute('data-value');

      // update visual selection
      var prev = grid.querySelector('.spieltag-cell.selected');
      if (prev) prev.classList.remove('selected');
      btn.classList.add('selected');

      // update trigger and hidden select and dispatch change
      current.textContent = val;
      hiddenSelect.value = val;
      var evt = new Event('change', { bubbles: true });
      hiddenSelect.dispatchEvent(evt);
      closeGrid();
    });

    // close on outside click or escape
    document.addEventListener('click', function(){ closeGrid(); });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeGrid(); });
    picker.addEventListener('click', function(e){ e.stopPropagation(); });
});