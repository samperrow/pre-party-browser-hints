/**
 * @jest-environment jsdom
 */

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
const FlyingPagesMin = require('../flying-pages.min.js');

FlyingPages.Init();
FlyingPagesMin.Init();

// AddUrlToQueue
test('test AddUrlToQueue', () => {

    expect(FlyingPages.AddUrlToQueue('http://localhost/sp-calendar-pro/core/', false))
        .toBe(true);

    expect(FlyingPages.AddUrlToQueue('http://loca3lhost/sp-calendar-pro/core/', false))
        .toBe(false);

    expect(FlyingPagesMin.AddUrlToQueue('http://localhost/sp-calendar-pro/core/', false))
        .toBe(true);

    expect(FlyingPagesMin.AddUrlToQueue('http://loca3lhost/sp-calendar-pro/core/', false))
        .toBe(false);

});
