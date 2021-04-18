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


// test GetCrossorigin()


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

test('', () => {
    expect(CreateHintsFile.GetFileType(longUrl + '?asdf=something&asdf=sladth325:8080'))
        .toBe('.css');
});

test('woff', () => {
    let res = CreateHintsFile.GetFileType('https://sphacks.local/wp-content/themes/sphacks/images/icons/newspaper.woff?19');
    expect(res)
        .toStrictEqual('.woff');
});

test('woff2', () => {
    let res = CreateHintsFile.GetFileType('https://sphacks.local/wp-content/themes/sphacks/images/icons/newspaper.woff2?19');
    expect(res)
        .toStrictEqual('.woff2');
});

// test getAttr(val, mimeValues, prop)
test('', () => {
    let mimeValues = { 'fileType': '.woff',   'as': 'font',     'mimeType': 'font/woff' };
    let expected = CreateHintsFile.GetAttr('', mimeValues, 'fileType' );
    expect(expected)
        .toStrictEqual('.woff');
});


// test getFileTypeMime(fileType)
test('css mime type', () => {
    expect(CreateHintsFile.GetFileTypeMime('.css'))
    .toStrictEqual({ 'fileType': '.css', 'as': 'style', 'mimeType': 'text/css' });
});

test('woff mime type', () => {
    expect(CreateHintsFile.GetFileTypeMime('.woff'))
    .toStrictEqual({ 'fileType': '.woff', 'as': 'font', 'mimeType': 'font/woff' });
});

test('woff2 mime type', () => {
    expect(CreateHintsFile.GetFileTypeMime('.woff2'))
        .toStrictEqual({ 'fileType': '.woff2', 'as': 'font', 'mimeType': 'font/woff2' });
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

test('woff and preload', () => {
    let input = {
        url: 'https://sphacks.local/wp-content/themes/sphacks/images/icons/newspaper.woff?19',
        hint_type: 'preload',
    };

    let expectedValue = CreateHintsFile.CreateHint(input);

    let result = {
        url: 'https://sphacks.local/wp-content/themes/sphacks/images/icons/newspaper.woff?19',
        hint_type: 'preload',
        media: '',
        as_attr: 'font',
        type_attr: 'font/woff',
        crossorigin: 'crossorigin'
    };


    expect(expectedValue)
        .toStrictEqual(result);
});

test('woff2 and preload', () => {
    let input = {
        url: 'https://sphacks.local/wp-content/themes/sphacks/images/icons/newspaper.woff2?19',
        hint_type: 'preload',
    };

    let expectedValue = CreateHintsFile.CreateHint(input);

    let result = {
        url: 'https://sphacks.local/wp-content/themes/sphacks/images/icons/newspaper.woff2?19',
        hint_type: 'preload',
        media: '',
        as_attr: 'font',
        type_attr: 'font/woff2',
        crossorigin: 'crossorigin'
    };


    expect(expectedValue)
        .toStrictEqual(result);
});


// test('flying pages wildcard ends with *', () => {
//     let keyword = '/products/*';
//     let url_1 = 'https://tester.com/asdf/products/asdfsadfsadf/w3dsgf';
//     let url_2 = 'https://tester.com/asdf/products/';
//
//     expect(CreateHintsFile.WildcardSearch(keyword, url_1))
//         .toStrictEqual(false);
//
//     expect(CreateHintsFile.WildcardSearch(keyword, url_2))
//         .toStrictEqual(true);
// });


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