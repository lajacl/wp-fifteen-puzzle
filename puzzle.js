setupGame();

function setupGame() {
    const numTiles = 15;
    const boardSize = 400;
    const tileSize = 100;
    const gridBoard = document.getElementById('grid-board');
    let imgX = 0;
    let imgY = 0;

    for (let i = 0; i <= numTiles; i++) {
        const homeSquare = `square-${i + 1}`;
        const div = document.createElement('div');
        div.className = 'tile';
        div.textContent = (i != numTiles) ? i + 1 : '';
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

    updateMoveablePieces();
}

function updateMoveablePieces() {
    const numRowsCols = 4;
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