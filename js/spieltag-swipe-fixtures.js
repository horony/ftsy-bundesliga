// Swipe support for round navigation
(function() {
    var select = document.getElementById('spieltag-select');
    var touchStartX = null;

    function handleTouchStart(e) {
        if (e.touches.length === 1) {
            touchStartX = e.touches[0].clientX;
        }
    }
    function handleTouchEnd(e) {
        if (touchStartX === null) return;
        var touchEndX = e.changedTouches[0].clientX;
        var diff = touchEndX - touchStartX;
        if (Math.abs(diff) > 40) { // swipe threshold
            var idx = select.selectedIndex;
            if (diff < 0 && idx < select.options.length - 1) {
                // swipe left: next round
                select.selectedIndex = idx + 1;
                select.dispatchEvent(new Event('change'));
            } else if (diff > 0 && idx > 0) {
                // swipe right: previous round
                select.selectedIndex = idx - 1;
                select.dispatchEvent(new Event('change'));
            }
        }
        touchStartX = null;
    }
    // Attach to the container for better UX
    var container = document.getElementById('hilfscontainer');
    if (container && select) {
        container.addEventListener('touchstart', handleTouchStart, {passive:true});
        container.addEventListener('touchend', handleTouchEnd, {passive:true});
    }
})();
