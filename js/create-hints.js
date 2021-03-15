(function (global, factory) {
    global.pprhCreateHint = factory();
}(this, function() {

    function createHint(data) {
        var hintType = getHintType(data.hint_type);
        var urlValue = getUrl(data.url, hintType);
        var fileType = getFileType(urlValue);

        var mimeTypeValues = getFileTypeMime(fileType);

        var obj = {
            url:         urlValue,
            hint_type:   hintType,
            media:       ('preload' === hintType) ? getAttr(data.media) : '',
            as_attr:     getAttr(data.as_attr, mimeTypeValues),
            type_attr:   getAttr(data.type_attr, mimeTypeValues),
            crossorigin: getCrossorigin(hintType, data.crossorigin, urlValue, fileType),
        };

        if (typeof pprhProAdminJS === "object") {
            obj.post_id = pprhProAdminJS.GetPostId();
        }

        return obj;
    }

    // tested
    function getUrl(url, hintType) {
        if ( (-1 === url.indexOf('http')) && (0 !== url.indexOf('//')) ) {
            url = '//' + url;
        }

        if ( /dns-prefetch|preconnect/i.test(hintType)) {
            url = getDomain(url);
        }

        return sanitizeURL(url);
    }

    function sanitizeURL(url) {
        return url.replace(/[\[\]\{\}\<\>\'\"\\(\)\*\+\\^\$\|]/g, '');
    }

    // tested
    function getDomain(url) {
        var domain = '';

        if (typeof window.URL === "function" && (0 !== url.indexOf('//') )) {
            domain = new URL(url).origin;
        } else {
            var newStr = url.split('/');
            domain = newStr[0] + '//' + newStr[2];
        }

        return domain;
    }

    // tested
    function getHintType(hintType) {
        return (/dns-prefetch|prefetch|prerender|preconnect|preload/.test(hintType)) ? hintType : '';
    }

    function getFileType(url) {
        return "." + url.split('.').pop().split(/\W/)[0];
    }

    function getCrossorigin(hintType, crossoriginElem, urlValue, fileType) {
        if ( 'preconnect' === hintType ) {
            if ( true === crossoriginElem || /fonts.(googleapis|gstatic|cdnfonts).com/i.test(urlValue) || /(\.woff|\.woff2|\.ttf|\.eot)/i.test(fileType)) {
                return 'crossorigin';
            }
        }

        return '';
    }

    function getAttr(val, mimeValues, prop) {
        if (val && val !== '') {
            return val;
        } else if (mimeValues && mimeValues[prop] && mimeValues[prop] !== '') {
            return mimeValues[prop];
        }
        return '';
    }

    // tested
    function getFileTypeMime(fileType) {
        var i = 0;

        for (i = 0; i < mimes.length; i++) {
            if (mimes[i].fileType === fileType) {
                return mimes[i];
            }
        }

        return false;
    }

    const mimes = [
        { 'fileType': '.epub',   'as': '',         'mimeType': 'application/epub+zip' },
        { 'fileType': '.json',   'as': '',         'mimeType': 'application/json' },
        { 'fileType': '.jsonld', 'as': '',         'mimeType': 'application/ld+json' },
        { 'fileType': '.bin',    'as': '',         'mimeType': 'application/octet-stream' },
        { 'fileType': '.ogx',    'as': '',         'mimeType': 'application/ogg' },
        { 'fileType': '.pdf',    'as': '',         'mimeType': 'application/pdf' },
        { 'fileType': '.swf',    'as': 'embed',    'mimeType': 'application/x-shockwave-flash' },

        { 'fileType': '.aac',    'as': 'audio',    'mimeType': 'audio/aac' },
        { 'fileType': '.mp3',    'as': 'audio',    'mimeType': 'audio/mpeg' },
        { 'fileType': '.mpeg',   'as': 'audio',    'mimeType': 'audio/mpeg' },
        { 'fileType': '.oga',    'as': '',         'mimeType': 'audio/ogg' },
        { 'fileType': '.opus',   'as': '',         'mimeType': 'audio/opus' },
        { 'fileType': '.weba',   'as': 'audio',    'mimeType': 'audio/webm' },

        { 'fileType': '.eot',    'as': 'font',     'mimeType': 'font/eot' },
        { 'fileType': '.otf',    'as': 'font',     'mimeType': 'font/otf' },
        { 'fileType': '.ttf',    'as': 'font',     'mimeType': 'font/ttf' },
        { 'fileType': '.woff',   'as': 'font',     'mimeType': 'font/woff' },
        { 'fileType': '.woff2',  'as': 'font',     'mimeType': 'font/woff2' },

        { 'fileType': '.css',    'as': 'style',    'mimeType': 'text/css' },
        { 'fileType': '.htm',    'as': 'document', 'mimeType': 'text/html' },
        { 'fileType': '.html',   'as': 'document', 'mimeType': 'text/html' },
        { 'fileType': '.js',     'as': 'script',   'mimeType': 'text/javascript' },
        { 'fileType': '.txt',    'as': '',         'mimeType': 'text/plain' },
        { 'fileType': '.vtt',    'as': 'track',    'mimeType': 'text/vtt' },

        { 'fileType': '.mp4',    'as': 'video',    'mimeType': 'video/mp4' },
        { 'fileType': '.ogv',    'as': 'video',    'mimeType': 'video/ogg' },
        { 'fileType': '.webm',   'as': 'video',    'mimeType': 'video/webm' },
        { 'fileType': '.avi',    'as': 'video',    'mimeType': 'video/x-msvideo' },

        { 'fileType': '.bmp',    'as': 'image',    'mimeType': 'image/bmp' },
        { 'fileType': '.jpg',    'as': 'image',    'mimeType': 'image/jpeg' },
        { 'fileType': '.jpeg',   'as': 'image',    'mimeType': 'image/jpeg' },
        { 'fileType': '.png',    'as': 'image',    'mimeType': 'image/png' },
        { 'fileType': '.svg',    'as': 'image',    'mimeType': 'image/svg+xml' },
        { 'fileType': '.ico',    'as': 'image',    'mimeType': 'image/vnd.microsoft.icon' },
        { 'fileType': '.webp',   'as': 'image',    'mimeType': 'image/webp' },
    ];


    return {
        GetUrl: getUrl,
        GetDomain: getDomain,
        GetHintType: getHintType,
        GetFileType: getFileType,
        GetAttr: getAttr,
        GetFileTypeMime: getFileTypeMime,
        CreateHint: createHint
    };

}));


if (! /sphacks.local/g.test(document.location.href)) {
    module.exports = this.pprhCreateHint;
}
