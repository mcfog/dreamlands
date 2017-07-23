window.paceOptions = {
    eventLag: false,
    document: false,
    elements: {
        selectors: ['body:not(.loading)']
    }

};
const Pace = require("pace-progress");

export function start() {
    if (Pace.running) return;
    document.body.classList.add('loading');
    Pace.start();
}

export function stop() {
    document.body.classList.remove('loading');
    Pace.stop();
}

