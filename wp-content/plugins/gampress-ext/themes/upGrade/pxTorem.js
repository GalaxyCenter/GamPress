(function (doc, win) {

    var _root = doc.documentElement,

        resizeEvent = 'orientationchange' in window ? 'orientationchange' : 'resize';

    function resizeFont () {
        var clientWidth = _root.clientWidth;

        if(clientWidth > 750){
            clientWidth = 750;
        }
        _root.style.fontSize = ( clientWidth / 20) + 'px';

    }

    win.addEventListener(resizeEvent, resizeFont, false);

    doc.addEventListener('DOMContentLoaded', resizeFont, false);

})(document, window);