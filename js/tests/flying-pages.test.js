'use strict';

global.IntersectionObserver = class IntersectionObserver {
    constructor() {}

    observe() {
        return null;
    }

    disconnect() {
        return null;
    };

    unobserve() {
        return null;
    }
};

const FlyingPages = require('../flying-pages.js');
FlyingPages.Init();

// AddUrlToQueue
test('test AddUrlToQueue', () => {


    expect(FlyingPages.AddUrlToQueue('http://localhost/sp-calendar-pro/core/', false))
        .toBe(true);

    expect(FlyingPages.AddUrlToQueue('http://loca3lhost/sp-calendar-pro/core/', false))
        .toBe(false);

});





