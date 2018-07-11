'use strict';

{

    var bindEvent = function bindEvent(el, eventName, eventHandler) {
        if (el.addEventListener){
            el.addEventListener(eventName, eventHandler, false);
        } else if (el.attachEvent){
            el.attachEvent('on'+eventName, eventHandler);
        }
    };

    var getImageDimensions = function getImageDimensions(src) {
        var t = new Image();
        t.src = src;
        var data = {
            'width': t.width,
            'height': t.height,
            'ratio': (t.height / t.width * 100).toFixed(2)
        };
        return data;
    };

    var addDebugMessage = function addDebugMessage(imgEl, data) {
        var parent = imgEl.parentNode;

        var msg = document.createElement('ul');

        var elementWidth = document.createElement('li');
        elementWidth.appendChild(document.createTextNode('container: ' + data.elementWidth));

        var currentSrcWidth = document.createElement('li');
        currentSrcWidth.appendChild(document.createTextNode('current: ' + data.currentSrcWidth + 'x' + data.currentSrcHeight + ' (' + data.ratio + ')'));

        msg.appendChild(currentSrcWidth);
        msg.appendChild(elementWidth);

        if (typeof parent.getElementsByClassName('img-debug')[0] === "undefined") {
            var container = document.createElement("div");
            container.setAttribute('class', 'img-debug');
            container.appendChild(msg);
            parent.appendChild(container);
        } else {
            var debugContainer = parent.getElementsByClassName('img-debug')[0];
            debugContainer.replaceChild(msg, debugContainer.childNodes[0]);
        }
    };

    window.observe = function (imgEl) {
        var action = function action() {
            console.log(imgEl);
            var dimensions = getImageDimensions(imgEl.currentSrc || imgEl.src);
            var parent = imgEl.parentNode;
            var data = {
                currentSrc: imgEl.currentSrc  || imgEl.src,
                currentSrcWidth: dimensions.width,
                currentSrcHeight: dimensions.height,
                ratio: dimensions.ratio,
                elementWidth: imgEl.width,
                html: parent.outerHTML
            };
            addDebugMessage(imgEl, data);
        };
        action();
        bindEvent(imgEl, 'load', function () {
            action();
        });
        bindEvent(imgEl, 'resize', function () {
            action();
        });

    };

    var images = document.querySelectorAll('img[data-img-debug="1"]');

    for(var i = 0; i < images.length; i++) {
        observe(images[i]);
    }
}