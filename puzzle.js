const numRowsCols = 4;
const numTiles = Math.pow(numRowsCols, 2);
setupGame();

function setupGame() {
    const boardSize = 400;
    const tileSize = 100;
    const gridBoard = document.getElementById('grid-board');
    let imgX = 0;
    let imgY = 0;

    for (let i = 1; i <= numTiles; i++) {
        const homeSquare = `square-${i}`;
        const div = document.createElement('div');
        div.className = 'tile';
        div.textContent = (i != numTiles) ? i : '';
        div.dataset.homeSquare = homeSquare;
        div.style.gridArea = homeSquare;

        if (i != numTiles) {
            div.style.backgroundImage = 'url(images/background.jpg)';
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

    const shuffleBtn = document.getElementById('shuffle-btn');
    shuffleBtn.addEventListener('click', shuffleTiles);

    updateMoveablePieces();
}

function updateMoveablePieces() {
    const emptyTile = document.getElementById('empty-square');
    const emptyGridArea = emptyTile.style.gridArea;
    const emptySquare = Number(emptyGridArea.replace('square-', ''));
    const tiles = document.getElementsByClassName('tile');

    for (const tile of tiles) {
        if (tile.id == 'empty-square') return;

        const tileGridArea = tile.style.gridArea;
        const tileSquare = Number(tileGridArea.replace('square-', ''));

        if ((tileSquare + numRowsCols == emptySquare) || (tileSquare - numRowsCols == emptySquare) ||
            (emptySquare % 4 != 0 && tileSquare - 1 == emptySquare) || (emptySquare % 4 != 1 && tileSquare + 1 == emptySquare)) {
            tile.classList.add('moveablepiece');
            tile.addEventListener('click', moveTile);
        } else {
            tile.classList.remove('moveablepiece');
            tile.removeEventListener('click', moveTile);
        }
    }
}

function moveTile(event) {
    const tile = event.currentTarget;
    const emptyTile = document.getElementById('empty-square');
    [tile.style.gridArea, emptyTile.style.gridArea] = [emptyTile.style.gridArea, tile.style.gridArea];
    updateMoveablePieces();
}

function shuffleTiles() {
    const numShifts = 500;
    const numMoveTypes = 4;
    const tiles = document.getElementsByClassName('tile');
    const emptyTile = document.getElementById('empty-square');

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

    updateMoveablePieces();
}