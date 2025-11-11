/* Make users waiver ranking sortable by drag and drop */
$(function() {
    $("#active_waivers").sortable({placeholder: "ui-state-highlight"});
    $("#active_waivers").disableSelection();
});

/* Touch support for mobile drag & drop */
function enableTouchSortable() {
    var container = document.getElementById('active_waivers');
    if (!container) return;
    let draggedItem = null;
    let lastHighlight = null;

    container.querySelectorAll('li').forEach(function(item) {
        item.setAttribute('touch-action', 'none');
        item.addEventListener('touchstart', function(e) {
            draggedItem = item;
            item.classList.add('dragging');
        });
        item.addEventListener('touchend', function(e) {
            item.classList.remove('dragging');
            if (lastHighlight) lastHighlight.classList.remove('ui-state-highlight');
            draggedItem = null;
            lastHighlight = null;
        });
        item.addEventListener('touchmove', function(e) {
            if (!draggedItem) return;
            var touch = e.touches[0];
            var target = document.elementFromPoint(touch.clientX, touch.clientY);
        if (target && target.parentElement === container && target !== draggedItem) {
            if (lastHighlight && lastHighlight !== target) {
                lastHighlight.classList.remove('ui-state-highlight');
            }
            target.classList.add('ui-state-highlight');
            lastHighlight = target;
            if (touch.clientY < target.getBoundingClientRect().top + target.offsetHeight / 2) {
                container.insertBefore(draggedItem, target);
            } else {
                container.insertBefore(draggedItem, target.nextSibling);
            }
        } else if (lastHighlight) {
            lastHighlight.classList.remove('ui-state-highlight');
            lastHighlight = null;
        }
        e.preventDefault();
        });
    });
}

/* DOM ready: Enable touch support */
document.addEventListener('DOMContentLoaded', function() {
    enableTouchSortable();
});

/* Submit final ranking to PHP script in order to save it the MySQL DB */
function save_waiver_ranking() {
    // Get active ranking
    var postData = $("#active_waivers").sortable('serialize');

    // POST to PHP script
    $.ajax({
        type: "POST",
        url: "../php/jobs/save-user-waiver-ranking.php",
        dataType: "json",
        traditional: true,
        data: {list: postData},
        success: function () {
            window.location.reload(true);
        },
        error: function (xhr, status, e) {
            window.location.reload(true);
            console.log(xhr.responseText);
        }
    });
}