const canvas = document.getElementById("game");
const ctx = canvas.getContext("2d");
const menu = document.getElementById("menu");
const startBtn = document.getElementById("start-game");
const continueBtn = document.getElementById("continue-game");
const newGameBtn = document.getElementById("new-game");
const highScoreEl = document.getElementById("high-score");

const box = 20;
const GRID_SIZE = 30;
const CANVAS_SIZE = 600;
const GOAL_PER_LEVEL = 10;

let snake = [];
let dir = null;
let food = {};
let totalScore = 0;
let currentLevel = 1;
let remainingGoal = GOAL_PER_LEVEL;
let speed = 160;
let obstacles = [];
let gameState = 'menu';
let gameLoop;
let highScore = parseInt(localStorage.getItem('snakeHighScore') || '0');

highScoreEl.textContent = `High Score: ${highScore}`;

function showMenu() { menu.style.display = 'flex'; }
function hideMenu() { menu.style.display = 'none'; }

function updateDisplays() {
  document.getElementById("score").innerText = `Total: ${totalScore}`;
  document.getElementById("level").innerText = `Level: ${currentLevel}`;
  document.getElementById("remaining").innerText = `Remaining: ${remainingGoal}`;
}
function playSnakeTitleIntro() {
  gameState = 'title-intro';
  clearInterval(gameLoop);

  // This is the EXACT "SNAKE" shape from classic games (scaled to your 20px grid)
  const SNAKE_TITLE = [
    // S
    {x: 3, y: 1}, {x: 4, y: 1}, {x: 5, y: 1},
    {x: 2, y: 2},
    {x: 3, y: 3}, {x: 4, y: 3}, {x: 5, y: 3},
    {x: 6, y: 4},
    {x: 5, y: 5}, {x: 4, y: 5}, {x: 3, y: 5},

      // N ← fixed, no longer looks like M
    {x: 8, y: 1}, {x: 8, y: 2}, {x: 8, y: 3}, {x: 8, y: 4}, {x: 8, y: 5},
    {x: 9, y: 2}, {x: 10, y: 3},                     // only these two for the diagonal
    {x: 11, y: 4},                                   // this one makes it symmetric and clean
    {x: 12, y: 1}, {x: 12, y: 2}, {x: 12, y: 3}, {x: 12, y: 4}, {x: 12, y: 5},

    // A
    {x: 14, y: 1}, {x: 15, y: 1}, {x: 16, y: 1},
    {x: 13, y: 2}, {x: 17, y: 2},
    {x: 13, y: 3}, {x: 17, y: 3},
    {x: 13, y: 4}, {x: 14, y: 4}, {x: 15, y: 4}, {x: 16, y: 4}, {x: 17, y: 4},
    {x: 13, y: 5}, {x: 17, y: 5},

    // K
    {x: 19, y: 1}, {x: 19, y: 2}, {x: 19, y: 3}, {x: 19, y: 4}, {x: 19, y: 5},
    {x: 20, y: 3},
    {x: 21, y: 2}, {x: 22, y: 1},
    {x: 21, y: 4}, {x: 22, y: 5},

    // E
    {x: 24, y: 1}, {x: 25, y: 1}, {x: 26, y: 1}, {x: 27, y: 1},
    {x: 24, y: 2}, {x: 24, y: 3}, {x: 24, y: 4},
    {x: 24, y: 5}, {x: 25, y: 5}, {x: 26, y: 5}, {x: 27, y: 5},
    {x: 25, y: 3}, {x: 26, y: 3}
  ];

  // Convert grid → pixels and place in top-left
  snake = SNAKE_TITLE.map(p => ({ x: p.x * box, y: p.y * box }));
  dir = null;

  // Draw it once
  draw();

  // Wait 1.8 seconds, then start the real game
  setTimeout(() => {
    resetLevel();           // Spawns normal snake in center
    gameState = 'playing';
    gameLoop = setInterval(mainLoop, speed);
  }, 1800);
}

function updateSpeed() {
  switch (currentLevel) {

    case 1:
      speed = 100;
      break;

    case 2:
      speed = 180;
      break;

    case 3:
      speed = 160;
      break;

    case 4:
      speed = 140;
      break;

    case 5:
      speed = 120;
      break;

    case 6:
      speed = 100;
      break;

    case 7:
      speed = 100;
      break;

    case 8:
      speed = 100;
      break;

    case 9:
      speed = 90;
      break;

    case 10:
      speed = 90;
      break;

    default:
      speed = 200; // si jamais level est hors range
  }

  // Redémarrer la boucle si on est en train de jouer
  if (gameState === 'playing') {
    clearInterval(gameLoop);
    gameLoop = setInterval(mainLoop, speed);
  }
}

function resetLevel() {
  const centerRow = 14;
  const centerCol = 14;
  snake = [
    { x: (centerCol + 1) * box, y: centerRow * box },
    { x: centerCol * box, y: centerRow * box },
    { x: (centerCol - 1) * box, y: centerRow * box }
  ];
  dir = "RIGHT";  // ← THIS FIXES EVERYTHING!
  obstacles = getObstaclesForLevel(currentLevel);
  randomFood();
}

function resetGame() {
  totalScore = 0;
  currentLevel = 1;
  remainingGoal = GOAL_PER_LEVEL;
  updateSpeed();
  updateDisplays();
  resetLevel();
}
function startNewGame() {
  resetGame();           // resets score, level, speed, etc.
  hideMenu();            // hide menu instantly
  playSnakeTitleIntro(); // ← this function handles EVERYTHING from now on
}

function continueGame() {
  gameState = 'playing';
  hideMenu();
  gameLoop = setInterval(mainLoop, speed);
}

function showStartMenu() {
  startBtn.style.display = 'block';
  continueBtn.style.display = 'none';
  newGameBtn.style.display = 'none';
  showMenu();
  gameState = 'menu';
}

function showPauseMenu() {
  draw();
  startBtn.style.display = 'none';
  continueBtn.style.display = 'block';
  newGameBtn.style.display = 'block';
  showMenu();
  gameState = 'paused';
}

function getObstaclesForLevel(lvl) {
  let obs = [];
  if (lvl >= 2) {
    obs.push({ c: 1, r: 1 }, { c: 1, r: 28 }, { c: 28, r: 1 }, { c: 28, r: 28 });
  }
  if (lvl >= 3) {
    obs.push({ c: 15, r: 5 }, { c: 15, r: 10 }, { c: 15, r: 20 }, { c: 15, r: 25 });
  }
  if (lvl >= 4) {
    obs.push({ c: 5, r: 15 }, { c: 10, r: 15 }, { c: 20, r: 15 }, { c: 25, r: 15 });
  }
  if (lvl >= 5) {
    obs.push({ c: 8, r: 8 }, { c: 8, r: 9 }, { c: 22, r: 21 }, { c: 22, r: 22 });
  }
  if (lvl >= 6) {
    for (let r = 8; r <= 22; r += 2) {
      obs.push({ c: 4, r });
    }
  }
  if (lvl >= 7) {
    for (let r = 8; r <= 22; r += 2) {
      obs.push({ c: 25, r });
    }
  }
  if (lvl >= 8) {
    for (let c = 8; c <= 22; c += 2) {
      obs.push({ c, r: 4 });
    }
  }
  if (lvl >= 9) {
    for (let c = 8; c <= 22; c += 2) {
      obs.push({ c, r: 25 });
    }
  }
  if (lvl >= 10) {
    obs.push(
      { c: 12, r: 12 }, { c: 13, r: 12 }, { c: 17, r: 12 }, { c: 18, r: 12 },
      { c: 12, r: 17 }, { c: 13, r: 17 }, { c: 17, r: 17 }, { c: 18, r: 17 }
    );
  }
  return obs.map(p => ({ x: p.c * box, y: p.r * box }));
}

function randomFood() {
  let attempts = 0;
  const maxAttempts = 500;
  while (attempts < maxAttempts) {
    const col = Math.floor(Math.random() * GRID_SIZE);
    const row = Math.floor(Math.random() * GRID_SIZE);
    const tx = col * box;
    const ty = row * box;
    if (snake.every(s => s.x !== tx || s.y !== ty) &&
        obstacles.every(o => o.x !== tx || o.y !== ty)) {
      food = { x: tx, y: ty };
      return;
    }
    attempts++;
  }
  // Rare fallback
  food = { x: Math.floor(Math.random() * GRID_SIZE) * box, y: Math.floor(Math.random() * GRID_SIZE) * box };
}

function draw() {
  ctx.clearRect(0, 0, CANVAS_SIZE, CANVAS_SIZE);

  // Snake
  snake.forEach((segment, i) => {
    const ratio = i / snake.length;
    const r = Math.floor(50 + 150 * (1 - ratio));
    const g = Math.floor(255 - 100 * ratio);
    const b = Math.floor(50 + 50 * (1 - ratio));
    ctx.fillStyle = `rgb(${r}, ${g}, ${b})`;

    if (i !== 0) {
      ctx.beginPath();
      ctx.arc(segment.x + box / 2, segment.y + box / 2, box / 2 - 1, 0, Math.PI * 2);
      ctx.fill();
    } else {
      // Head
      ctx.beginPath();
      ctx.arc(segment.x + box / 2, segment.y + box / 2, box / 2 - 1, 0, Math.PI * 2);
      ctx.fill();

      // Eyes
      ctx.fillStyle = "white";
      let eyeX = box / 2 + box / 3;
      let eyeY = box / 2;
      if (dir === "UP") { eyeX = box / 2; eyeY = box / 3; }
      if (dir === "DOWN") { eyeX = box / 2; eyeY = box - box / 3; }
      if (dir === "LEFT") { eyeX = box / 4; eyeY = box / 2; }
      if (dir === "RIGHT") { eyeX = box - box / 4; eyeY = box / 2; }

      ctx.beginPath();
      ctx.arc(segment.x + eyeX - 3, segment.y + eyeY - 3, 4, 0, Math.PI * 2);
      ctx.arc(segment.x + eyeX + 3, segment.y + eyeY - 3, 4, 0, Math.PI * 2);
      ctx.fill();

      ctx.fillStyle = "black";
      ctx.beginPath();
      ctx.arc(segment.x + eyeX - 3, segment.y + eyeY - 3, 2, 0, Math.PI * 2);
      ctx.arc(segment.x + eyeX + 3, segment.y + eyeY - 3, 2, 0, Math.PI * 2);
      ctx.fill();
    }
  });

  // Food (apple)
  const fx = food.x + box / 2;
  const fy = food.y + box / 2;
  const gradient = ctx.createRadialGradient(fx - 3, fy - 3, 1, fx, fy, box / 2);
  gradient.addColorStop(0, "#ff4444");
  gradient.addColorStop(0.7, "#cc0000");
  gradient.addColorStop(1, "#880000");
  ctx.fillStyle = gradient;
  ctx.beginPath();
  ctx.arc(fx, fy, box / 2 - 2, 0, Math.PI * 2);
  ctx.fill();

  ctx.fillStyle = "rgba(255,255,255,0.4)";
  ctx.beginPath();
  ctx.arc(fx - 4, fy - 4, box / 6, 0, Math.PI * 2);
  ctx.fill();

  ctx.strokeStyle = "#553300";
  ctx.lineWidth = 3;
  ctx.lineCap = "round";
  ctx.beginPath();
  ctx.moveTo(fx, fy - box / 2 + 2);
  ctx.lineTo(fx, fy - box / 2 - 4);
  ctx.stroke();

  // Obstacles (round rocks)
  ctx.fillStyle = "#666";
  obstacles.forEach(o => {
    ctx.beginPath();
    ctx.arc(o.x + box / 2, o.y + box / 2, box / 2 - 2, 0, Math.PI * 2);
    ctx.fill();
  });
}

function update() {
  if (!dir) return 'ok';

  let head = { ...snake[0] };
  if (dir === "UP") head.y -= box;
  if (dir === "DOWN") head.y += box;
  if (dir === "LEFT") head.x -= box;
  if (dir === "RIGHT") head.x += box;

  if (head.x === food.x && head.y === food.y) {
    totalScore++;
    remainingGoal--;
    updateDisplays();
    if (remainingGoal <= 0) {
      return 'nextlevel';
    }
    randomFood();
  } else {
    snake.pop();
  }

  if (head.x < 0 || head.x >= CANVAS_SIZE || head.y < 0 || head.y >= CANVAS_SIZE) {
    return 'gameover';
  }
  if (snake.some(s => s.x === head.x && s.y === head.y)) {
    return 'gameover';
  }
  if (obstacles.some(o => o.x === head.x && o.y === head.y)) {
    return 'gameover';
  }

  snake.unshift(head);
  return 'ok';
}

function nextLevelTransition() {
  const oldLevel = currentLevel;
  currentLevel++;
  
  // Win condition - completed all 10 levels
  if (currentLevel > 10) {
    alert(`Congratulations! You completed all 10 levels! Total Score: ${totalScore}`);
    if (totalScore > highScore) {
      highScore = totalScore;
      localStorage.setItem('snakeHighScore', highScore);
      highScoreEl.textContent = `High Score: ${highScore}`;
    }
    showStartMenu();
    return;
  }

  // Prepare next level
  resetLevel();
  updateSpeed();        // This recalculates the correct faster speed
  gameState = 'transition';

  let animFrame = 0;

  function transitionLoop() {
    // Dark background
    ctx.fillStyle = 'black';
    ctx.fillRect(0, 0, CANVAS_SIZE, CANVAS_SIZE);

    // Big green level numbers
    ctx.fillStyle = '#0f0';
    ctx.font = 'bold 140px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';

    const cx = CANVAS_SIZE / 2;
    const cy = CANVAS_SIZE / 2;
    const slideDist = 300;
    const slideSpeed = 12;  // Feels snappier

    // Old level slides up and fades out
    const oldY = cy - (animFrame * slideSpeed);
    if (oldY > cy - slideDist) {
      ctx.globalAlpha = Math.max(0, (cy - slideDist - oldY + 100) / 200);
      ctx.fillText(oldLevel.toString(), cx, oldY);
      ctx.globalAlpha = 1;
    }

    // New level slides down from below
    const newY = cy + slideDist - (animFrame * slideSpeed);
    ctx.fillText(currentLevel.toString(), cx, newY);

    animFrame++;

    // When animation finishes
    if (newY <= cy + 30) {
      gameState = 'playing';
      clearInterval(gameLoop);                    // Stop any old interval
      gameLoop = setInterval(mainLoop, speed);    // Start with CORRECT new speed
      return;
    }

    requestAnimationFrame(transitionLoop);
  }

  transitionLoop();
}
function mainLoop() {
  if (gameState !== 'playing') return;

  const result = update();
  if (result === 'gameover') {
    clearInterval(gameLoop);
    alert(`Game Over! Final Total: ${totalScore}`);
    if (totalScore > highScore) {
      highScore = totalScore;
      localStorage.setItem('snakeHighScore', highScore);
      highScoreEl.textContent = `High Score: ${highScore}`;
    }
    showStartMenu();
    return;
  }

  if (result === 'nextlevel') {
    clearInterval(gameLoop);  // Important: stop old (slower) loop
    nextLevelTransition();
    return;
  }

  draw();
  // Do NOT restart the interval here! It's already running at correct speed
}
// Event listeners
startBtn.onclick = startNewGame;
continueBtn.onclick = continueGame;
newGameBtn.onclick = startNewGame;

document.addEventListener("keydown", e => {
  if (e.key === "Escape") {
    e.preventDefault();
    if (gameState === 'playing') {
      clearInterval(gameLoop);
      showPauseMenu();
    } else if (gameState === 'paused') {
      continueGame();
    }
    return;
  }
  if (gameState !== 'playing') return;

  if (e.key === "ArrowUp" && dir !== "DOWN") dir = "UP";
  if (e.key === "ArrowDown" && dir !== "UP") dir = "DOWN";
  if (e.key === "ArrowLeft" && dir !== "RIGHT") dir = "LEFT";
  if (e.key === "ArrowRight" && dir !== "LEFT") dir = "RIGHT";
});

// Init
showStartMenu();