document.addEventListener("DOMContentLoaded", function(event) {

    const jsCookie              = new jsCookieClass();
    const cookieEvent           = new CustomEvent('cookie:accepted');
    const cookieContainer       = document.getElementById('cookie-container');
    const cookieBanner          = document.getElementById('cookie-banner');
    const cookieAcceptButton    = document.getElementById('cookie-accept');
    const cookieAuthorizeInput  = document.querySelectorAll('.cookie-authorize-input');
    const cookieCancelButton    = document.getElementById('cookie-more-button');
    const cookiesNames          = cookieArgs.cookiesNames;
    const cookieDuration        = cookieArgs.cookieDuration;

    var CookieNotice = function(){
        var self = this;

        /**
         * Class initialisation
         */
        self.processCookieConsent = function() {
            /**
             * Get User Consent
             * @type {*|string}
             */
            const consentCookie = jsCookie.getCookie('hasConsent');

            /**
             * Get doNotTrack navigator attribute
             * @type {string | null | *}
             */
            const doNotTrack = navigator.doNotTrack || navigator.msDoNotTrack;

            /**
             * Set Cookie Accept Click Event
             */
            if(cookieAcceptButton)
                cookieAcceptButton.addEventListener('click', function(event){self.onDocumentClick(event)}, false);

            /**
             * Set Cookie Refuse Click Event
             */
            if(cookieCancelButton)
                cookieCancelButton.addEventListener('click', function(event){self.onMoreButtonClick(event)}, false);

            /**
             * Set Cookie List Input Change Event
             */
            if(cookieAuthorizeInput){
                for(var i = 0; i < cookieAuthorizeInput.length; i++){
                    cookieAuthorizeInput[i].addEventListener('change', function(event){self.onCheckboxChange(event)}, false);
                }

            }

            /**
             * If User refused cookie or doNotTrack is on, reject cookies
             */
            if (doNotTrack === 'yes' || doNotTrack === '1' || consentCookie === 'false') {
                self._rejectCookies();
                return;
            }

            /**
             * If User accepted cookie and doNotTrack is off, accept cookies & hide cookie bar
             */
            if (doNotTrack === 'no' || doNotTrack === '0' || consentCookie === 'true') {
                self._acceptCookies();
                return;
            }


            /**
             * If cookie banner doesn't exist, return, else display it
             */
            if(!cookieBanner)
                return;

            cookieContainer.classList.add('active');
            cookieBanner.classList.add('active');
            document.addEventListener('click', self.onDocumentClick, false);
        };

        /**
         * On input Checkbox Change Event
         * @param event
         */
        self.onCheckboxChange = function(event) {
            var currentTarget = event.currentTarget;
            var cookies = jsCookie.getCookie('unwantedCookies').split(',');
            var cookieNameExist = cookies.indexOf(currentTarget.name);
            if(currentTarget.checked) {
                if(cookieNameExist > -1){
                    cookies.splice(cookieNameExist, 1);
                }
            } else {
                if (cookieNameExist <= -1) {
                    cookies.push(currentTarget.name);
                }
            }

            self.rejectUnwantedCookies();

            jsCookie.setCookie('unwantedCookies', cookies, cookieDuration);
        };

        /**
         * On Refuse Cookie Button Click Event
         * @param event
         */
        self.onMoreButtonClick = function(event) {
            event.preventDefault();

            self._hideCookieBar();

            self._rejectCookies();

            window.location = cookieCancelButton.href;
        };

        /**
         * On Accept Cookie Document Click Event
         * @param event
         */
        self.onDocumentClick = function(event) {
            if ((event.target.id === 'cookie-banner'
                    || event.target.parentNode.id === 'cookie-banner'
                    || event.target.parentNode.parentNode.id === 'cookie-banner'
                    || event.target.id === 'cookie-more-button') && cookieBanner) {
                return;
            }

            var reload = jsCookie.getCookie('hasConsent') === 'false';

            event.preventDefault();

            self._acceptCookies();

            if(reload)
                window.location.reload();
        };

        /**
         * Hide Cookie Bar
         * @private
         */
        self._hideCookieBar = function(){
            if(!cookieBanner)
               return;

            cookieBanner.className = cookieBanner.className.replace('active', '').trim();
            cookieContainer.className = cookieContainer.className.replace('active', '').trim();

            // Remove old events
            document.removeEventListener('click', self.onDocumentClick, false);
            cookieAcceptButton.removeEventListener('click', self.onDocumentClick, false);
            cookieCancelButton.removeEventListener('click', self.onMoreButtonClick, false);
        };

        /**
         * Accept Cookies
         * @private
         */
        self._acceptCookies = function(){
            if(jsCookie.getCookie('hasConsent') === 'true')
                return;

            self._hideCookieBar();

            // Set Consent Cookie & Inform World
            jsCookie.setCookie('hasConsent', true, { expires: cookieDuration });
            window.dispatchEvent(cookieEvent);

            // Inject JavaScript from Interface Admin
            if(typeof cookie_banned_scripts !== 'undefined'){
                eval(cookie_banned_scripts);
            }

            // Launch Init Functions on Cookie Accept
            initCookieFunctions();
        };

        /**
         * Refuse Cookies
         * @private
         */
        self._rejectCookies = function() {
            self._hideCookieBar();

            // Remove All Cookies
            for(var i in cookiesNames){
                jsCookie.deleteCookie(cookiesNames[i]['cookie_name'], '/', window.location.hostname);
            }
            jsCookie.setCookie('hasConsent', false, { expires: cookieDuration });
        };

        /**
         * Refuse Specific Cookies
         * @public
         */
        self.rejectUnwantedCookies = function() {
            var cookies = jsCookie.getCookie('unwantedCookies').split(',');

            for(var i in cookies){
                jsCookie.deleteCookie(cookies[i], '/', window.location.hostname);
            }
        };
    };

    var cookie_notice = new CookieNotice();
    cookie_notice.processCookieConsent();

    // Remove unwanted cookies on window load
    window.addEventListener("load", function(event) {
        cookie_notice.rejectUnwantedCookies();
    });
});