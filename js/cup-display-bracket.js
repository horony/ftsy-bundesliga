// Tournament Bracket JavaScript

let currentSeason = null;
let seasonSelectElement = null;

// Initialize bracket on page load
function loadBracket() {
    seasonSelectElement = seasonSelectElement || document.getElementById('season_select');
    if (!seasonSelectElement || !seasonSelectElement.value) {
        setTimeout(loadBracket, 100);
        return;
    }
    currentSeason = seasonSelectElement.value;
    fetchBracketData(currentSeason);
}

// Handle season change
function changeSeason() {
    seasonSelectElement = seasonSelectElement || document.getElementById('season_select');
    const newSeason = seasonSelectElement.value;
    
    if (newSeason !== currentSeason) {
        currentSeason = newSeason;
        showLoading();
        fetchBracketData(newSeason);
    }
}

// Show loading indicator
function showLoading() {
    document.getElementById('loading_indicator').style.display = 'block';
    document.getElementById('tournament_bracket').style.opacity = '0.5';
}

// Hide loading indicator
function hideLoading() {
    document.getElementById('loading_indicator').style.display = 'none';
    document.getElementById('tournament_bracket').style.opacity = '1';
}

// Fetch bracket data from server
function fetchBracketData(season) {
    fetch(`../php/get-tournament-bracket.php?season=${season}`)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                renderBracket(data);
            } else {
                showError('Fehler beim Laden des Turnierbaums');
            }
        })
        .catch(error => {
            hideLoading();
            showError('Netzwerkfehler beim Laden der Daten');
        });
}

let currentSeasonFromBackend = null;

// Render the tournament bracket
function renderBracket(data) {
    // Store current season from backend for link activation
    currentSeasonFromBackend = data.current_season;
    
    // Clear existing matches
    document.getElementById('playoff_matches').innerHTML = '';
    document.getElementById('quarter_matches').innerHTML = '';
    document.getElementById('semi_matches').innerHTML = '';
    document.getElementById('final_matches').innerHTML = '';
    
    // Reset all round subtitles to original text
    resetRoundSubtitles();
    
    // Render each round
    if (data.data.playoffs && data.data.playoffs.length > 0) {
        renderRound('playoff_matches', data.data.playoffs, true, 'playoffs');
    } else {
        document.getElementById('playoff_matches').innerHTML = '<div class="no_matches">Keine Playoff-Spiele gefunden</div>';
    }
    
    if (data.data.quarters && data.data.quarters.length > 0) {
        renderRound('quarter_matches', data.data.quarters, true, 'quarters');
    } else {
        document.getElementById('quarter_matches').innerHTML = '<div class="no_matches">Keine Viertelfinale gefunden</div>';
    }
    
    if (data.data.semis && data.data.semis.length > 0) {
        renderRound('semi_matches', data.data.semis, true, 'semis');
    } else {
        document.getElementById('semi_matches').innerHTML = '<div class="no_matches">Keine Halbfinale gefunden</div>';
    }
    
    if (data.data.final && data.data.final.length > 0) {
        renderRound('final_matches', data.data.final, false, 'final');
    } else {
        document.getElementById('final_matches').innerHTML = '<div class="no_matches">Kein Finale gefunden</div>';
    }
}

// Render matches for a specific round
function renderRound(containerId, matches, isTwoLegged, roundName) {
    const container = document.getElementById(containerId);
    
    // Update round subtitle with Spieltag information
    updateRoundSubtitle(roundName, matches, isTwoLegged);
    
    matches.forEach((match, index) => {
        const matchElement = createMatchElement(match, index + 1, isTwoLegged, roundName);
        container.appendChild(matchElement);
    });
    
    // Add footnotes for specific rounds
    addRoundFootnote(roundName, container);
}

// Create individual match element
function createMatchElement(match, matchNumber, isTwoLegged, roundName) {
    const matchDiv = document.createElement('div');
    matchDiv.className = 'match_card';
    matchDiv.setAttribute('data-match-id', match.match_id);
    matchDiv.setAttribute('data-round', roundName);
    
    // Create round-specific match title
    let matchTitle;
    switch(roundName) {
        case 'playoffs':
            matchTitle = `Playoffs #${matchNumber}`;
            break;
        case 'quarters':
            matchTitle = `Viertelfinale #${matchNumber}`;
            break;
        case 'semis':
            matchTitle = `Halbfinale #${matchNumber}`;
            break;
        case 'final':
            matchTitle = 'FINALE';
            break;
        default:
            matchTitle = `Match #${matchNumber}`;
    }
    
    matchDiv.innerHTML = `
        <div class="match_header">${matchTitle}</div>
        <div class="match_teams">
            ${createTeamSlot(match.home_team, match.scores, 'home', isTwoLegged, roundName, match)}
            ${createTeamSlot(match.away_team, match.scores, 'away', isTwoLegged, roundName, match)}
        </div>
    `;
    
    return matchDiv;
}

// Create team slot HTML
function createTeamSlot(team, scores, side, isTwoLegged, roundName, match) {
    let winnerClass = '';
    if (team.is_winner === true) {
        winnerClass = 'winner';
    } else if (team.is_winner === false) {
        winnerClass = 'loser';
    }
    
    const teamName = team.name === 'TBD' ? 'Noch offen' : team.name;
    const teamClass = team.name === 'TBD' ? 'tbd' : '';
    
    const totalScore = side === 'home' ? scores.home_total : scores.away_total;
    
    let legScoresHtml = '';
    if (isTwoLegged && scores.first_leg && scores.second_leg) {
        const firstLegScore = side === 'home' ? scores.first_leg.home : scores.first_leg.away;
        const secondLegScore = side === 'home' ? scores.second_leg.home : scores.second_leg.away;
        const firstLegOpponentScore = side === 'home' ? scores.first_leg.away : scores.first_leg.home;
        const secondLegOpponentScore = side === 'home' ? scores.second_leg.away : scores.second_leg.home;
        
        // Determine leg winners
        const firstLegWinnerClass = (firstLegScore > firstLegOpponentScore) ? 'leg_winner' : (firstLegScore < firstLegOpponentScore) ? 'leg_loser' : '';
        const secondLegWinnerClass = (secondLegScore > secondLegOpponentScore) ? 'leg_winner' : (secondLegScore < secondLegOpponentScore) ? 'leg_loser' : '';

        // Make leg scores clickable with underscore conversion (only for current season)
        const isCurrentSeason = currentSeasonFromBackend && currentSeason == currentSeasonFromBackend;
        
        if (isCurrentSeason) {
            const firstLegId = addUnderscores(scores.first_leg.match_id);
            const secondLegId = addUnderscores(scores.second_leg.match_id);
            const firstLegLink = scores.first_leg.match_id ? `onclick="openMatch('${firstLegId}')" style="cursor: pointer;" title="» Gehe zu Fantasy-Match"` : '';
            const secondLegLink = scores.second_leg.match_id ? `onclick="openMatch('${secondLegId}')" style="cursor: pointer;" title="» Gehe zu Fantasy-Match"` : '';
            
            legScoresHtml = `
                <div class="leg_scores">
                    <span class="leg_score clickable ${firstLegWinnerClass}" ${firstLegLink}>${firstLegScore || '-'}</span>
                    <span class="leg_score clickable ${secondLegWinnerClass}" ${secondLegLink}>${secondLegScore || '-'}</span>
                </div>
            `;
        } else {
            legScoresHtml = `
                <div class="leg_scores">
                    <span class="leg_score ${firstLegWinnerClass}">${firstLegScore || '-'}</span>
                    <span class="leg_score ${secondLegWinnerClass}">${secondLegScore || '-'}</span>
                </div>
            `;
        }
    } else if (scores.first_leg) {
        const firstLegScore = side === 'home' ? scores.first_leg.home : scores.first_leg.away;
        const firstLegOpponentScore = side === 'home' ? scores.first_leg.away : scores.first_leg.home;
        
        // Determine leg winner
        const firstLegWinnerClass = (firstLegScore > firstLegOpponentScore) ? 'leg_winner' : 
                                   (firstLegScore < firstLegOpponentScore) ? 'leg_loser' : '';
        
        const isCurrentSeason = currentSeasonFromBackend && currentSeason == currentSeasonFromBackend;
        
        if (isCurrentSeason) {
            const firstLegId = addUnderscores(scores.first_leg.match_id);
            const firstLegLink = scores.first_leg.match_id ? `onclick="openMatch('${firstLegId}')" style="cursor: pointer;" title="» Gehe zu Fantasy-Match"` : '';
            
            legScoresHtml = `
                <div class="leg_scores">
                    <span class="leg_score clickable ${firstLegWinnerClass}" ${firstLegLink}>${firstLegScore || '-'}</span>
                </div>
            `;
        } else {
            legScoresHtml = `
                <div class="leg_scores">
                    <span class="leg_score ${firstLegWinnerClass}">${firstLegScore || '-'}</span>
                </div>
            `;
        }
    }
    
    // Add event listeners for hover effects
    const teamSlotHtml = `
        <div class="team_slot ${winnerClass}" 
             data-team-id="${team.id}" 
             data-team-name="${team.name}"
             data-round="${roundName}"
             onmouseenter="highlightTeamMatches('${team.name}')" 
             onmouseleave="clearTeamHighlight()">
            <div class="team_name ${teamClass}">${teamName}</div>
            <div class="score_container">
                <div class="aggregate_score">${totalScore || '-'}</div>
                ${legScoresHtml}
            </div>
        </div>
    `;
    
    return teamSlotHtml;
}

// Open match details page
function openMatch(matchId) {
    if (matchId) {
        window.open(`https://fantasy-bundesliga.de/html/view_match.php?ID=${matchId}`, '_blank');
    }
}

// Add underscores to match ID (e.g., 12025063 to 1_2025_063)
function addUnderscores(matchId) {
    if (!matchId) return matchId;
    
    const idStr = matchId.toString();
    
    // Pattern: 12025063 -> 1_2025_063 or 12024001 -> 1_2024_001
    if (idStr.length === 8) {
        return `${idStr.substring(0, 1)}_${idStr.substring(1, 5)}_${idStr.substring(5)}`;
    }
    
    return matchId; // Return original if pattern doesn't match
}

// Reset all round subtitles to their original text
function resetRoundSubtitles() {
    const selectors = ['#round_playoffs .round_subtitle', '#round_quarters .round_subtitle', '#round_semis .round_subtitle', '#round_final .round_subtitle'];
    selectors.forEach(selector => {
        const element = document.querySelector(selector);
        if (element) {
            element.innerHTML = '';
        }
    });
}

// Add footnotes to specific rounds
function addRoundFootnote(roundName, container) {
    // Remove existing footnotes first
    const existingFootnote = container.querySelector('.round_footnote');
    if (existingFootnote) {
        existingFootnote.remove();
    }
    
    let footnoteText = '';
    
    switch(roundName) {
        case 'playoffs':
            footnoteText = '* Platz 7-10 in der Liga nach Spieltag 9. Platz 7 vs. Platz 10, Platz 8 vs. Platz 9.';
            break;
        case 'quarters':
            footnoteText = '* Platz 1-6 in der Liga nach Spieltag 9 + 2 Playoff-Sieger. Begegnungen werden ausgelost.';
            break;
        case 'semis':
            footnoteText = '* Sieger der Viertelfinale. Begegnungen werden ausgelost.';
            break;
        case 'final':
            footnoteText = '* Sieger der Halbfinale.';
            break;
        default:
            return;
    }
    
    if (footnoteText) {
        const footnoteDiv = document.createElement('div');
        footnoteDiv.className = 'round_footnote';
        footnoteDiv.textContent = footnoteText;
        container.appendChild(footnoteDiv);
    }
}

// Update round subtitle with Spieltag information
function updateRoundSubtitle(roundName, matches, isTwoLegged) {
    const subtitleElement = document.querySelector(`#round_${roundName} .round_subtitle`);
    if (!subtitleElement || matches.length === 0) return;
    
    // Get the clean original text (should already be reset)
    const originalText = subtitleElement.textContent;
    
    // Collect unique Spieltag rounds from matches
    const rounds = new Set();
    
    matches.forEach(match => {
        if (match.scores.first_leg && match.scores.first_leg.round) {
            rounds.add(match.scores.first_leg.round);
        }
        if (isTwoLegged && match.scores.second_leg && match.scores.second_leg.round) {
            rounds.add(match.scores.second_leg.round);
        }
    });
    
    if (rounds.size > 0) {
        const roundsArray = Array.from(rounds).sort((a, b) => a - b);
        let spieltagInfo = '';
        
        if (isTwoLegged && roundsArray.length >= 2) {
            spieltagInfo = `Spieltag ${roundsArray[0]} & ${roundsArray[roundsArray.length - 1]}`;
        } else if (roundsArray.length === 1) {
            spieltagInfo = `Spieltag ${roundsArray[0]}`;
        } else if (roundsArray.length > 1) {
            spieltagInfo = `Spieltag ${roundsArray[0]} & ${roundsArray[roundsArray.length - 1]}`;
        }
        
        // Set subtitle with Spieltag info only (no "Winners" text)
        if (spieltagInfo) {
            // Only make clickable for current season (same logic as leg scores)
            const isCurrentSeason = currentSeasonFromBackend && currentSeason == currentSeasonFromBackend;
            
            if (isCurrentSeason) {
                // For multiple rounds, create separate links for each Spieltag
                if (roundsArray.length > 1) {
                    const firstRound = roundsArray[0];
                    const lastRound = roundsArray[roundsArray.length - 1];
                    const clickableLink = `<a href="#" onclick="openSpieltag('${firstRound}')" style="color: #27ae60; text-decoration: underline; cursor: pointer;" title="» Gehe zu Bundesliga-Spieltag ${firstRound}">Spieltag ${firstRound}</a> & <a href="#" onclick="openSpieltag('${lastRound}')" style="color: #27ae60; text-decoration: underline; cursor: pointer;" title="» Gehe zu Bundesliga-Spieltag ${lastRound}">${lastRound}</a>`;
                    subtitleElement.innerHTML = clickableLink;
                } else {
                    // Single round - simple link
                    const clickableLink = `<a href="#" onclick="openSpieltag('${roundsArray[0]}')" style="color: #27ae60; text-decoration: underline; cursor: pointer;" title="» Gehe zu Bundesliga-Spieltag ${roundsArray[0]}">${spieltagInfo}</a>`;
                    subtitleElement.innerHTML = clickableLink;
                }
            } else {
                // For past seasons, show plain text without links
                subtitleElement.textContent = spieltagInfo;
            }
        }
    }
}

// Show error message
function showError(message) {
    const bracket = document.getElementById('tournament_bracket');
    bracket.innerHTML = `
        <div style="text-align: center; padding: 40px; color: #721c24; background: #f8d7da; border-radius: 8px;">
            <strong>Fehler:</strong> ${message}
        </div>
    `;
}

// Highlight all matches for a specific team across all tournament rounds
function highlightTeamMatches(teamName) {
    if (!teamName || teamName === 'TBD' || teamName === 'Noch offen') return;
    
    const bracket = document.getElementById('tournament_bracket');
    
    // Clear previous highlights
    clearTeamHighlight();
    
    // Find all team slots and matches for this team
    const teamSlots = document.querySelectorAll(`[data-team-name="${teamName}"]`);
    
    teamSlots.forEach(slot => {
        // Highlight the team slot
        slot.classList.add('team_highlighted');
        
        // Highlight the parent match card
        const matchCard = slot.closest('.match_card');
        if (matchCard) {
            matchCard.classList.add('team_highlighted');
        }
    });
    
    // Fade out match_teams and match_header sections that don't contain highlighted teams
    const allMatchCards = document.querySelectorAll('.match_card');
    
    allMatchCards.forEach(matchCard => {
        const hasHighlightedTeam = matchCard.querySelector('.team_highlighted');
        if (!hasHighlightedTeam) {
            const matchTeams = matchCard.querySelector('.match_teams');
            const matchHeader = matchCard.querySelector('.match_header');
            
            if (matchTeams) {
                matchTeams.classList.add('faded_out');
            }
            if (matchHeader) {
                matchHeader.classList.add('faded_out');
            }
        }
    });
}

// Clear team highlighting
function clearTeamHighlight() {
    
    // Remove highlights from all elements
    document.querySelectorAll('.team_highlighted').forEach(element => {
        element.classList.remove('team_highlighted');
    });
    
    // Remove fadeout from all elements
    document.querySelectorAll('.faded_out').forEach(element => {
        element.classList.remove('faded_out');
    });
}

// Function to open Bundesliga Spieltag page and navigate to specific round
function openSpieltag(roundNumber) {
    // Open spieltag_buli.php with round parameter in a new tab
    window.open(`spieltag_buli.php?round=${roundNumber}`, '_blank');
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only load if we're on the pokal page
    if (document.getElementById('tournament_bracket')) {
        loadBracket();
    }
});