// Allows users to change rounds on spieltag-page

document.addEventListener('DOMContentLoaded', function() {
    var picker = document.getElementById('spieltag-picker');
    var trigger = document.getElementById('spieltag-trigger');
    var grid = document.getElementById('spieltag-grid');
    var current = document.getElementById('spieltag-current');
    var hiddenSelect = document.getElementById('spieltag-select');
    var topxiContainer = document.getElementById('topxi-link-container');
    var topxiLink = document.getElementById('topxi-link');

    // Check if elements exist before proceeding
    if (!picker || !trigger || !grid || !current || !hiddenSelect) {
        console.warn('Spieltag picker elements not found');
        return;
    }

    // Function to update topxi link visibility and URL
    function updateTopxiLink(spieltag) {
        if (!topxiContainer || !topxiLink) return;
        
        var selectedSpieltag = parseInt(spieltag);
        var currentSpieltag = window.currentSpieltag || 1;
        
        if (selectedSpieltag < currentSpieltag) {
            // Show link for past rounds
            topxiContainer.style.display = 'block';
            
            // Create URL that will navigate to the specific round in topxi
            var currentSeasonId = window.currentSeasonId || '21795';
            var topxiUrl = 'topxi.php?nav=FABU&season=' + currentSeasonId + '&round=' + selectedSpieltag;
            topxiLink.href = topxiUrl;
            
            // Update link text to show specific round
            topxiLink.innerHTML = '<i class="fas fa-trophy"></i> Elf der Woche - Spieltag ' + selectedSpieltag;
        } else {
            // Hide link for current/future rounds
            topxiContainer.style.display = 'none';
        }
    }

    function openGrid(){ grid.style.display='grid'; grid.setAttribute('aria-hidden','false'); trigger.setAttribute('aria-expanded','true'); }
    function closeGrid(){ grid.style.display='none'; grid.setAttribute('aria-hidden','true'); trigger.setAttribute('aria-expanded','false'); }

    // initial state
    closeGrid();
    
    // Initialize topxi link visibility
    var initialSpieltag = current.textContent;
    updateTopxiLink(initialSpieltag);

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
      
      // Update topxi link
      updateTopxiLink(val);
      
      closeGrid();
    });

    // close on outside click or escape
    document.addEventListener('click', function(){ closeGrid(); });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeGrid(); });
    picker.addEventListener('click', function(e){ e.stopPropagation(); });
});