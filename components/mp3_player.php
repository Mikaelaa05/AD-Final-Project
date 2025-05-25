<script>window.BASE_URL = "<?php echo BASE_URL; ?>";</script>

<div id="mp3-player" class="floating-player">
    <div class="player-header">
        <img id="song-logo" src="<?php echo BASE_URL; ?>assets/img/songs/seishun complex.jpg" class="song-logo"
            alt="Song Logo" />
        <span id="song-title">Select a song</span>
        <div class="header-buttons">
            <button id="playlist-toggle" title="Show Playlist">☰</button>
            <button id="minimize-player" title="Hide Player" style="margin-left:auto;">—</button>
        </div>
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
        <button id="loop-one-btn" title="Loop One" style="display:none;">🔂</button>
    </div>
    <div class="progress-slider">
        <input type="range" id="progress" min="0" max="100" value="0">
        <div class="progress-labels">
            <span id="current-time">0:00</span>
            <span id="total-time" style="float:right;">0:00</span>
        </div>
    </div>
    <div class="volume-slider">
        <span class="volume-icon" style="vertical-align: middle;">🔊</span>
        <input type="range" id="volume" min="0" max="1" step="0.01" value="1" style="width:90%;">
    </div>
    <div id="playlist" class="playlist-dropdown" style="display:none;"></div>
</div>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/mp3_player.css">
<script src="<?php echo BASE_URL; ?>assets/js/mp3_player.js"></script>