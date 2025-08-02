const numRowsCols = 4;
const numTiles = Math.pow(numRowsCols, 2);
const gridBoard = document.getElementById('grid-board');
const message = document.getElementById('message');

let playInterval;
let solveTime = 0;
let movesCount = 0;
let background = {
    id: 1,
    name: "Bob's Burger's",
    path: "bob's_burgers.jpg"
};
let state;

window.onload = function () {
    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.get('action') === 'stats') {
        solveTime = urlParams.get('time');
        movesCount = urlParams.get('moves');
        background = JSON.parse(urlParams.get('bg'));
        state = 'end';

        setupGame();
        message.textContent = `🎉 You solved the puzzle and won! Time: ${solveTime}s | Moves: ${movesCount}`;
        const tiles = document.getElementsByClassName('tile');
        for (const tile of tiles) {
            tile.removeEventListener('click', moveTile);
            tile.classList.remove('moveablepiece');
            tile.style.display = 'none';
        }

        gridBoard.style.backgroundImage = `url("backgrounds/${background.path}")`;
        gridBoard.style.borderWidth = '4px';
    } else {
        setupGame();
    }
};

function setupGame() {
    const boardSize = 400;
    const tileSize = 100;
    let imgX = 0;
    let imgY = 0;

    document.getElementById('current_bg').value = JSON.stringify(background);
    for (let i = 1; i <= numTiles; i++) {
        const homeSquare = `square-${i}`;
        const div = document.createElement('div');
        div.className = 'tile';
        div.textContent = (i != numTiles) ? i : '';
        div.dataset.homeSquare = homeSquare;
        div.style.gridArea = homeSquare;

        if (i != numTiles) {
            div.style.backgroundImage = `url("backgrounds/${background.path}")`;
            div.style.backgroundRepeat = 'no-repeat';
            div.style.backgroundPositionX = `${imgX}px`;
            div.style.backgroundPositionY = `${imgY}px`;

            imgX = (imgX > (-boardSize + tileSize)) ? (imgX - tileSize) : 0;
            imgY = (imgX === 0) ? (imgY - tileSize) : imgY;
        } else {
            div.id = "empty-square";
        }

        gridBoard.appendChild(div);
    }

    setupGallery();

    const shuffleBtn = document.getElementById('shuffle-btn');
    shuffleBtn.addEventListener('click', shuffleTiles);
}

function setupGallery() {
    const galleryContainer = document.getElementById('gallery-container');
    const galleryClose = document.getElementById('gallery-close');
    const backgroundOpt = document.getElementById('bg-opt');
    const bgImages = document.getElementsByClassName('bg-img');
    const uploadMsg = document.getElementById('upload-msg');

    backgroundOpt.addEventListener('click', () => {
        uploadMsg.textContent = '';
        galleryContainer.style.display = 'block';
    });

    galleryClose.addEventListener('click', () => {
        galleryContainer.style.display = 'none';
        uploadMsg.textContent = '';

    });

    for (const img of bgImages) {
        img.addEventListener('click', function () {
            background = JSON.parse(this.dataset.bg);
            document.getElementById('current_bg').value = JSON.stringify(background);

            const tiles = document.getElementsByClassName('tile');
            for (const tile of tiles) {
                if (tile.id != "empty-square") {
                    tile.style.backgroundImage = `url("backgrounds/${background.path}")`;
                }
            }

            if (state == 'end') gridBoard.style.backgroundImage = `url("backgrounds/${background.path}")`;

            galleryContainer.style.display = 'none';
        });
    }
}

function shuffleTiles() {
    const numShifts = 500;
    const numMoveTypes = 4;
    const tiles = document.getElementsByClassName('tile');
    const emptyTile = document.getElementById('empty-square');

    state = '';
    message.innerHTML = '&nbsp;';
    gridBoard.style.borderWidth = '';
    gridBoard.style.backgroundImage = '';

    for (const tile of tiles) {
        tile.style.removeProperty('display');
        tile.style.transition = '';
    }

    for (let i = 1; i <= numShifts;) {
        const emptyGridArea = emptyTile.style.gridArea;
        const emptySquare = Number(emptyGridArea.replace('square-', ''));
        const moveType = Math.floor(Math.random() * numMoveTypes) + 1;


        if (moveType == 1 && emptySquare > numRowsCols) {  // Move tile above empty square down
            squareAbove = emptySquare - numRowsCols;
            gridAreaAbove = `square-${squareAbove}`;
            for (const tile of tiles) {
                if (tile.style.gridArea == gridAreaAbove) {
                    [tile.style.gridArea, emptyTile.style.gridArea] = [emptyTile.style.gridArea, tile.style.gridArea];
                    i++;
                    break;
                }
            }

        } else if (moveType == 2 && (emptySquare % numRowsCols != 0)) {  // Move tile to right of empty square left
            squareToRight = emptySquare + 1;
            gridAreaToRight = `square-${squareToRight}`;
            for (const tile of tiles) {
                if (tile.style.gridArea == gridAreaToRight) {
                    [tile.style.gridArea, emptyTile.style.gridArea] = [emptyTile.style.gridArea, tile.style.gridArea];
                    i++;
                    break;
                }
            }

        } else if (moveType == 3 && emptySquare <= (numTiles - numRowsCols)) {  // Move tile below empty square up
            squareBelow = emptySquare + numRowsCols;
            gridAreaBelow = `square-${squareBelow}`;
            for (const tile of tiles) {
                if (tile.style.gridArea == gridAreaBelow) {
                    [tile.style.gridArea, emptyTile.style.gridArea] = [emptyTile.style.gridArea, tile.style.gridArea];
                    i++;
                    break;
                }
            }

        } else if (moveType == 4 && (emptySquare % numRowsCols != 1)) {  // Move tile to left of empty square right
            squareToLeft = emptySquare - 1;
            gridAreaToLeft = `square-${squareToLeft}`;
            for (const tile of tiles) {
                if (tile.style.gridArea == gridAreaToLeft) {
                    [tile.style.gridArea, emptyTile.style.gridArea] = [emptyTile.style.gridArea, tile.style.gridArea];
                    i++;
                    break;
                }
            }
        }
    }

    if (isPuzzleSolved()) shuffleTiles();

    updateMoveablePieces();
    resetGame();
}

function moveTile(event) {
    const tile = event.currentTarget;
    const xShift = tile.dataset.xShift;
    const yShift = tile.dataset.yShift;
    const emptyTile = document.getElementById('empty-square');
    const bgSong = document.getElementById('bg-song');

    playSound('slide.mp3');
    tile.style.transition = 'transform 0.2s ease-out';
    tile.style.transform = `translate(${xShift}, ${yShift})`;
    setTimeout(() => {
        movesCount++;
        tile.style.transition = 'none';
        tile.style.removeProperty('transform');
        [tile.style.gridArea, emptyTile.style.gridArea] = [emptyTile.style.gridArea, tile.style.gridArea];
        updateMoveablePieces();

        if (isPuzzleSolved()) {
            clearInterval(playInterval);
            playSound('win.wav');

            setTimeout(() => {
                document.getElementById('game_time').value = solveTime;
                document.getElementById('game_moves').value = movesCount;
                document.getElementById('stats-btn').click();
            }, 2000);
        }
    }, 200);
}

function updateMoveablePieces() {
    const emptyTile = document.getElementById('empty-square');
    const emptyGridArea = emptyTile.style.gridArea;
    const emptySquare = Number(emptyGridArea.replace('square-', ''));
    const tiles = document.getElementsByClassName('tile');

    for (const tile of tiles) {
        if (tile.id == 'empty-square') continue;

        const tileGridArea = tile.style.gridArea;
        const tileSquare = Number(tileGridArea.replace('square-', ''));

        delete tile.dataset.xShift;
        delete tile.dataset.yShift;

        if (tileSquare + numRowsCols == emptySquare) {    // Tile is below empty square
            updateTile(tile, 0, 100);
        } else if (tileSquare - numRowsCols == emptySquare) {    // Tile is above empty square
            updateTile(tile, 0, -100);
        } else if (emptySquare % numRowsCols != 0 && tileSquare - 1 == emptySquare) {    // Tile is to the right of empty square
            updateTile(tile, -100, 0);
        } else if (emptySquare % numRowsCols != 1 && tileSquare + 1 == emptySquare) {    // Tile is to the left of empty square
            updateTile(tile, 100, 0);
        } else {
            tile.classList.remove('moveablepiece');
            tile.removeEventListener('click', moveTile);
        }
    }
}

function updateTile(tile, xShift, yShift) {
    tile.classList.add('moveablepiece');
    tile.addEventListener('click', moveTile);
    tile.dataset.xShift = `${xShift}px`;
    tile.dataset.yShift = `${yShift}px`;
}

function isPuzzleSolved() {
    const tiles = document.getElementsByClassName('tile');

    for (const tile of tiles) {
        if (tile.dataset.homeSquare != tile.style.gridArea) return false;
    }

    return true;
}

function resetGame() {
    solveTime = 0;
    movesCount = 0;

    const bgSong = document.getElementById('bg-song');
    bgSong.loop = true;
    bgSong.volume = 0.2;
    bgSong.play();

    playInterval = setInterval(() => {
        solveTime++;
    }, 1000);
}

function playSound(filename) {
    const audio = new Audio(`audio/${filename}`);
    audio.volume = 0.5;
    audio.play();
}