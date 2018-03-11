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

        let elementWidth = document.createElement('span').appendChild(
            document.createTextNode(
                'container: ' + data.elementWidth
            )
        );

        let currentSrcWidth = document.createElement('span').appendChild(
            document.createTextNode(
                'current: ' + data.currentSrcWidth + 'x' + data.currentSrcHeight + ' (' + data.ratio + ')'
            )
        );

        let msg = document.createElement('div');
        msg.appendChild(currentSrcWidth);
        msg.appendChild(elementWidth);

        if (typeof parent.getElementsByClassName('imgdebug')[0] === "undefined") {
            let container = document.createElement("div");
            container.setAttribute('class', 'imgdebug');
            container.appendChild(msg);
            parent.appendChild(container);
        } else {
            let debugContainer = parent.getElementsByClassName('imgdebug')[0];
            debugContainer.replaceChild(msg, debugContainer.childNodes[0])
        }
    };

    window.observe = imgEl => {
        const action = () => {
            let dimensions = getImageDimensions(imgEl.currentSrc);
            console.log(dimensions);
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

    document.querySelectorAll('[data-imgdebug]').forEach(function(e) {
        observe(e);
    });


}