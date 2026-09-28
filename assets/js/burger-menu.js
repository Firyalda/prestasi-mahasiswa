/**
 * Global Burger Menu JavaScript
 * Responsive Sidebar
 */

(function () {

    'use strict';

    const MOBILE_THRESHOLD = 992;

    let burgerBtn = null;
    let sidebar = null;
    let mainContent = null;
    let isMobile = false;


    // =====================================
    // CEK MODE MOBILE
    // =====================================

    function checkMobile() {
        return window.innerWidth <= MOBILE_THRESHOLD;
    }


    // =====================================
    // MOBILE
    // =====================================

    function openMobileSidebar() {

        if (!sidebar) return;

        sidebar.classList.remove('active');

    }


    function closeMobileSidebar() {

        if (!sidebar) return;

        sidebar.classList.add('active');

    }


    function toggleMobileSidebar() {

        if (!sidebar) return;

        /*
         * Jika sidebar sedang terlihat,
         * klik burger → sembunyikan.
         */
        if (!sidebar.classList.contains('active')) {

            closeMobileSidebar();

        } else {

            /*
             * Jika sidebar sedang tersembunyi,
             * klik burger → tampilkan.
             */
            openMobileSidebar();

        }

    }


    // =====================================
    // DESKTOP
    // =====================================

    function openDesktopSidebar() {

        if (!sidebar) return;

        sidebar.classList.remove('collapsed');

        if (mainContent) {
            mainContent.classList.remove('sidebar-collapsed');
        }

    }


    function closeDesktopSidebar() {

        if (!sidebar) return;

        sidebar.classList.add('collapsed');

        if (mainContent) {
            mainContent.classList.add('sidebar-collapsed');
        }

    }


    function toggleDesktopSidebar() {

        if (!sidebar) return;

        if (sidebar.classList.contains('collapsed')) {

            openDesktopSidebar();

        } else {

            closeDesktopSidebar();

        }

    }


    // =====================================
    // KLIK BURGER
    // =====================================

    function handleBurgerClick(event) {

        event.preventDefault();
        event.stopPropagation();

        if (isMobile) {

            toggleMobileSidebar();

        } else {

            toggleDesktopSidebar();

        }

    }


    // =====================================
    // RESIZE
    // =====================================

    function handleResize() {

        const currentMobile = checkMobile();

        if (currentMobile === isMobile) {
            return;
        }

        isMobile = currentMobile;


        if (isMobile) {

            /*
             * Masuk ke mode HP.
             * Sidebar terlihat terlebih dahulu.
             */
            sidebar.classList.remove('collapsed');
            sidebar.classList.remove('active');

            if (mainContent) {
                mainContent.classList.remove('sidebar-collapsed');
            }

        } else {

            /*
             * Masuk ke mode desktop.
             */
            sidebar.classList.remove('active');

            openDesktopSidebar();

        }

    }


    // =====================================
    // INITIALIZATION
    // =====================================

    function init() {

        burgerBtn = document.querySelector('.burger-menu');

        sidebar = document.querySelector('.sidebar');

        mainContent = document.querySelector('.main-content');


        /*
         * Jika halaman tidak memiliki burger/sidebar,
         * hentikan script.
         */
        if (!burgerBtn || !sidebar) {
            return;
        }


        isMobile = checkMobile();


        // =====================================
        // KONDISI AWAL
        // =====================================

        if (isMobile) {

            /*
             * HP:
             * sidebar terlihat saat pertama dibuka.
             */
            sidebar.classList.remove('active');
            sidebar.classList.remove('collapsed');

            if (mainContent) {
                mainContent.classList.remove('sidebar-collapsed');
            }

        } else {

            /*
             * Desktop:
             * sidebar normal.
             */
            sidebar.classList.remove('active');
            sidebar.classList.remove('collapsed');

            if (mainContent) {
                mainContent.classList.remove('sidebar-collapsed');
            }

        }


        // =====================================
        // EVENT
        // =====================================

        burgerBtn.addEventListener(
            'click',
            handleBurgerClick
        );

        window.addEventListener(
            'resize',
            handleResize
        );

    }


    // =====================================
    // PUBLIC API
    // =====================================

    window.BurgerMenu = {

        open: function () {

            if (isMobile) {
                openMobileSidebar();
            } else {
                openDesktopSidebar();
            }

        },

        close: function () {

            if (isMobile) {
                closeMobileSidebar();
            } else {
                closeDesktopSidebar();
            }

        },

        toggle: function () {

            if (isMobile) {
                toggleMobileSidebar();
            } else {
                toggleDesktopSidebar();
            }

        },

        init: init

    };


    // =====================================
    // AUTO INITIALIZE
    // =====================================

    if (document.readyState === 'loading') {

        document.addEventListener(
            'DOMContentLoaded',
            init
        );

    } else {

        init();

    }

})();