<div id="mp3-player" class="floating-player">
    <div class="player-header">
        <img id="song-logo" src="<?php echo BASE_URL; ?>assets/img/songs/default_logo.jpg" class="song-logo"
            alt="Song Logo" />
        <span id="song-title">Select a song</span>
        <button id="playlist-toggle" title="Show Playlist">=</button>
    </div>
    <audio id="audio" preload="metadata"></audio>
    <div class="player-controls">
        <button id="prev-btn" title="Previous">⏮️</button>
        <button id="rewind-btn" title="Rewind 5s">⏪</button>
        <button id="play-btn" title="Play">▶️</button>
        <button id="pause-btn" style="display:none;" title="Pause">⏸️</button>
        <button id="ff-btn" title="Forward 5s">⏩</button>
        <button id="next-btn" title="Next">⏭️</button>
        <button id="shuffle-btn" title="Shuffle">🔀</button>
        <button id="loop-btn" title="Loop">🔁</button>
    </div>
    <input type="range" id="progress" min="0" max="100" value="0">
    <input type="range" id="volume" min="0" max="1" step="0.01" value="1">
    <div id="playlist" class="playlist-dropdown" style="display:none;"></div>
</div>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/mp3_player.css">
<script src="<?php echo BASE_URL; ?>assets/js/mp3_player.js"></script>