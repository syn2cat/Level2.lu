/**
 * START Trianglify
 */

var t = new Trianglify();
var prevheight = height();

window.onresize = function() {
    redraw();
};

redraw();

function redraw() {
    var pattern = t.generate(document.body.clientWidth, height());
    document.body.setAttribute('style', 'background-image: '+pattern.dataUrl);
}

function height() {
    return Math.max(
        document.body.scrollHeight, document.documentElement.scrollHeight,
        document.body.offsetHeight, document.documentElement.offsetHeight,
        document.body.clientHeight, document.documentElement.clientHeight
    );
}

/**
 * END Trianglify
 */