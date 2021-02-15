'use strict';

// const jQuery = jest.fn(() => ({
//     addClass: jest.fn(),
//     removeClass: jest.fn(),
//     hasClass: jest.fn()
// }));

const pprhAdminJS = require('../admin.js');



pprhAdminJS('adds 1 + 2 to equal 3', () => {
    pprhAdminJS(sum(1, 2)).toBe(3);
});