setupTiles();

function setupTiles() {
    const numTiles = 15;
    const boardSize = 400;
    const tileSize = 100;
    const gridBoard = document.getElementById('grid-board');
    let imgX = 0;
    let imgY = 0;

    for (let i = 1; i <= numTiles; i++) {
        let div = document.createElement('div');
        div.className = 'tile';
        div.textContent = i;
        div.style.backgroundPositionX = imgX + 'px';
        div.style.backgroundPositionY = imgY + 'px';

        imgX = (imgX > (-boardSize + tileSize)) ? (imgX - tileSize) : 0;
        imgY = (imgX === 0) ? (imgY - tileSize) : imgY;

        gridBoard.appendChild(div);
    }
}