'use strict';

const CreateHintsFile = require('../create-hints.js');

var longUrl = 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css';

// test GetUrl(url, hintType)
test('https should be preserved', () => {
    expect(CreateHintsFile.GetUrl(longUrl, 'dns-prefetch'))
    .toBe('https://stackpath.bootstrapcdn.com');
});

test('http should be preserved', () => {
    expect(CreateHintsFile.GetUrl(
        'http://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css',
        'dns-prefetch'))
    .toBe('http://stackpath.bootstrapcdn.com');
});

test(' // should remain', () => {
    expect(CreateHintsFile.GetUrl(
        '//stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css',
        'preconnect'))
    .toBe('//stackpath.bootstrapcdn.com');
});

test('no protocol should get a "//"', () => {
    expect(CreateHintsFile.GetUrl(
        'stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css',
        'preconnect'))
    .toBe('//stackpath.bootstrapcdn.com');
});

test('preconnect should work also.', () => {
    expect(CreateHintsFile.GetUrl(longUrl, 'preconnect'))
    .toBe('https://stackpath.bootstrapcdn.com');
});

test('preload, prerender, prefetch should be ignored', () => {
    expect(CreateHintsFile.GetUrl(longUrl, 'preload'))
    .toBe(longUrl);
});

test('preload, prerender, prefetch should be ignored', () => {
    expect(CreateHintsFile.GetUrl(longUrl, 'prerender'))
    .toBe(longUrl);
});

test('preload, prerender, prefetch should be ignored', () => {
    expect(CreateHintsFile.GetUrl(longUrl, 'prefetch'))
    .toBe(longUrl);
});



// test GetDomain(url)
test('', () => {
    expect(CreateHintsFile.GetDomain(longUrl))
    .toBe('https://stackpath.bootstrapcdn.com');
});

test('', () => {
    expect(CreateHintsFile.GetDomain('http://stackpath.bootstrapcdn.com'))
    .toBe('http://stackpath.bootstrapcdn.com');
});

test('', () => {
    expect(CreateHintsFile.GetDomain('//stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'))
    .toBe('//stackpath.bootstrapcdn.com');
});


// test GetHintType(hintType)
test('dns-prefetch should be dns-prefetch', () => {
    expect(CreateHintsFile.GetHintType('dns-prefetch'))
    .toBe('dns-prefetch');
});

test(' "" should be ""', () => {
    expect(CreateHintsFile.GetHintType(''))
    .toBe('');
});

test('invalid hint type should be ""', () => {
    expect(CreateHintsFile.GetHintType('invalidHintType'))
    .toBe('');
});


// test GetFileType(url)
test('', () => {
    expect(CreateHintsFile.GetFileType(longUrl))
    .toBe('.css');
});

test('', () => {
    expect(CreateHintsFile.GetFileType(longUrl + '?asdf=something&asdf=sladth325'))
    .toBe('.css');
});

test('', () => {
    expect(CreateHintsFile.GetFileType(longUrl + '?asdf=something&asdf=sladth325:8080'))
    .toBe('.css');
});



// test getFileTypeMime(fileType)
test('', () => {
    expect(CreateHintsFile.GetFileTypeMime('.css'))
    .toStrictEqual({ 'fileType': '.css', 'as': 'style', 'mimeType': 'text/css' });
});

test('', () => {
    expect(CreateHintsFile.GetFileTypeMime('.woff'))
    .toStrictEqual({ 'fileType': '.woff', 'as': 'font', 'mimeType': 'font/woff' });
});


// test createHint(data)
test('', () => {
    var data = {
        url: 'asdf.com',
        hint_type: 'preconnect',
    };

    var result = {
        url: '//asdf.com',
        hint_type: 'preconnect',
        media: '',
        as_attr: '',
        type_attr: '',
        crossorigin: ''
    };

    expect(CreateHintsFile.CreateHint(data))
    .toStrictEqual( result);
});

// const jQuery = jest.fn(() => ({
//     addClass: jest.fn(),
//     removeClass: jest.fn(),
//     hasClass: jest.fn()
// }));

// const pprhAdminJS = require('../admin.js');
//
//
//
// pprhAdminJS('adds 1 + 2 to equal 3', () => {
//     pprhAdminJS(sum(1, 2)).toBe(3);
// });