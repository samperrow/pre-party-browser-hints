/**
 * @jest-environment jsdom
 */
// 'use strict';

global.jQuery = require('/Users/samperrow/Desktop/repos/WordPress/wp-includes/js/jquery/jquery.js');

const pprhAdminJS = require('../admin.js');



test('IsObjectAndNotNull()', () => {

    expect(pprhAdminJS.IsObjectAndNotNull(null))
        .toBe(false);

    expect(pprhAdminJS.IsObjectAndNotNull('safg'))
        .toBe(false);

});

// test('GetHintValuesFromTable()', () => {
//
//     let tableElems_1 = {
//         url: 'https://fonts.googleapis.com/css?family=Source+Sans+Pro%3A300%2C400%2C500%2C600%2C700%7CLato%3A300%2C400%2C500%2C700%2C900%7CRaleway%3A300%2C400%2C500%2C700%2C900%7CRaleway&subset=latin%2Clatin-ext',
//         // hint_type: 'preload',
//         media: '',
//         as_attr: 'font',
//         type_attr: 'font/woff2',
//         crossorigin: '',
//     };
//     let expected_1 = {
//         url: 'https://fonts.googleapis.com/css?family=Source+Sans+Pro%3A300%2C400%2C500%2C600%2C700%7CLato%3A300%2C400%2C500%2C700%2C900%7CRaleway%3A300%2C400%2C500%2C700%2C900%7CRaleway&subset=latin%2Clatin-ext',
//         hint_type: 'preload',
//         media: '',
//         as_attr: 'font',
//         type_attr: 'font/woff2',
//         crossorigin: '',
//     };
//     expect(pprhAdminJS.GetHintValuesFromTable(tableElems_1, 'preload'))
//         .toBe(expected_1);
// });
