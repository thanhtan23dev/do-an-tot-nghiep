export default function initImageResize() {
    const pane = document.getElementById("pane");
    const ghostpane = document.getElementById("ghostpane");

    if (!pane || !ghostpane) return;

    console.log("Image Resize Module: Initialized");

    const minWidth = 60;
    const minHeight = 40;
    const FULLSCREEN_MARGINS = -10;
    const MARGINS = 4;

    let clicked = null;
    let onRightEdge, onBottomEdge, onLeftEdge, onTopEdge;
    let rightScreenEdge, bottomScreenEdge;
    let preSnapped;
    let b, x, y;
    let redraw = false;
    let currentEvent;

    function setBounds(element, x, y, w, h) {
        element.style.left = x + "px";
        element.style.top = y + "px";
        element.style.width = w + "px";
        element.style.height = h + "px";
    }

    function hintHide() {
        setBounds(ghostpane, b.left, b.top, b.width, b.height);
        ghostpane.style.opacity = 0;
    }

    pane.addEventListener("mousedown", onDown);
    document.addEventListener("mousemove", (e) => onMove(e));
    document.addEventListener("mouseup", onUp);

    pane.addEventListener("touchstart", (e) => onDown(e.touches[0]));
    document.addEventListener("touchmove", (e) => onMove(e.touches[0]));
    document.addEventListener("touchend", (e) => {
        if (e.touches.length == 0) onUp(e.changedTouches[0]);
    });

    function onDown(e) {
        calc(e);
        const isResizing =
            onRightEdge || onBottomEdge || onTopEdge || onLeftEdge;

        clicked = {
            x: x,
            y: y,
            cx: e.clientX,
            cy: e.clientY,
            w: b.width,
            h: b.height,
            isResizing: isResizing,
            isMoving: !isResizing && canMove(),
            onTopEdge: onTopEdge,
            onLeftEdge: onLeftEdge,
            onRightEdge: onRightEdge,
            onBottomEdge: onBottomEdge,
        };
    }

    function canMove() {
        return x > 0 && x < b.width && y > 0 && y < b.height && y < 30;
    }

    function calc(e) {
        b = pane.getBoundingClientRect();
        x = e.clientX - b.left;
        y = e.clientY - b.top;

        onTopEdge = y < MARGINS;
        onLeftEdge = x < MARGINS;
        onRightEdge = x >= b.width - MARGINS;
        onBottomEdge = y >= b.height - MARGINS;

        rightScreenEdge = window.innerWidth - MARGINS;
        bottomScreenEdge = window.innerHeight - MARGINS;
    }

    function onMove(e) {
        calc(e);
        currentEvent = e;
        redraw = true;
    }

    function animate() {
        requestAnimationFrame(animate);
        if (!redraw || !currentEvent) return;
        redraw = false;

        const e = currentEvent;

        if (clicked && clicked.isResizing) {
            if (clicked.onRightEdge)
                pane.style.width = Math.max(x, minWidth) + "px";
            if (clicked.onBottomEdge)
                pane.style.height = Math.max(y, minHeight) + "px";

            if (clicked.onLeftEdge) {
                let currentWidth = Math.max(
                    clicked.cx - e.clientX + clicked.w,
                    minWidth,
                );
                if (currentWidth > minWidth) {
                    pane.style.width = currentWidth + "px";
                    pane.style.left = e.clientX + "px";
                }
            }

            if (clicked.onTopEdge) {
                let currentHeight = Math.max(
                    clicked.cy - e.clientY + clicked.h,
                    minHeight,
                );
                if (currentHeight > minHeight) {
                    pane.style.height = currentHeight + "px";
                    pane.style.top = e.clientY + "px";
                }
            }
            hintHide();
            return;
        }

        if (clicked && clicked.isMoving) {
            if (
                b.top < FULLSCREEN_MARGINS ||
                b.left < FULLSCREEN_MARGINS ||
                b.right > window.innerWidth - FULLSCREEN_MARGINS ||
                b.bottom > window.innerHeight - FULLSCREEN_MARGINS
            ) {
                setBounds(
                    ghostpane,
                    0,
                    0,
                    window.innerWidth,
                    window.innerHeight,
                );
                ghostpane.style.opacity = 0.2;
            } else {
                hintHide();
            }

            if (preSnapped) {
                setBounds(
                    pane,
                    e.clientX - preSnapped.width / 2,
                    e.clientY - Math.min(clicked.y, preSnapped.height),
                    preSnapped.width,
                    preSnapped.height,
                );
                return;
            }

            pane.style.top = e.clientY - clicked.y + "px";
            pane.style.left = e.clientX - clicked.x + "px";
            return;
        }

        if ((onRightEdge && onBottomEdge) || (onLeftEdge && onTopEdge))
            pane.style.cursor = "nwse-resize";
        else if ((onRightEdge && onTopEdge) || (onBottomEdge && onLeftEdge))
            pane.style.cursor = "nesw-resize";
        else if (onRightEdge || onLeftEdge) pane.style.cursor = "ew-resize";
        else if (onBottomEdge || onTopEdge) pane.style.cursor = "ns-resize";
        else if (canMove()) pane.style.cursor = "move";
        else pane.style.cursor = "default";
    }

    animate();

    function onUp(e) {
        if (clicked && clicked.isMoving) {
            const snapped = { width: b.width, height: b.height };
            if (b.top < FULLSCREEN_MARGINS) {
                setBounds(pane, 0, 0, window.innerWidth, window.innerHeight);
                preSnapped = snapped;
            } else {
                preSnapped = null;
            }
            hintHide();
        }
        clicked = null;
    }
}
