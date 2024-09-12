/**
 * This class implements pull to refresh.
 * To make this happen there is component inside app.blade.php and some css styles
 * inside app.css
 */
class PullToRefresh {
    constructor(standAloneOnly = true) {
        // Don't run the code if standalone is requested, and we are not on PWA (standalone)
        if (standAloneOnly && !this.isStandalone()) {
            return;
        }
        console.log("yes");
        const pullToRefresh     = document.querySelector('.pull-to-refresh');
        const pullToRefreshIcon = document.querySelector('.pull-to-refresh-icon');

        let touchstartY         = 0;
        let touchDiff           = 0;

        const refreshDelay      = 100;
        let refreshed           = false;

        document.addEventListener('touchstart', e => {
            touchstartY = e.touches[0].clientY;
        });

        document.addEventListener('touchmove', e => {
            const touchY = e.touches[0].clientY;
            touchDiff = touchY - touchstartY;

            if (touchDiff > 0 && window.scrollY === 0) {
                e.preventDefault();

                if (!pullToRefresh.classList.contains('visible')) {
                    pullToRefresh.classList.add('visible');
                }

                // Don't let the pull-to-refresh area get too big
                if (touchDiff <= 180) {
                    pullToRefresh.style.height = touchDiff+'px';
                }

                if (touchDiff > 170 && !refreshed) {
                    pullToRefreshIcon.classList.add('rotate');
                    refreshed = true;
                    setTimeout(() => {
                        location.reload();
                    }, refreshDelay)
                }
                else if (pullToRefresh.clientHeight >= 30 && pullToRefresh.clientHeight < 170) {
                    if (touchDiff <= 180) {
                        pullToRefreshIcon.style.opacity = (touchDiff-30)/100;
                        const rotateDeg = -180 + touchDiff;
                        pullToRefreshIcon.style.transform = 'rotate('+rotateDeg+'deg)';
                        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                            // Dark mode
                            pullToRefreshIcon.style.background = 'rgba(99, 102, 241, '+touchDiff/2/100+')';
                        }
                        else {
                            // Light mode
                            pullToRefreshIcon.style.background = 'rgba(165, 180, 252, '+touchDiff/2/100+')';
                        }
                        pullToRefreshIcon.style.padding = touchDiff/4+'px';
                    }
                }
            }
            else if (window.scrollY > 0) {
                if (pullToRefresh.classList.contains('visible')) {
                    pullToRefresh.classList.remove('visible');
                }
            }

            /**
             * Adding { passive: false } to the addEventListener ensures that the preventDefault() call is respected.
             * By default, touchmove events in iOS may be treated as passive to improve performance,
             * preventing preventDefault() from working.
             */
        }, { passive: false });


        document.addEventListener('touchend', e => {
            if (pullToRefresh.classList.contains('visible') && !refreshed) {
                // if (touchDiff >= 180) {
                //     location.reload();
                // }
                pullToRefresh.classList.remove('visible');
                pullToRefreshIcon.classList.remove('rotate');
                pullToRefresh.style.height = 0;
                pullToRefreshIcon.style.opacity = 0;
                pullToRefreshIcon.style.background = 'rgba(198, 200, 204, 0.5)';
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    // Dark mode
                    pullToRefreshIcon.style.background = 'rgba(99, 102, 241, 0.5)';
                }
                else {
                    // Light mode
                    pullToRefreshIcon.style.background = 'rgba(165, 180, 252, 0.5)';
                }
                pullToRefreshIcon.style.padding = 0;
            }
        });
    }

    isStandalone() {
        try {
            // For iOS
            if (window && window.navigator && window.navigator.standalone) {
                return true;
            }

            // For other platforms (like Android)
            return window.matchMedia('(display-mode: standalone)').matches;
        }
        catch (e) {}

        return false;
    }
}

export default PullToRefresh;
