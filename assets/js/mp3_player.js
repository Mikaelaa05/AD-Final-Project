const BASE_URL = window.BASE_URL || '/';

const songs = [
    {
        title: "seishun complex",
        file: BASE_URL + "assets/mp3/seishun complex.mp3",
        logo: BASE_URL + "assets/img/songs/seishun complex.jpg"
    },
    {
        title: "hitoribocchi tokyo",
        file: BASE_URL + "assets/mp3/hitoribocchi tokyo.mp3",
        logo: BASE_URL + "assets/img/songs/hitoribocchi tokyo.jpg"
    },
    {
        title: "Distortion!!",
        file: BASE_URL + "assets/mp3/Distortion!!.mp3",
        logo: BASE_URL + "assets/img/songs/Distortion!!.jpg"
    },
    {
        title: "Secret base",
        file: BASE_URL + "assets/mp3/Secret base.mp3",
        logo: BASE_URL + "assets/img/songs/Secret base.jpg"
    },
    {
        title: "Guitar, Loneliness and Blue Planet",
        file: BASE_URL + "assets/mp3/Guitar, Loneliness and Blue Planet.mp3",
        logo: BASE_URL + "assets/img/songs/Guitar, Loneliness and Blue Planet.jpg"
    },
    {
        title: "I can't sing a love song",
        file: BASE_URL + "assets/mp3/I can't sing a love song.mp3",
        logo: BASE_URL + "assets/img/songs/I can't sing a love song.jpg"
    },
    {
        title: "That band",
        file: BASE_URL + "assets/mp3/That band.mp3",
        logo: BASE_URL + "assets/img/songs/That band.jpg"
    },
    {
        title: "Karakara",
        file: BASE_URL + "assets/mp3/Karakara.mp3",
        logo: BASE_URL + "assets/img/songs/Karakara.jpg"
    },
    {
        title: "The Little Sea",
        file: BASE_URL + "assets/mp3/The Little Sea.mp3",
        logo: BASE_URL + "assets/img/songs/hitoribocchi tokyo.jpg"
    },
    {
        title: "What is wrong with",
        file: BASE_URL + "assets/mp3/What is wrong with.mp3",
        logo: BASE_URL + "assets/img/songs/What is wrong with.jpg"
    },
    {
        title: "Never forget",
        file: BASE_URL + "assets/mp3/Never forget.mp3",
        logo: BASE_URL + "assets/img/songs/Never forget.jpg"
    },
    {
        title: "If I could be a constellation",
        file: BASE_URL + "assets/mp3/If I could be a constellation.mp3",
        logo: BASE_URL + "assets/img/songs/If I could be a constellation.jpg"
    },
    {
        title: "Flashbacker",
        file: BASE_URL + "assets/mp3/Flashbacker.mp3",
        logo: BASE_URL + "assets/img/songs/Flashbacker.jpg"
    },
    {
        title: "Rockn'Roll, Morning Light Falls on You",
        file: BASE_URL + "assets/mp3/Rockn' Roll, Morning Light Falls on You.mp3",
        logo: BASE_URL + "assets/img/songs/Rockn'Roll, Morning Light Falls on You.jpg"
    },
    {
        title: "Into the Light",
        file: BASE_URL + "assets/mp3/Into the Light.mp3",
        logo: BASE_URL + "assets/img/songs/Into the Light.jpg"
    },
    {
        title: "Blue Spring and Western Sky",
        file: BASE_URL + "assets/mp3/Blue Spring and Western Sky.mp3",
        logo: BASE_URL + "assets/img/songs/Blue Spring and Western Sky.jpg"
    },
    {
        title: "Shine as usual",
        file: BASE_URL + "assets/mp3/Shine as usual.mp3",
        logo: BASE_URL + "assets/img/songs/Shine as usual.jpg"
    },
    {
        title: "Now, I'm going from underground",
        file: BASE_URL + "assets/mp3/Now, I'm going from underground.mp3",
        logo: BASE_URL + "assets/img/songs/Now, I'm going from underground.jpg"
    },
    {
        title: "Doppelganger",
        file: BASE_URL + "assets/mp3/Doppelganger.mp3",
        logo: BASE_URL + "assets/img/songs/Doppelganger.jpg"
    },
    {
        title: "Me and the three primary colors",
        file: BASE_URL + "assets/mp3/Me and the three primary colors.mp3",
        logo: BASE_URL + "assets/img/songs/Me and the three primary colors.jpg"
    },
    {
        title: "Chronostasis",
        file: BASE_URL + "assets/mp3/Chronostasis.mp3",
        logo: BASE_URL + "assets/img/songs/Chronostasis.jpg"
    },
    {
        title: "Re:Re:",
        file: BASE_URL + "assets/mp3/Re-Re-.mp3",
        logo: BASE_URL + "assets/img/songs/Re-Re-.jpg"
    },
    {
        title: "milky way",
        file: BASE_URL + "assets/mp3/milky way.mp3",
        logo: BASE_URL + "assets/img/songs/milky way.jpg"
    },
    {
        title: "Planet",
        file: BASE_URL + "assets/mp3/Planet.mp3",
        logo: BASE_URL + "assets/img/songs/Planet.jpg"
    },
    {
        title: "UNITE",
        file: BASE_URL + "assets/mp3/UNITE.mp3",
        logo: BASE_URL + "assets/img/songs/UNITE.jpg"
    },
    {
        title: "Bundle up your dreams",
        file: BASE_URL + "assets/mp3/Bundle up your dreams.mp3",
        logo: BASE_URL + "assets/img/songs/Bundle up your dreams.jpg"
    },
    {
        title: "I'm the only ghost",
        file: BASE_URL + "assets/mp3/I'm the only ghost.mp3",
        logo: BASE_URL + "assets/img/songs/I'm the only ghost.jpg"
    },
]

let current = 0, isPlaying = false, isLoop = false, isShuffle = false;
const audio = document.getElementById("audio");
const playBtn = document.getElementById("play-btn");
const pauseBtn = document.getElementById("pause-btn");
const nextBtn = document.getElementById("next-btn");
const prevBtn = document.getElementById("prev-btn");
const ffBtn = document.getElementById("ff-btn");
const rwBtn = document.getElementById("rewind-btn");
const shuffleBtn = document.getElementById("shuffle-btn");
const loopBtn = document.getElementById("loop-btn");
const progress = document.getElementById("progress");
const volume = document.getElementById("volume");
const songLogo = document.getElementById("song-logo");
const songTitle = document.getElementById("song-title");
const playlistBtn = document.getElementById("playlist-toggle");
const playlistDiv = document.getElementById("playlist");

function restoreState() {
    const state = JSON.parse(localStorage.getItem('mp3player-state') || '{}');
    if (state.current !== undefined) current = state.current;
    if (state.time !== undefined) audio.currentTime = state.time;
    if (state.volume !== undefined) audio.volume = state.volume;
    if (state.isLoop !== undefined) isLoop = state.isLoop;
    if (state.isShuffle !== undefined) isShuffle = state.isShuffle;
}

function saveState() {
    localStorage.setItem('mp3player-state', JSON.stringify({
        current,
        time: audio.currentTime,
        volume: audio.volume,
        isLoop,
        isShuffle
    }));
}

function loadSong(idx) {
    audio.src = songs[idx].file;
    songLogo.src = songs[idx].logo;
    songTitle.textContent = songs[idx].title;
    updatePlaylistHighlight();
    saveState();
}

function playSong() {
    audio.play();
    isPlaying = true;
    playBtn.style.display = "none";
    pauseBtn.style.display = "";
    songLogo.classList.add("spin");
}

function pauseSong() {
    audio.pause();
    isPlaying = false;
    playBtn.style.display = "";
    pauseBtn.style.display = "none";
    songLogo.classList.remove("spin");
}

function nextSong() {
    if (isShuffle) {
        let next;
        do {
            next = Math.floor(Math.random() * songs.length);
        } while (next === current && songs.length > 1);
        current = next;
    } else {
        current = (current + 1) % songs.length;
    }
    loadSong(current);
    playSong();
}

function prevSong() {
    if (isShuffle) {
        let prev;
        do {
            prev = Math.floor(Math.random() * songs.length);
        } while (prev === current && songs.length > 1);
        current  = prev;
    } else {
        current = (current - 1 + songs.length) % songs.length;
    }
    loadSong(current);
    playSong();
}

function updateProgress() {
    progress.value = (audio.currentTime / audio.duration) * 100 || 0;
    saveState();
}

function setProgress(e) {
    audio.currentTime = (e.target.value / 100) * audio.duration;
}

function setVolume(e) {
    audio.volume = e.target.value;
    saveState();
}

function toggleShuffle() {
    isShuffle = !isShuffle;
    shuffleBtn.classList.toggle("active", isShuffle);
    saveState();
}

function toggleLoop() {
    isLoop = !isLoop;
    loopBtn.classList.toggle("active", isLoop);
    saveState();
}

function fastForward() {
    audio.currentTime = Math.min(audio.currentTime + 5, audio.duration);
}

function rewind() {
    audio.currentTime = Math.max(audio.currentTime - 5, 0);
}

function updatePlaylistHighlight() {
    document.querySelectorAll(".playlist-item").forEach((el, idx) => {
        el.classList.toggle("active", idx === current);
    });
}

function showPlaylist() {
    playlistDiv.innerHTML = songs.map((s, i) =>
        `<div class="playlist-item${i === current ? ' active' : ''}" onclick="selectSong(${i})">${s.title}</div>`
    ).join("");
    playlistDiv.style.display = playlistDiv.style.display === "none" ? "block" : "none";
}

window.selectSong = function(idx) {
    current = idx;
    loadSong(current);
    playSong();
    //playlistDiv.style.display = "none";
};

audio.addEventListener("ended", () => {
    if (audio.loop) {
        // Loop One: let the browser handle repeating the current song
        return;
    }
    if (isLoop) {
        if (isShuffle) {
            // Shuffle + Loop: pick a random song (not the current one)
            let next;
            do {
                next = Math.floor(Math.random() * songs.length);
            } while (next === current && songs.length > 1);
            current = next;
        } else {
            // Loop Playlist: go to next song, wrap to first if at end
            current = (current + 1) % songs.length;
        }
        loadSong(current);
        playSong();
    } else {
        // No loop: just go to next song if not at end
        if (current < songs.length - 1) {
            if (isShuffle) {
                let next;
                do {
                    next = Math.floor(Math.random() * songs.length);
                } while (next === current && songs.length > 1);
                current = next;
            } else {
                current++;
            }
            loadSong(current);
            playSong();
        }
        // else: do nothing (stop at last song)
    }
});

audio.addEventListener("timeupdate", updateProgress);
progress.addEventListener("input", setProgress);
volume.addEventListener("input", setVolume);
playBtn.addEventListener("click", playSong);
pauseBtn.addEventListener("click", pauseSong);
nextBtn.addEventListener("click", nextSong);
prevBtn.addEventListener("click", prevSong);
ffBtn.addEventListener("click", fastForward);
rwBtn.addEventListener("click", rewind);
shuffleBtn.addEventListener("click", toggleShuffle);
loopBtn.addEventListener("click", toggleLoop);
playlistBtn.addEventListener("click", showPlaylist);

audio.volume = 1;
restoreState();
loadSong(current);

const player = document.getElementById("mp3-player");
let offsetx, offsety, isDragging = false;
player.addEventListener("mousedown", (e) => {
    if (e.target.closest(".player-header")) {
        isDragging = true;
        offsetx = e.clientX - player.offsetLeft;
        offsety = e.clientY - player.offsetTop;
        document.body.style.userSelect = "none";
    }
});
document.addEventListener("mousemove", (e) => {
    if (isDragging) {
        let newLeft = e.clientX - offsetx;
        let newTop = e.clientY - offsety;

        const playerRect = player.getBoundingClientRect();
        const minLeft = 0;
        const minTop = 0;
        const maxLeft = window.innerWidth - playerRect.width;
        const maxTop = window.innerHeight - playerRect.height;

        newLeft = Math.max(minLeft, Math.min(newLeft, maxLeft));
        newTop = Math.max(minTop, Math.min(newTop, maxTop));

        player.style.left = newLeft + "px";
        player.style.top = newTop + "px";
        player.style.right = "auto";
        player.style.bottom = "auto";
    }
});
document.addEventListener("mouseup", () => {
    isDragging = false;
    document.body.style.userSelect = "";
});

const minimizeBtn = document.getElementById("minimize-player");
minimizeBtn.addEventListener("click", () => {
    player.style.display = "none";
    // Optionally, show a small button to restore the player
    let restoreBtn = document.getElementById("restore-player-btn");
    if (!restoreBtn) {
        restoreBtn = document.createElement("button");
        restoreBtn.id = "restore-player-btn";
        restoreBtn.textContent = "🎵";
        restoreBtn.title = "Show Player";
        restoreBtn.style.position = "fixed";
        restoreBtn.style.bottom = "30px";
        restoreBtn.style.right = "30px";
        restoreBtn.style.zIndex = 10000;
        restoreBtn.style.background = "#ff80bf";
        restoreBtn.style.color = "#fff";
        restoreBtn.style.border = "none";
        restoreBtn.style.borderRadius = "50%";
        restoreBtn.style.width = "40px";
        restoreBtn.style.height = "40px";
        restoreBtn.style.fontSize = "1.5em";
        document.body.appendChild(restoreBtn);
        restoreBtn.addEventListener("click", () => {
            player.style.display = "";
            restoreBtn.remove();
        });
    }
});

window.addEventListener("beforeumload", () => {
    localStorage.setitem('mp3player-autoplay', isPlaying);
});

window.addEventListener("DOMContentLoaded", () => {
    const shouldAutoplay = localStorage.getItem('mp3player-autoplay') === "true";
    if (shouldAutoplay) {
        playSong();
    }
});

const loopOneBtn = document.getElementById("loop-one-btn");

// Hide Loop One button by default
loopOneBtn.style.display = "none";

function toggleLoop() {
    if (!isLoop && !audio.loop) {
        // 1st click: Enable Loop Playlist
        isLoop = true;
        audio.loop = false;
        loopBtn.classList.add("active");
        loopBtn.style.display = ""; // Show Loop button
        loopOneBtn.classList.remove("active");
        loopOneBtn.style.display = "none"; // Hide Loop One button
    } else if (isLoop && !audio.loop) {
        // 2nd click: Enable Loop One
        isLoop = false;
        audio.loop = true;
        loopBtn.classList.remove("active");
        loopBtn.style.display = "none"; // Hide Loop button
        loopOneBtn.classList.add("active");
        loopOneBtn.style.display = ""; // Show Loop One button
    } else {
        // 3rd click: Disable all loops
        isLoop = false;
        audio.loop = false;
        loopBtn.classList.remove("active");
        loopBtn.style.display = ""; // Show Loop button
        loopOneBtn.classList.remove("active");
        loopOneBtn.style.display = "none"; // Hide Loop One button
    }
    saveState();
}

// Only use loopBtn for toggling
loopBtn.addEventListener("click", toggleLoop);
loopOneBtn.addEventListener("click", toggleLoop);

window.addEventListener("beforeunload", () => {
    localStorage.setItem('mp3player-autoplay', isPlaying);
    localStorage.setItem('mp3player-time', audio.currentTime);
    localStorage.setItem('mp3player-current', current);
    localStorage.setItem('mp3player-volume', audio.volume);
});

window.addEventListener("DOMContentLoaded", () => {
    const shouldAutoplay = localStorage.getItem('mp3player-autoplay') === "true";
    const savedTime = parseFloat(localStorage.getItem('mp3player-time') || "0");
    const savedCurrent = parseInt(localStorage.getItem('mp3player-current') || "0");
    const savedVolume = parseFloat(localStorage.getItem('mp3player-volume') || "1");
    current = savedCurrent;
    loadSong(current);
    audio.currentTime = savedTime;
    audio.volume = savedVolume;
    volume.value = savedVolume;
    if (shouldAutoplay) {
        playSong();
    }
});

const currentTimeLabel = document.getElementById("current-time");
const totalTimeLabel = document.getElementById("total-time");

function formatTime(sec) {
    sec = Math.floor(sec);
    return `${Math.floor(sec/60)}:${(sec%60).toString().padStart(2,'0')}`;
}

audio.addEventListener("timeupdate", () => {
    currentTimeLabel.textContent = formatTime(audio.currentTime);
    progress.value = (audio.currentTime / audio.duration) * 100 || 0;
});
audio.addEventListener("loadedmetadata", () => {
    totalTimeLabel.textContent = formatTime(audio.duration);
});