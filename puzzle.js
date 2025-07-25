let emptySquare = { row: 3, col: 3 };

setupGame();

function setupGame() {
    const numTiles = 15;
    const boardSize = 400;
    const tileSize = 100;
    const numRowsCols = 4;
    const gridBoard = document.getElementById('grid-board');
    let imgX = 0;
    let imgY = 0;

    for (let i = 0; i < numTiles; i++) {
        let div = document.createElement('div');
        div.className = 'tile';
        div.textContent = i + 1;
        div.dataset.row = Math.floor(i / numRowsCols);
        div.dataset.col = i % numRowsCols;
        div.style.backgroundPositionX = `${imgX}px`;
        div.style.backgroundPositionY = `${imgY}px`;

        imgX = (imgX > (-boardSize + tileSize)) ? (imgX - tileSize) : 0;
        imgY = (imgX === 0) ? (imgY - tileSize) : imgY;

        gridBoard.appendChild(div);
    }

    emptySquare = { row: 3, col: 3 };
    updateMoveablePieces();
}

function updateMoveablePieces() {
    const tiles = document.getElementsByClassName('tile');
    for (const tile of tiles) {
        let tileRow = Number(tile.dataset.row);
        let tileCol = Number(tile.dataset.col);

        if ((tileRow == emptySquare.row && (tileCol + 1 == emptySquare.col || tileCol - 1 == emptySquare.col)) ||
            (tileCol == emptySquare.col && (tileRow + 1 == emptySquare.row || tileRow - 1 == emptySquare.row))) {
            tile.classList.add('moveablepiece');
        }
    }
}