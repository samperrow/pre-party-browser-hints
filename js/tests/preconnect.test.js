/**
 * @jest-environment jsdom
 */
// 'use strict';

pprh_data = {
    start_time: new Date().getTime() * 1000,
    timeout: 1000,
}

const Preconnects = require('../preconnect.js');



// test GetUrl(url, hintType)
test('IsValidHintDomain()', () => {

    let domainArr = ['asdf.com'];

    expect(Preconnects.IsValidHintDomain('asdf.com', domainArr))
        .toBe(false);

    expect(Preconnects.IsValidHintDomain('asdf2.com', domainArr))
        .toBe(true);

});

test('GetAltHostName()', () => {

    expect(Preconnects.GetAltHostName( 'https://example.com'))
        .toBe('https://www.example.com');

    expect(Preconnects.GetAltHostName( 'https://www.example.com'))
        .toBe('https://example.com');

    expect(Preconnects.GetAltHostName( 'http://test.com'))
        .toBe('http://www.test.com');

    expect(Preconnects.GetAltHostName( 'http://www.test.com'))
        .toBe('http://test.com');

});

test('ScriptSentWithinSixHours()', () => {

    let currentTime = new Date().getTime() / 1000;
    let sixHoursInMS = 21600000;

    let timeInit1 = currentTime;
    expect(Preconnects.ScriptSentWithinSixHours(timeInit1))
        .toBe(true);

    let timeInit2 = currentTime - (sixHoursInMS);
    expect(Preconnects.ScriptSentWithinSixHours(timeInit2))
        .toBe(false);

    let timeInit3 = currentTime - (sixHoursInMS - 100);
    expect(Preconnects.ScriptSentWithinSixHours(timeInit3))
        .toBe(false);

});


// test('FindResourceSources()', () => {
//
//     expect(Preconnects.FindResourceSources())
//         .toBe();
//
//     expect(Preconnects.FindResourceSources())
//         .toBe();
//
// });

// test('FireAjax()', done => {
//
//     let resources = [
//         {url: 'https://espn.com', hint_type: 'preconnect', 'as_attr': '', 'type_attr': '', 'media': '', 'crossorigin': ''}
//     ];
//
//     function test(result) {
//         try {
//             expect(result).toBe(true);
//             done();
//         } catch (error) {
//             done(error);
//         }
//     }
//
//     Preconnects.FireAjax(resources, test);
//
// });

