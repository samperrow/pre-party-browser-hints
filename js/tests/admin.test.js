'use strict';

const pprhAdminJS = require('../admin');

pprhAdminJS('adds 1 + 2 to equal 3', () => {
    pprhAdminJS(sum(1, 2)).toBe(3);
});