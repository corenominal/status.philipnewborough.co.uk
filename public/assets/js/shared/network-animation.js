(() => {
  'use strict';

  const canvas = document.createElement('canvas');
  canvas.setAttribute('aria-hidden', 'true');
  Object.assign(canvas.style, {
    position: 'fixed',
    top: '0',
    left: '0',
    width: '100%',
    height: '100%',
    zIndex: '-1',
    pointerEvents: 'none',
  });
  document.body.prepend(canvas);

  const ctx = canvas.getContext('2d');
  const COLOR = 'rgba(255, 211, 0, 0.25)';
  const NODE_COUNT = 65;
  const MAX_DISTANCE = 200;
  const MAX_CONNECTIONS = 3;
  const EDGE_DURATION = 480;
  const CONCURRENT = 4;

  let nodes = [];
  let edges = [];
  let active = [];
  let queue = [];
  let queueIdx = 0;
  let running = false;

  const resize = () => {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
  };

  const shuffle = (arr) => {
    for (let i = arr.length - 1; i > 0; i -= 1) {
      const j = Math.floor(Math.random() * (i + 1));
      [arr[i], arr[j]] = [arr[j], arr[i]];
    }
  };

  const buildGraph = () => {
    nodes = Array.from({ length: NODE_COUNT }, () => ({
      x: Math.random() * canvas.width,
      y: Math.random() * canvas.height,
      connected: false,
    }));

    const edgeSet = new Set();
    edges = [];

    nodes.forEach((node, i) => {
      const neighbors = nodes
        .map((other, j) => {
          if (i === j) return null;
          const dist = Math.hypot(node.x - other.x, node.y - other.y);
          return dist < MAX_DISTANCE ? { j, dist } : null;
        })
        .filter(Boolean)
        .sort((a, b) => a.dist - b.dist)
        .slice(0, MAX_CONNECTIONS);

      neighbors.forEach(({ j }) => {
        const key = i < j ? `${i}-${j}` : `${j}-${i}`;
        if (!edgeSet.has(key)) {
          edgeSet.add(key);
          edges.push({ from: i, to: j, done: false });
        }
      });
    });

    shuffle(edges);
    queue = edges.slice();
    active = [];
    queueIdx = 0;
  };

  const drawEdge = (edge, progress = 1) => {
    const a = nodes[edge.from];
    const b = nodes[edge.to];
    ctx.beginPath();
    ctx.moveTo(a.x, a.y);
    ctx.lineTo(a.x + (b.x - a.x) * progress, a.y + (b.y - a.y) * progress);
    ctx.strokeStyle = COLOR;
    ctx.lineWidth = 1;
    ctx.stroke();
  };

  const drawNode = (node) => {
    ctx.beginPath();
    ctx.arc(node.x, node.y, 2.5, 0, Math.PI * 2);
    ctx.fillStyle = COLOR;
    ctx.fill();
  };

  const frame = (ts) => {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    edges.forEach((e) => {
      if (e.done) drawEdge(e);
    });

    while (active.length < CONCURRENT && queueIdx < queue.length) {
      active.push({ edge: queue[queueIdx], t0: ts });
      queueIdx += 1;
    }

    active = active.filter(({ edge, t0 }) => {
      const p = Math.min(1, (ts - t0) / EDGE_DURATION);
      drawEdge(edge, p);
      if (p >= 1) {
        edge.done = true;
        nodes[edge.from].connected = true;
        nodes[edge.to].connected = true;
        return false;
      }
      return true;
    });

    nodes.forEach((n) => {
      if (n.connected) drawNode(n);
    });

    if (active.length > 0 || queueIdx < queue.length) {
      requestAnimationFrame(frame);
    } else {
      running = false;
    }
  };

  const init = () => {
    resize();
    buildGraph();
    running = true;
    requestAnimationFrame(frame);
  };

  window.addEventListener('resize', () => {
    if (running) return;
    resize();
    buildGraph();
    running = true;
    requestAnimationFrame(frame);
  });

  document.addEventListener('DOMContentLoaded', init);
})();
