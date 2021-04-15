/*
 * @name Flying Pages
 * @description Flying Pages prefetch pages before the user click on links, making them load instantly
 * @documentation https://github.com/gijo-varghese/flying-pages
 * @author Gijo Varghese
 *
 * Copyright 2020 Gijo Varghese (https://wpspeedmatters.com)
 * Licensed under the ISC license:
 * https://opensource.org/licenses/ISC
*/
function pprhFlyingPages() {

    const toPrefetch = new Set();
    const alreadyPrefetched = new Set();

    // Check browser support for native 'prefetch'
    const prefetcher = document.createElement("link");
    const isSupported = prefetcher.relList && prefetcher.relList.supports && prefetcher.relList.supports("prefetch") && window.IntersectionObserver && "isIntersecting" in IntersectionObserverEntry.prototype;

    var fp_data = {
        maxRPS: Number(pprh_fp_data.maxRPS),
        delay: Number(pprh_fp_data.delay),
        hoverDelay: Number(pprh_fp_data.hoverDelay),
        ignoreKeywords: pprh_fp_data.ignoreKeywords.replace(/\s/g, '').split(','),
        debug: ('true' === pprh_fp_data.debug),
        maxPrefetches: Number(pprh_fp_data.maxPrefetches)
    };

    var prefetchCount = 0;

    // Checks if user is on slow connection or has enabled data saver
    const isSlowConnection = navigator.connection && (navigator.connection.saveData || (navigator.connection.effectiveType || "").includes("2g"));

    // Don't start prefetching if user is on a slow connection or not supported
    if (isSlowConnection || !isSupported) return;

    // Prefetch the given url using native 'prefetch'. Fallback to 'xhr' if not supported
    const prefetch = url => new Promise((resolve, reject) => {
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        link.onload = resolve;
        link.onerror = reject;
        document.head.appendChild(link);
        if (fp_data.debug) {
            console.log(link);
        }
    });

    // Prefetch pages with a timeout
    const prefetchWithTimeout = url => {
        const timer = setTimeout(() => stopPrefetching(), 5000);
        prefetch(url)
            .catch(() => stopPrefetching())
            .finally(() => clearTimeout(timer));
    };

    function isUrlValid(url) {
        const origin = window.location.origin;
        const currentPage = origin + document.location.pathname;
        return ! ((alreadyPrefetched.has(url) || toPrefetch.has(url)) || (prefetchCount >= fp_data.maxPrefetches) || (url.substring(0, origin.length) !== origin) || (currentPage === url));
    }

    const addUrlToQueue = (url, processImmediately = false) => {
        if (!isUrlValid(url)) {
            return;
        }

        // Ignore keywords in the array, if matched to the url
        for (let i = 0; i < fp_data.ignoreKeywords.length; i++) {
            var keyword = fp_data.ignoreKeywords[i];
            if (keyword.length > 0 && url.includes(keyword)) {
                return;
            }

            // wildcard check
            else if (keyword.indexOf("*") === (keyword.length - 1)) {
                let wildcard = keyword.replace("*", "");
                if (url.indexOf(wildcard) >= 0) {
                    return;
                }
            }
        }

        // If max RPS is 0 or is on mouse hover, process immediately (without queue)
        if (processImmediately) {
            prefetchWithTimeout(url);
            alreadyPrefetched.add(url);
        } else toPrefetch.add(url);

        prefetchCount++;
    };

    // Observe the links in viewport, add url to queue if found intersecting
    const linksObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const url = entry.target.href;
                addUrlToQueue(url, !fp_data.maxRPS);
            }
        });
    });

    // Queue that process requests based on max RPS (requests per second)
    const startQueue = () => setInterval(() => {
        Array.from(toPrefetch)
            .slice(0, fp_data.maxRPS)
            .forEach(url => {
                prefetchWithTimeout(url);
                alreadyPrefetched.add(url);
                toPrefetch.delete(url);
            });
    }, 1000);

    let hoverTimer = null;

    // Add URL to queue on mouse hover, after timeout
    const mouseOverListener = event => {
        const elm = event.target.closest("a");
        if (elm && elm.href && !alreadyPrefetched.has(elm.href)) {
            hoverTimer = setTimeout(() => {
                addUrlToQueue(elm.href, true);
            }, fp_data.hoverDelay);
        }
    };

    // prefetch on touchstart on mobile
    const touchStartListener = event => {
        const elm = event.target.closest("a");
        if (elm && elm.href && !alreadyPrefetched.has(elm.href)) addUrlToQueue(elm.href, true);
    };

    // Clear timeout on mouse out if not already prefetched
    const mouseOutListener = event => {
        const elm = event.target.closest("a");
        if (elm && elm.href && !alreadyPrefetched.has(elm.href)) {
            clearTimeout(hoverTimer);
        }
    };

    // Fallback for requestIdleCallback https://caniuse.com/#search=requestIdleCallback
    const requestIdleCallback = window.requestIdleCallback || function (cb) {
        const start = Date.now();
        return setTimeout(function () {
            cb({
                didTimeout: false, timeRemaining: function () {
                    return Math.max(0, 50 - (Date.now() - start));
                }
            });
        }, 1);
    };

    // Stop prefetching in case server is responding slow/errors
    const stopPrefetching = () => {
        // Find all links are remove it from observer (viewport)
        document.querySelectorAll("a").forEach(e => linksObserver.unobserve(e));

        // Clear pending links in queue
        toPrefetch.clear();

        // Remove event listeners for mouse hover and mobile touch
        document.removeEventListener("mouseover", mouseOverListener, true);
        document.removeEventListener("mouseout", mouseOutListener, true);
        document.removeEventListener("touchstart", touchStartListener, true);
    };

    // Start Queue
    startQueue();

    // Start prefetching links in viewport on idle callback, with a delay
    requestIdleCallback(() => setTimeout(() => document.querySelectorAll("a").forEach(e => linksObserver.observe(e)), fp_data.delay * 1000));

    // Add event listeners to detect mouse hover and mobile touch
    const listenerOptions = {capture: true, passive: true};
    document.addEventListener("mouseover", mouseOverListener, listenerOptions);
    document.addEventListener("mouseout", mouseOutListener, listenerOptions);
    document.addEventListener("touchstart", touchStartListener, listenerOptions);
}

pprhFlyingPages();