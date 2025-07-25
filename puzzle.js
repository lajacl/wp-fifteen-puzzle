const numTiles = 15;
const numRows = 4;
const gridBoard = document.getElementById('grid-board');

setupTiles();

function setupTiles() {
    for (let i = 1; i <= numTiles; i++) {
        let div = document.createElement('div');
        div.className = 'tile';
        div.textContent = i;

        gridBoard.appendChild(div);
    }
}