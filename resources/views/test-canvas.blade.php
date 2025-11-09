<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Canvas Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background: #f8f9fc;
        }
        canvas {
            border: 1px solid #ccc;
            background: #fff;
        }
    </style>
</head>
<body>
    <h2>Barra simple 0 – 100</h2>
    <p>Generada exclusivamente con JavaScript nativo y canvas.</p>
    <canvas id="demoBar" width="640" height="140"></canvas>

    <script>
        (function () {
            const canvas = document.getElementById('demoBar');
            const ctx = canvas.getContext('2d');

            const value = 50;
            const max = 100;

            const margin = 40;
            const barHeight = 40;
            const barX = margin;
            const barY = canvas.height / 2 - barHeight / 2;
            const barWidth = canvas.width - margin * 2;
            const filled = barWidth * Math.max(0, Math.min(1, value / max));

            const drawRoundedRect = (x, y, w, h, r, color) => {
                ctx.fillStyle = color;
                ctx.beginPath();
                ctx.moveTo(x + r, y);
                ctx.lineTo(x + w - r, y);
                ctx.quadraticCurveTo(x + w, y, x + w, y + r);
                ctx.lineTo(x + w, y + h - r);
                ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
                ctx.lineTo(x + r, y + h);
                ctx.quadraticCurveTo(x, y + h, x, y + h - r);
                ctx.lineTo(x, y + r);
                ctx.quadraticCurveTo(x, y, x + r, y);
                ctx.closePath();
                ctx.fill();
            };

            ctx.clearRect(0, 0, canvas.width, canvas.height);
            drawRoundedRect(barX, barY, barWidth, barHeight, 10, '#e9ecef');
            drawRoundedRect(barX, barY, filled, barHeight, 10, '#4e73df');

            ctx.fillStyle = '#1f2933';
            ctx.font = '18px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(`${value} / ${max}`, barX + barWidth / 2, barY + barHeight / 2 + 6);
        })();
    </script>
</body>
</html>

