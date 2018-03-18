{

    const getImageDimensions = (src) => {
        const t = new Image();
        t.src = src;
        let data = {
            'width': t.width,
            'height': t.height,
            'ratio': (t.height / t.width * 100).toFixed(2)
        };
        return data;
    };

    const addDebugMessage = (imgEl, data) => {
        let parent = imgEl.parentNode;

        let msg = document.createElement('ul');

        let elementWidth = document.createElement('li');
        elementWidth.appendChild(
            document.createTextNode(
                'container: ' + data.elementWidth
            )
        );

        let currentSrcWidth = document.createElement('li');
        currentSrcWidth.appendChild(
            document.createTextNode(
                'current: ' + data.currentSrcWidth + 'x' + data.currentSrcHeight + ' (' + data.ratio + ')'
            )
        );

        msg.appendChild(currentSrcWidth);
        msg.appendChild(elementWidth);

        if (typeof parent.getElementsByClassName('img-debug')[0] === "undefined") {
            let container = document.createElement("div");
            container.setAttribute('class', 'img-debug');
            container.appendChild(msg);
            parent.appendChild(container);
        } else {
            let debugContainer = parent.getElementsByClassName('img-debug')[0];
            debugContainer.replaceChild(msg, debugContainer.childNodes[0])
        }
    };

    window.observe = imgEl => {
        const action = () => {
            let dimensions = getImageDimensions(imgEl.currentSrc);
            const data = {
                currentSrc: imgEl.currentSrc,
                currentSrcWidth: dimensions.width,
                currentSrcHeight: dimensions.height,
                ratio: dimensions.ratio,
                elementWidth: imgEl.width
            };
            addDebugMessage(imgEl, data);
        };
        action();
        imgEl.addEventListener('load', action);
        imgEl.addEventListener('resize', action);
    };

    document.querySelectorAll('[data-img-debug]').forEach(function (e) {
        observe(e);
    });


}