<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Blueneba') }}</title>
    <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sora:wght@600;700;800&display=swap" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
    <!-- CSS Files -->
    <link href="{{ asset('assets/css/materialdashboard2.css?v=' . now()->timestamp) }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/newdesign.css?v=' . now()->timestamp) }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/custom1.css?v=' . now()->timestamp) }}" rel="stylesheet" />
    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link href="{{ asset('assets/demo/demo.css?v=' . now()->timestamp) }}" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.3/themes/base/jquery-ui.min.css" rel="stylesheet" />
    <link href="{{ asset('assets/css/responsive.bootstrap4.css?v=' . now()->timestamp) }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.css?v=' . now()->timestamp) }}">
    <!-- <link href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css" rel="stylesheet"> -->

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/5.5.2/css/fileinput.min.css"
        rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/5.5.2/js/fileinput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.3/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Icons+Outlined" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <meta http-equiv="Cache-Control" content="no-store" />
    <style>
        .iconimg {
            background: #fff;
            padding: 0px;
            height: 100%;
            min-height: 50px;
            object-fit: contain;
            margin: 6px;
        }

        .main-panel>.navbar {
            background: #FFFFFF;
            padding: 0 !important;
            z-index: 50 !important;
        }

        /* Google Font Import - Poppins */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        :root {
            /* ===== Colors ===== */
            --body-color: #E4E9F7;
            --sidebar-color: #FFF;
            --primary-color: #3860a4;
            --primary-color-light: #F6F5FF;
            --toggle-color: #DDD;
            --text-color: #707070;

            /* ====== Transition ====== */
            /*  --tran-03: all 0.2s ease;
      --tran-03: all 0.3s ease;
      --tran-04: all 0.3s ease;
      --tran-05: all 0.3s ease;*/

            --tran-03: unset;
            --tran-03: unset;
            --tran-04: unset;
            --tran-05: unset;
        }

        body {
            min-height: 100vh;
            background-color: var(--body-color);
            transition: var(--tran-05);
        }

        ::selection {
            background-color: us color: #fff;
        }

        .sidebar li.nav-link a {
            /* background-color: var(--primary-color); */
            color: #707070;
            font-weight: 500;
        }


        .sidebar li.nav-link:hover a {
            /* background-color: var(--primary-color); */
            color: #fff;
        }

        .sidebar li.nav-link ul.navd a {
            background: transparent;
            color: #fff;
            font-weight: 500;
            font-size: 16px;
        }

        body.dark {
            --body-color: #18191a;
            --sidebar-color: #242526;
            --primary-color: #3a3b3c;
            --primary-color-light: #3a3b3c;
            --toggle-color: #fff;
            --text-color: #ccc;
        }

        /* ===== Sidebar ===== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 280px;
            padding: 0;
            background: var(--sidebar-color);
            transition: var(--tran-05);
            z-index: 100;
            background: linear-gradient(45deg, #3860a4 0%, #3694cc 100%);
        }

        .sidebar.close {
            width: 88px;
        }

        .sidebar li.nav-link a:hover>a {
            color: #fff;
        }

        /* ===== Reusable code - Here ===== */
        .sidebar li.nav-link {
            /* height: 50px;*/
            list-style: none;
            /* display: flex; */
            align-items: center;
            margin-top: 10px;
            margin-bottom: 10px;
            padding: 0;
        }

        .sidebar header .image,
        .sidebar .icon {
            min-width: 50px;
            border-radius: 6px;
        }

        .sidebar .icon {
            min-width: 50px;
            border-radius: 6px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .sidebar .text,
        .sidebar .icon {
            /* color: #fff;*/
            /* transition: var(--tran-03);*/
        }


        .sidebar .text {
            font-size: 17px;
            font-weight: 400;
            white-space: nowrap;
            opacity: 1;
            color: #fff;
            padding-left: 8px;

        }

        .sidebar .bottom-content i {
            color: #fff;
        }

        .sidebar.close .text {
            opacity: 0;
        }

        /* =========================== */

        .sidebar header {
            position: relative;
        }

        .sidebar header .image-text {
            display: flex;
            align-items: center;
        }

        .sidebar header .logo-text {
            display: flex;
            flex-direction: column;
        }

        header .image-text .name {
            margin-top: 2px;
            font-size: 18px;
            font-weight: 600;
            color: #fff;
            white-space: normal;
        }

        header .image-text .profession {
            font-size: 16px;
            margin-top: -2px;
            display: block;
        }

        .sidebar header .image {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar header .image img {

            width: 40px;
            border-radius: 32px;
            height: 40px;
            object-fit: cover;
        }

        .sidebar header .toggle {
            position: absolute;
            top: 135%;
            right: -10px;
            transform: translateY(-50%) rotate(180deg);
            height: 25px;
            width: 25px;
            /* background-color: var(--primary-color); */
            background: #fff;
            color: #3860a4;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            cursor: pointer;
            transition: var(--tran-05);
        }

        body.dark .sidebar header .toggle {
            color: var(--text-color);
        }

        .sidebar.close .toggle {
            transform: translateY(-50%) rotate(0deg);
        }

        /*  .sidebar .menu {
      margin-top: 20px;
    }*/

        .sidebar li.search-box {
            border-radius: 6px;
            background-color: var(--primary-color-light);
            cursor: pointer;
            transition: var(--tran-05);
        }

        .sidebar li.search-box input {
            height: 100%;
            width: 100%;
            outline: none;
            border: none;
            background-color: var(--primary-color-light);
            color: var(--text-color);
            border-radius: 6px;
            font-size: 17px;
            font-weight: 500;
            transition: var(--tran-05);
        }

        .sidebar li.nav-link a {
            list-style: none;
            height: 100%;
            background-color: transparent;
            display: flex;
            align-items: center;
            height: 100%;
            width: 100%;
            border-radius: 6px;
            text-decoration: none;
            transition: var(--tran-03);
            /*   border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;*/
            padding: 8px 0px;
            position: relative;
        }


        .sidebar li.nav-link ul li a:after {
            position: unset;
            display: none
        }

        .sidebar li.nav-link a:after {
            position: absolute;
            content: "";
            width: 10px;
            height: 10px;
            background-color: transparent;
            border: 3px solid #fff;
            right: 15px;
            top: 15px;
            border-top: 0px;
            border-left: 0px;
            transform: rotate(45deg);
        }

        .sidebar li.nav-link a.no-after:after {
            display: none !important;
        }

        .sidebar li.nav-link a.active:after {
            position: absolute;
            content: "";
            width: 10px;
            height: 10px;
            background-color: #fff;
            border: 3px solid #3860a4;
            right: 15px;
            top: 15px;
            border-top: 0px;
            border-left: 0px;
            transform: rotate(45deg);
        }

        .sidebar li.nav-link a:hover {
            /* background-color: var(--primary-color);
*/
            color: #fff;
            background: linear-gradient(90deg, #3860a4 0%, #1b4e6c 100%);
        }

        .sidebar li.nav-link ul li a:hover .icon,
        .sidebar li.nav-link a:hover .text,
        .sidebar li.nav-link ul li a:hover span {
            /* color: var(--primary-color);*/
            color: #fff;
        }

        .sidebar li.nav-link ul li a:hover {
            background: #3860a4a3;
        }

        body.dark .sidebar li.nav-link a:hover .icon,
        body.dark .sidebar li.nav-link a:hover .text {
            color: var(--text-color);
        }



        .sidebar li.nav-link.active ul li.nav-link-btn.active .icon {
            color: #fff;
            font-weight: 500;
        }

        .sidebar .menu-bar {
            height: calc(100% - 162px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow-y: visible;
            margin-top: 20px;
            overflow-x: hidden;
        }

        .sidebar li.nav-link.active ul li .icon {
            color: #fff;
        }


        .sidebar .menu-bar::-webkit-scrollbar {
            width: 2px;
            background-color: transparent;
        }

        .sidebar .menu-bar::-webkit-scrollbar-thumb {
            border-radius: 2px;
            -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, .3);
            /*  background-color: var(--primary-color);*/
            background: linear-gradient(45deg, #3860a4 0%, #3694cc 100%);
        }

        /* .menu-bar::-webkit-scrollbar {
      display: none;
    }
*/
        .sidebar .menu-bar .mode {
            border-radius: 6px;
            background-color: var(--primary-color-light);
            position: relative;
            transition: var(--tran-05);
        }

        .menu-bar .mode .sun-moon {
            height: 50px;
            width: 60px;
        }

        .mode .sun-moon i {
            position: absolute;
        }

        .mode .sun-moon i.sun {
            opacity: 0;
        }

        body.dark .mode .sun-moon i.sun {
            opacity: 1;
        }

        body.dark .mode .sun-moon i.moon {
            opacity: 0;
        }

        .menu-bar .bottom-content .toggle-switch {
            position: absolute;
            right: 0;
            height: 100%;
            min-width: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            cursor: pointer;
        }

        .toggle-switch .switch {
            position: relative;
            height: 22px;
            width: 40px;
            border-radius: 25px;
            background-color: var(--toggle-color);
            transition: var(--tran-05);
        }

        .switch::before {
            content: '';
            position: absolute;
            height: 15px;
            width: 15px;
            border-radius: 50%;
            top: 50%;
            left: 5px;
            transform: translateY(-50%);
            background-color: var(--sidebar-color);
            transition: var(--tran-04);
        }

        body.dark .switch::before {
            left: 20px;
        }

        ..main-panel {
            position: absolute;
            top: 0;
            top: 0;
            left: 280px;
            height: 100vh;
            width: calc(100% - 280px);
            background-color: var(--body-color);
            transition: var(--tran-05);
        }

        .home .text {
            font-size: 30px;
            font-weight: 500;
            color: var(--text-color);
            padding: 12px 60px;
        }

        .sidebar.close~..main-panel {
            left: 78px;
            height: 100vh;
            width: calc(100% - 78px);
        }

        body.dark .home .text {
            color: var(--text-color);
        }

        .logo-main.desktop img {
            width: 75%;
            padding: 10px 5px;
        }

        .logo-main.mobile img {
            width: 68%;
        }


        .sidebar li.nav-link.active .text,
        .sidebar li.nav-link.active .icon {
            color: #fff;
        }

        /* li.nav-link a {
    color: #707070;
    font-weight: 500;
} */


        .sidebar li.nav-link.active a {
            background-color: var(--primary-color);
            color: #ffff;
            font-weight: 500;
        }

        ul.navd {
            background: transparent;
            height: 100%;
            z-index: 9;
            position: relative;
            padding: 10px 6px;
            border-bottom-left-radius: 6px;
            border-bottom-right-radius: 6px;
            /* box-shadow: 0 16px 38px -12px rgba(0, 0, 0, 0.56), 0 4px 25px 0px rgba(0, 0, 0, 0.12), 0 8px 10px -5px rgba(0, 0, 0, 0.2);*/

        }

        .sidebar li.nav-link a.collapsed {
            color: #fff;
            font-weight: 500;
            background: transparent;
        }


        .sidebar li.nav-link a {
            /* background-color: var(--primary-color);
    color: #ffff;
    font-weight: 500;*/
            background-color: transparent;
            color: #fff;
            font-weight: 600;
        }


        .sidebar li.nav-link a.collapsed:hover,
        .sidebar li.nav-link.active a.collapsed {
            background: linear-gradient(90deg, #3860a4 0%, #1b4e6c 100%);
            color: #ffff;
            font-weight: 500;
        }


        .sidebar li.nav-link.active a {
            background: linear-gradient(45deg, #3860a4 0%, #3694cc 100%);
        }


        .sidebar li.nav-link.active ul.navd li.nav-link-btn.active a {
            background: #3860a4a3;
            color: rgb(125, 143, 191);
            font-weight: 400;

        }

        ul.navd li.nav-link-btn {
            margin: 0px 0;
        }

        .sidebar.close li.nav-link a:after {

            width: 5px;
            height: 5px;
        }

        /* .sidebar.close~.main-panel {
    left: 78px;
    height: 100vh;
    width: calc(100% - 78px);
}
.main-panel {

    position: absolute;
    top: 0;
    top: 0;
    left: 250px;
    height: 100vh;
    width: calc(100% - 250px);
    background-color: var(--body-color);
    transition: var(--tran-05);
} */

        body .main-panel {
            position: absolute;
            top: 0;
            float: unset;
            left: 280px;
            height: unset !important;
            width: calc(100% - 280px);
            max-height: unset !important;
        }


        body .sidebar.close~.main-panel {
            left: 87px;
            height: 100vh;
            width: calc(100% - 87px);
        }

        .sidebar {
            z-index: 9999;
        }

        li {
            list-style: none;
        }

        /*ul.navd li.nav-link-btn.active, ul.navd li.nav-link-btn:hover {
    background: #00000094;
    border-radius: 8px;
}
*/
        nav.sidebar.close {
            opacity: 1;
        }


        nav.sidebar.close .icon {
            min-width: 60px;
        }

        nav.sidebar.close .menu-links span {
            display: none;
        }

        nav.sidebar.close li.nav-link a:after {
            right: 5px;
            top: 12px;
        }



        nav.sidebar.close .menu-bar {
            height: calc(100% - 106px);
            overflow-y: visible;
            margin-top: 20px;
            overflow-x: visible;
        }

        nav.sidebar.close .bottom-content {
            text-align: center;
        }


        nav.sidebar.close .bottom-content span.text.nav-text {
            display: none;
        }



        nav.sidebar.close ul.navd {
            padding: 6px 0px;

        }



        nav.sidebar .bottom-content li a {
            background: linear-gradient(45deg, #3860a4 0%, #3694cc 100%);
            color: #fff;
            padding: 10px 18px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        nav.sidebar .mobile {
            display: none;
        }

        nav.sidebar.close .mobile {
            display: block;
        }

        /*  .logo-main.mobile img {
      width: 39px;
      height: 100%;
      object-fit: contain;
    }*/

        nav.sidebar.close .desktop {
            display: none;
        }

        nav.sidebar.close li.nav-link a.hoveradd,
        nav.sidebar.close li.nav-link ul a.hoveradd2 {
            position: relative;
        }


        /*tablet css*/

        @media (max-width: 996px) {
            body nav.sidebar {
                display: block !important;
            }

            body .main-panel {
                position: relative;
                top: 0;
                float: unset;
                left: 0;
                width: calc(100% - 0px);

            }
        }

        nav.sidebar.close li.nav-link .d-none.mobile_hide {
            color: #fff;
            font-weight: 300;
            font-size: 13px;
            padding: 10px 10px;
            border-radius: 12px;
            position: absolute;
            right: -290%;
            top: 6%;
            z-index: 9999999;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            /*    transition: var(--tran-05);*/
            width: 100%;
            min-width: 161px;
            font-style: normal;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(90deg, #3860a4 0%, #1b4e6c 100%);
            letter-spacing: 1px;
        }

        body nav.sidebar.close li.nav-link a.hoveradd:hover .d-none.mobile_hide,
        body nav.sidebar.close li.nav-link ul li a.hoveradd2:hover .d-none.mobile_hide {
            display: block !important;
        }

        :root {
            --fk-bg: #050e24;
            --fk-panel: #081a40;
            --fk-panel-2: #0d1c40;
            --fk-border: rgba(120, 160, 255, .14);
            --fk-border-strong: rgba(34, 211, 238, .34);
            --fk-text: #ffffff;
            --fk-muted: #7d8fbf;
            --fk-soft: #a9bce6;
            --fk-accent: #22d3ee;
            --fk-accent-2: #3b82f6;
            --fk-side-open: 264px;
            --fk-side-closed: 78px;
        }

        body.fk-dark-shell,
        body.fk-dark-shell .wrapper,
        body.fk-dark-shell .main-panel,
        body.fk-dark-shell .content {
            background:
                radial-gradient(120% 90% at 18% 8%, #0d2358 0, transparent 55%),
                radial-gradient(110% 80% at 95% 95%, #0a1b45 0, transparent 60%),
                var(--fk-bg) !important;
            color: var(--fk-soft);
            font-family: 'Inter', 'Poppins', sans-serif;
        }

        body.fk-dark-shell .wrapper {
            min-height: 100vh;
            height: auto;
        }

        body.fk-dark-shell .sidebar {
            width: var(--fk-side-open);
            background: linear-gradient(180deg, #081a40, #050e24) !important;
            border-right: 1px solid rgba(90, 130, 220, .18);
            box-shadow: none;
            overflow: hidden;
            z-index: 1060;
        }

        body.fk-dark-shell .sidebar.close {
            width: var(--fk-side-closed);
        }

        body.fk-dark-shell .sidebar header {
            min-height: 104px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            justify-content: flex-start;
            padding: 0;
            border-bottom: 1px solid rgba(90, 130, 220, .14);
            background: #081a40;
            overflow: hidden;
        }

        body.fk-dark-shell .sidebar .logo,
        body.fk-dark-shell .sidebar .simple-text {
            width: 100%;
            min-width: 0;
            margin: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            display: flex;
        }

        body.fk-dark-shell .sidebar .logo {
            min-height: 72px;
            display: flex;
            align-items: center;
            padding: 16px 14px 14px !important;
            border-bottom: 1px solid rgba(90, 130, 220, .14);
        }

        body.fk-dark-shell .fk-sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            min-height: 42px;
            height: auto;
            overflow: visible;
        }

        body.fk-dark-shell .fk-field-logo {
            display: block;
            height: 28px;
            max-width: 142px;
            width: auto;
            object-fit: contain;
            object-position: left center;
            filter: drop-shadow(0 2px 10px rgba(34, 211, 238, .3));
            transition: opacity .18s;
        }

        body.fk-dark-shell .fk-duke-mark {
            margin-left: auto;
            width: auto;
            height: auto;
            display: grid;
            place-items: center;
            border: 1px solid rgba(120, 160, 255, .18);
            background: rgba(120, 160, 255, .06);
            border-radius: 9px;
            padding: 4px 8px;
            transition: opacity .18s;
            flex: none;
        }

        body.fk-dark-shell .fk-duke-mark img {
            height: 18px;
            max-width: none;
            width: auto;
            display: block;
            object-fit: contain;
        }

        body.fk-dark-shell .fk-sidebar-tagline {
            height: 31px;
            display: flex;
            align-items: center;
            padding: 10px 16px 0;
            color: #6d82c0;
            font-family: 'Sora', 'Inter', sans-serif;
            font-size: 7.5px;
            font-weight: 700;
            letter-spacing: 2.4px;
            line-height: 1;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
        }

        body.fk-dark-shell .sidebar.close .fk-field-logo {
            display: none;
        }

        body.fk-dark-shell .sidebar.close .fk-sidebar-tagline {
            opacity: 0;
            pointer-events: none;
        }

        body.fk-dark-shell .sidebar.close .logo {
            padding: 0 12px !important;
        }

        body.fk-dark-shell .sidebar.close .fk-sidebar-brand {
            justify-content: center;
        }

        body.fk-dark-shell .sidebar.close .fk-duke-mark {
            width: 44px;
            height: 36px;
            margin: 0 auto;
        }

        body.fk-dark-shell .sidebar header .toggle {
            display: none;
        }

        body.fk-dark-shell .sidebar .menu-bar {
            height: calc(100vh - 104px);
            margin-top: 0;
            padding: 8px 12px 16px;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: #17386f #04112d;
        }

        body.fk-dark-shell .sidebar .menu-bar::-webkit-scrollbar {
            width: 2px;
        }

        body.fk-dark-shell .sidebar .menu-bar::-webkit-scrollbar-track {
            background: #04112d;
            border-radius: 20px;
        }

        body.fk-dark-shell .sidebar .menu-bar::-webkit-scrollbar-thumb {
            background: #17386f;
            border-radius: 20px;
        }

        body.fk-dark-shell .menu-links {
            padding: 0;
            margin: 0;
        }

        body.fk-dark-shell .fk-menu-section {
            margin-top: 14px;
            padding: 2px 10px 6px;
            font-family: 'Sora', 'Inter', sans-serif;
            font-size: 8.5px;
            line-height: 1.2;
            letter-spacing: 2.2px;
            text-transform: uppercase;
            color: #4d5f95;
            font-weight: 700;
            white-space: nowrap;
        }

        body.fk-dark-shell .fk-menu-section:first-child {
            margin-top: 0;
        }

        body.fk-dark-shell .sidebar.close .fk-menu-section span {
            display: none;
        }

        body.fk-dark-shell .sidebar li.nav-link,
        body.fk-dark-shell .sidebar li.nav-link-btn,
        body.fk-dark-shell .sidebar li.nav-item-btn {
            margin: 0 0 5px;
            padding: 0;
            list-style: none;
        }

        body.fk-dark-shell .sidebar li.nav-link>a,
        body.fk-dark-shell .sidebar li.nav-link-btn>a,
        body.fk-dark-shell .sidebar li.nav-item-btn>a {
            min-height: 42px;
            padding: 0 18px;
            gap: 11px;
            border: 1px solid transparent;
            border-radius: 11px;
            background: transparent !important;
            color: #a9bce6 !important;
            font-family: 'Inter', sans-serif;
            font-size: 12.5px;
            font-weight: 500;
            line-height: 1.25;
            box-shadow: none !important;
            letter-spacing: 0;
        }

        body.fk-dark-shell .sidebar li.nav-link>a:hover,
        body.fk-dark-shell .sidebar li.nav-link-btn>a:hover,
        body.fk-dark-shell .sidebar li.nav-item-btn>a:hover,
        body.fk-dark-shell .sidebar li.nav-link>a[aria-expanded="true"] {
            color: #fff !important;
            background: rgba(34, 211, 238, .07) !important;
            border-color: rgba(34, 211, 238, .18);
        }

        body.fk-dark-shell .sidebar li.nav-link.active>a,
        body.fk-dark-shell .sidebar li.nav-link>a[aria-expanded="true"] {
            color: #fff !important;
            background: rgba(34, 211, 238, .07) !important;
            border-color: rgba(34, 211, 238, .16);
            box-shadow:
                inset 3px 0 0 var(--fk-accent),
                0 0 18px -8px rgba(34, 211, 238, .45) !important;
        }

        body.fk-dark-shell .sidebar li.nav-link a:after {
            border-color: #5a6a95;
            width: 7px;
            height: 7px;
            right: 12px;
            top: 16px;
            border-width: 2px;
            border-top: 0;
            border-left: 0;
        }

        body.fk-dark-shell .sidebar li.nav-link a.no-after:after,
        body.fk-dark-shell .sidebar li.nav-link-btn>a:after,
        body.fk-dark-shell .sidebar li.nav-item-btn>a:after {
            display: none !important;
        }

        body.fk-dark-shell .sidebar li.nav-link a[aria-expanded="true"]:after {
            transform: rotate(225deg);
            top: 18px;
            border-color: #5a6a95;
            background: transparent;
        }

        body.fk-dark-shell .sidebar .icon {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            min-width: 22px;
            width: 22px;
            height: 22px;
            font-size: 20px;
            letter-spacing: normal;
            text-transform: none;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
            color: inherit !important;
            border-radius: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        body.fk-dark-shell .sidebar .menu-links span {
            color: inherit;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        body.fk-dark-shell ul.navd {
            margin: 2px 0 6px 20px;
            padding: 0 0 0 8px;
            background: transparent;
            border-radius: 0;
            border-left: 1px solid rgba(90, 130, 220, .20);
        }

        body.fk-dark-shell ul.navd li>a {
            position: relative;
            min-height: 34px;
            padding: 0 10px 0 24px;
            color: #7d8fbf !important;
            font-family: 'Inter', sans-serif;
            font-size: 12.8px;
            border-radius: 9px;
            font-weight: 500;
        }

        body.fk-dark-shell ul.navd li>a .icon {
            display: none;
        }

        body.fk-dark-shell ul.navd li>a:before {
            content: "";
            position: absolute;
            left: 2px;
            top: 50%;
            width: 5px;
            height: 5px;
            border-radius: 999px;
            background: currentColor;
            opacity: .5;
            transform: translateY(-50%);
        }

        body.fk-dark-shell ul.navd li.active>a {
            color: #fff !important;
            background: rgba(34, 211, 238, .13) !important;
            font-weight: 600;
            box-shadow: 0 0 18px -6px rgba(34, 211, 238, .6) !important;
        }

        body.fk-dark-shell ul.navd li.active>a:before {
            background: var(--fk-accent);
            opacity: 1;
            box-shadow: 0 0 8px var(--fk-accent);
        }

        body.fk-dark-shell ul.navd li.active:has(> .collapse.show)>a {
            color: #a9bce6 !important;
            background: transparent !important;
            font-weight: 500;
            box-shadow: none !important;
        }

        body.fk-dark-shell ul.navd li.active:has(> .collapse.show)>a:before {
            background: currentColor;
            opacity: .5;
            box-shadow: none;
        }

        body.fk-dark-shell ul.navd li.active:has(> .collapse.show)>a[aria-expanded="true"] {
            color: #fff !important;
            background: rgba(34, 211, 238, .07) !important;
            border-color: rgba(34, 211, 238, .16);
        }

        body.fk-dark-shell .sidebar.close .menu-links span,
        body.fk-dark-shell .sidebar.close ul.navd {
            display: none;
        }

        body.fk-dark-shell .sidebar.close li.nav-link>a {
            justify-content: center;
            padding: 0;
        }

        body.fk-dark-shell .sidebar.close .icon {
            min-width: 42px;
        }

        body.fk-dark-shell .main-panel {
            left: var(--fk-side-open);
            width: calc(100% - var(--fk-side-open));
            min-height: 100vh;
            transition: left .28s cubic-bezier(.2, .8, .2, 1), width .28s cubic-bezier(.2, .8, .2, 1);
        }

        body.fk-dark-shell .sidebar.close~.main-panel {
            left: var(--fk-side-closed);
            width: calc(100% - var(--fk-side-closed));
        }

        body.fk-dark-shell .main-panel>.navbar {
            min-height: 64px;
            height: 64px;
            padding: 0 20px !important;
            background: rgba(5, 14, 36, .72) !important;
            border-bottom: 1px solid rgba(90, 130, 220, .15);
            backdrop-filter: blur(18px);
            box-shadow: none;
        }

        body.fk-dark-shell .main-panel>.navbar .container-fluid {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            max-width: none;
            min-height: 64px;
            background: transparent !important;
            padding: 0;
        }

        body.fk-dark-shell .fk-header-left,
        body.fk-dark-shell .fk-header-right {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        body.fk-dark-shell .fk-header-right {
            margin-left: auto;
            flex: none;
        }

        body.fk-dark-shell .fk-header-btn,
        body.fk-dark-shell .fk-live-sync,
        body.fk-dark-shell .fk-bell-btn {
            height: 42px;
            border: 1px solid rgba(90, 130, 220, .28);
            border-radius: 12px;
            background: rgba(8, 20, 50, .62);
            color: #a9bce6;
            box-shadow: none;
        }

        body.fk-dark-shell .fk-header-btn {
            width: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            cursor: pointer;
        }

        body.fk-dark-shell .fk-header-btn .material-symbols-outlined,
        body.fk-dark-shell .fk-bell-btn .material-symbols-outlined,
        body.fk-dark-shell .fk-user-trigger .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            font-size: 20px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
        }

        body.fk-dark-shell .fk-live-sync {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 0 18px;
            border-color: rgba(34, 211, 238, .35);
            color: var(--fk-accent);
            font-family: 'Sora', 'Inter', sans-serif;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2.5px;
            text-transform: uppercase;
        }

        body.fk-dark-shell .fk-live-sync:before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: var(--fk-accent);
            box-shadow: 0 0 10px rgba(34, 211, 238, .85);
            animation: 1.4s ease 0s infinite normal none running bl;
        }

        @keyframes bl {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.25;
                transform: scale(1.4);
            }
        }

        body.fk-dark-shell .fk-bell-btn {
            position: relative;
            width: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            color: #a9bce6;
        }

        body.fk-dark-shell .fk-bell-btn:after {
            content: "";
            position: absolute;
            top: 9px;
            right: 10px;
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #ff5d7a;
            box-shadow: 0 0 8px rgba(255, 93, 122, .7);
        }

        body.fk-dark-shell .fk-user-menu {
            position: relative;
        }

        body.fk-dark-shell .fk-user-trigger {
            min-width: 182px;
            height: 52px;
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 0 10px 0 0;
            border: 0;
            background: transparent;
            color: #fff;
            text-decoration: none;
        }

        body.fk-dark-shell .fk-user-avatar {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            background: linear-gradient(135deg, #22d3ee, #3b82f6);
            color: #04121f;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 800;
            overflow: hidden;
            flex: none;
        }

        body.fk-dark-shell .fk-user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        body.fk-dark-shell .fk-user-copy {
            min-width: 0;
            flex: 1;
        }

        body.fk-dark-shell .fk-user-name,
        body.fk-dark-shell .fk-user-role {
            display: block;
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            line-height: 1.15;
        }

        body.fk-dark-shell .fk-user-name {
            color: #fff;
            font-size: 13.5px;
            font-weight: 700;
        }

        body.fk-dark-shell .fk-user-role {
            margin-top: 3px;
            color: #6d82c0;
            font-size: 11px;
            font-weight: 500;
        }

        body.fk-dark-shell .fk-user-trigger .fk-chevron {
            color: #5a6a95;
            font-size: 18px;
            flex: none;
        }

        body.fk-dark-shell .fk-user-menu .dropdown-menu {
            min-width: 180px;
            padding: 8px;
            margin-top: 6px;
            border: 1px solid rgba(90, 130, 220, .22);
            border-radius: 12px;
            background: #081a40;
            box-shadow: 0 18px 40px rgba(0, 0, 0, .35);
        }

        body.fk-dark-shell .fk-user-menu .dropdown-item {
            display: flex;
            align-items: center;
            gap: 9px;
            min-height: 36px;
            border-radius: 9px;
            color: #a9bce6 !important;
            font-size: 13px;
            font-weight: 600;
        }

        body.fk-dark-shell .fk-user-menu .dropdown-item:hover {
            background: rgba(34, 211, 238, .08);
            color: #fff !important;
        }

        body.fk-dark-shell .fk-user-menu .dropdown-item .material-symbols-outlined {
            color: inherit !important;
            font-size: 18px;
        }

        body.fk-dark-shell .navbar .nav-item p,
        body.fk-dark-shell .navbar .material-icons {
            color: #fff !important;
        }

        body.fk-dark-shell .content {
            margin-top: 5px !important;
            padding: 12px 28px 44px;
        }

        body.fk-dark-shell .container-fluid {
            max-width: 1560px;
        }

        body.fk-dark-shell .footer {
            display: none;
        }

        @media (max-width: 996px) {
            body.fk-dark-shell .sidebar {
                transform: translateX(-110%);
                width: 288px;
            }

            body.fk-dark-shell .sidebar.close {
                transform: translateX(0);
                width: 288px;
            }

            body.fk-dark-shell .sidebar.close .menu-links span,
            body.fk-dark-shell .sidebar.close ul.navd,
            body.fk-dark-shell .sidebar.close .fk-field-logo,
            body.fk-dark-shell .sidebar.close .fk-duke-mark,
            body.fk-dark-shell .sidebar.close .fk-sidebar-tagline,
            body.fk-dark-shell .sidebar.close .fk-menu-section span {
                display: block;
                opacity: 1;
            }

            body.fk-dark-shell .sidebar.close .fk-sidebar-tagline {
                display: flex;
            }

            body.fk-dark-shell .main-panel,
            body.fk-dark-shell .sidebar.close~.main-panel {
                left: 0;
                width: 100%;
            }

            body.fk-dark-shell .content {
                padding: 10px 12px 32px;
            }

            body.fk-dark-shell .main-panel>.navbar {
                padding: 0 12px !important;
            }

            body.fk-dark-shell .fk-live-sync {
                display: none;
            }

            body.fk-dark-shell .fk-user-trigger {
                min-width: 52px;
                padding-right: 0;
            }

            body.fk-dark-shell .fk-user-copy,
            body.fk-dark-shell .fk-user-trigger .fk-chevron {
                display: none;
            }

        }
    </style>
    <!-- Scripts -->
</head>

<body class="fk-shell fk-dark-shell">
    <!-- Loader -->
    <div class="loader-container" id="loader">
        <div class="loader"></div>
    </div>
    <div class="wrapper">
        <nav class="sidebar">
            <header>
                <div class="logo">
                    <a href="{{ url('customers') }}" class="simple-text logo-normal fk-sidebar-brand">
                        <img src="{{ asset('assets/img/fieldkonnect_login_logo.png') }}" class="fk-field-logo" alt="FieldKonnect">
                        <span class="fk-duke-mark">
                            <img src="{{ asset('assets/img/duke_logo_new.png') }}" alt="Duke Pipes">
                        </span>
                    </a>
                </div>
                <div class="fk-sidebar-tagline">Range &middot; Availability &middot; Reach &middot; Engagement</div>
                {{--<div class="image-text mt-2">
               <span class="image">
                  <img
                     src="{!! (count(Auth::user()->getMedia('profile_image')) > 0 ? Auth::user()->getMedia('profile_image')[0]->getFullUrl() : asset('assets/img/profileuser.png?')) !!}"
                     alt="">
               </span>
               <div class="text logo-text">
                  <span class="name"> {!! Auth::user()->name !!}</span>
               </div>
            </div>--}}
                <i class='bx bx-chevron-right toggle'></i>
            </header>
            <div class="menu-bar">
                <div class="menu">
                    <ul class="menu-links">
                        <li class="fk-menu-section"><span>Overview</span></li>
                        @if(auth()->user()->can(['dashboard_access']))
                        <li class="nav-link hide_icon {{ request()->is('dealer_dashboard') ? 'active' : '' }}">
                            <a class="collapsed hoveradd" href="{{ url('dashboard') }}">
                                <i class="material-icons icon">dashboard</i>
                                <span>{!! trans('panel.sidemenu.dashboard') !!}</span>
                                <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.dashboard') !!}</div>
                            </a>
                        </li>
                        @endif
                        <li class="fk-menu-section"><span>Customers</span></li>
                        @if(auth()->user()->can(['lead_management_access']))
                        <li
                            class="nav-link {{ request()->is('leads*') || request()->is('contacts*') ? 'active' : '' }}">
                            <a class="collapsed hoveradd" data-toggle="collapse" href="#leadManagementMenu"
                                aria-expanded="false">
                                <i class="material-icons icon">leaderboard</i>
                                <span> Lead Management
                                </span>
                                <div class="d-none mobile_hide">Lead Management</div>
                            </a>
                            <div class="collapse" id="leadManagementMenu" style="">
                                <ul class="navd">
                                    @if(auth()->user()->can(['lead_access']))
                                    <li class="nav-link-btn {{ request()->is('leads*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('leads') }}">
                                            <i class="material-icons icon">diamond</i>
                                            <span>Leads</span>
                                            <div class="d-none mobile_hide">Leads</div>
                                        </a>
                                    </li>

                                    <li class="nav-link-btn {{ request()->is('lead-contacts*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('lead-contacts') }}">
                                            <i class="material-icons icon">diamond</i>
                                            <span>Contacts</span>
                                            <div class="d-none mobile_hide">Contacts</div>
                                        </a>
                                    </li>
                                    <li class="nav-link-btn {{ request()->is('lead-opportunities*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('lead-opportunities') }}">
                                            <i class="material-icons icon">diamond</i>
                                            <span>Opportunities</span>
                                            <div class="d-none mobile_hide">Opportunities</div>
                                        </a>
                                    </li>
                                    @if(auth()->user()->can(['opportunities_status_access']))
                                    <li
                                        class="nav-link-btn {{ request()->is('lead-opportunities-status*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('lead-opportunities-status') }}">
                                            <i class="material-icons icon">diamond</i>
                                            <span>Opportunities Status</span>
                                            <div class="d-none mobile_hide">Opportunities Status</div>
                                        </a>
                                    </li>
                                    @endif
                                    <li class="nav-link-btn {{ request()->is('lead-tasks*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('lead-tasks') }}">
                                            <i class="material-icons icon">diamond</i>
                                            <span>Tasks</span>
                                            <div class="d-none mobile_hide">Tasks</div>
                                        </a>
                                    </li>
                                    @if(auth()->user()->can(['lead_visit_access']))
                                    <li class="nav-link-btn {{ request()->is('lead_visit_report*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('lead_visit_report') }}">
                                            <i class="material-icons icon">diamond</i>
                                            <span>Visit Report</span>
                                            <div class="d-none mobile_hide">Visit Report</div>
                                        </a>
                                    </li>
                                    @endif
                                    @endif
                                </ul>
                            </div>
                        </li>
                        @endif


                        <!-- <li class="nav-link
                              {{ request()->is('master-distributors*') ? 'active' : '' }}">

                            <a class="collapsed hoveradd" data-toggle="collapse" href="#masterDistributorMenu"
                                aria-expanded="false">

                                <i class="material-icons icon">store</i>

                                <span>
                                    Customer Management
                                </span>

                                <div class="d-none mobile_hide">
                                    Customer Management
                                </div>
                            </a>

                            <div class="collapse" id="masterDistributorMenu">
                                <ul class="navd">
                                    @if(auth()->user()->can(['master_distributor_access']))
                                    <li class="nav-link-btn {{ request()->is('master-distributors*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ route('master-distributors.index') }}">
                                            <i class="material-icons icon">inventory</i>
                                            <span>{!! trans('panel.sidemenu.master_distributor_list') !!}</span>
                                            <div class="d-none mobile_hide">{!!
                                                trans('panel.sidemenu.master_distributor_list') !!}</div>
                                        </a>
                                    </li>
                                    @endif

                                    @if(auth()->user()->can(['retailer_access']))
                                    <li class="nav-link-btn {{ request()->is('retailers*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ route('retailers.index') }}">
                                            <i class="material-icons icon">storefront</i>
                                            <span>Retailer</span>
                                            <div class="d-none mobile_hide">Retailer</div>
                                        </a>
                                    </li>
                                    @endif

                                </ul>

                            </div>
                        </li> -->
                        <!-- ----------------------------------- -->

                        @if(auth()->user()->can(['customer_access']))
                        <li
                            class="nav-link {{ request()->is('customers*') || request()->is('customertype*') || request()->is('firmtype*') || request()->is('customersLogin*') || request()->is('customers-survey*') || request()->is('fields*') ? 'active' : '' }}">
                            <a class="collapsed hoveradd" data-toggle="collapse" href="#customerMenu"
                                aria-expanded="false">
                                <i class="material-icons icon">diversity_3</i>
                                <span> {!! trans('panel.sidemenu.customers_master') !!} Management
                                </span>
                                <div class="d-none mobile_hide">{!! trans('panel.sidemenu.customers_master') !!} Management</div>
                            </a>
                            <div class="collapse" id="customerMenu" style="">
                                <ul class="navd">
                                    @if(auth()->user()->can(['customer_access']))
                                    <li class="nav-link-btn {{ request()->is('customers*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('customers') }}">
                                            <i class="material-icons icon">transcribe</i>
                                            <span>{!! trans('panel.sidemenu.customers') !!}</span>
                                            <div class="d-none mobile_hide">{!! trans('panel.sidemenu.customers') !!}
                                            </div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can(['customer_balance_confirmation_upload']))
                                    <li class="nav-link-btn {{ request()->is('customer_balance*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('customer_balance') }}">
                                            <i class="material-icons icon">balance</i>
                                            <span>Upload Balance Confirmation</span>
                                            <div class="d-none mobile_hide">Upload Balance Confirmation</div>
                                        </a>
                                    </li>
                                    @endif
                                    <!-- @if(auth()->user()->can(['distributor_access'])) -->
                                    <!-- <li class="nav-item {{ request()->is('distributors*') ? 'active' : '' }}">
                                 <a class="nav-link" href="{{ url('distributors') }}">
                                   <i class="material-icons">store</i>
                                   <p>{!! trans('panel.sidemenu.distributors') !!}</p>
                                 </a>
                                 </li> -->
                                    <!-- @endif -->
                                    @if(auth()->user()->can('customertype_access'))
                                    <li class="nav-link-btn {{ request()->is('customertype*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('customertype') }}">
                                            <i class="material-icons icon">library_books</i>
                                            <span>{!! trans('panel.sidemenu.customertype') !!}</span>
                                            <div class="d-none mobile_hide">{!! trans('panel.sidemenu.customertype') !!}
                                            </div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('firmtype_access'))
                                    <li class="nav-link-btn {{ request()->is('firmtype*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('firmtype') }}">
                                            <i class="material-icons icon">bubble_chart</i>
                                            <span>{!! trans('panel.sidemenu.firmtype') !!}</span>
                                            <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.firmtype') !!}
                                            </div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('customer_login'))
                                    <li class="nav-link-btn {{ request()->is('customersLogin*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('customersLogin') }}">
                                            <i class="material-icons icon">login</i>
                                            <span>{!! trans('panel.sidemenu.customersLogin') !!}</span>
                                            <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.customersLogin')
                                                !!}</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('survey_access'))
                                    <li class="nav-link-btn {{ request()->is('customers-survey*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('customers-survey') }}">
                                            <i class="material-icons icon">sentiment_satisfied</i>
                                            <span>Customers Survey</span>
                                            <div class="d-none mobile_hide"> Customers Survey</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('field_access'))
                                    <li class="nav-link-btn {{ request()->is('fields*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('fields') }}">
                                            <i class="material-icons icon">text_rotation_angleup</i>
                                            <span>Survey Field</span>
                                            <div class="d-none mobile_hide"> Survey Field</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('market_intelligence_access'))
                                    <li
                                        class="nav-link-btn {{ request()->is('market_intelligences*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('market_intelligences') }}">
                                            <i class="material-icons icon">key</i>
                                            <span>Market Intelligence</span>
                                            <div class="d-none mobile_hide"> Market Intelligence</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('dealer_appointment'))
                                    <li class="nav-link-btn {{ request()->is('dealer-appointment*') ? 'active' : '' }}">
                                        <a class="nav-link hoveradd2" href="{{ url('dealer-appointments') }}">
                                            <i class="material-icons icon">pending_actions</i>
                                            <span>Dealer / Distributor Appointment</span>
                                            <div class="d-none mobile_hide"> Dealer / Distributor Appointment</div>
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                        @endif
                        @if(auth()->user()->can('country_access'))
                        <li
                            class="nav-link {{ request()->is('country') || request()->is('state') || request()->is('district*') || request()->is('city*') || request()->is('pincode*') ? 'active' : '' }}">
                            <a class="collapsed hoveradd " data-toggle="collapse" href="#addressMenu"
                                aria-expanded="false">
                                <i class="material-icons icon">contact_mail</i>
                                <span> {!! trans('panel.sidemenu.address_master') !!} Management
                                </span>
                                <div class="d-none mobile_hide">{!! trans('panel.sidemenu.address_master')
                                    !!} Management</div>
                            </a>
                            <div class="collapse" id="addressMenu" style="">
                                <ul class="navd">
                                    @if(auth()->user()->can('country_access'))
                                    <li
                                        class="nav-link-btn {{ request()->is('country*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('country') }}">
                                            <i class="material-icons icon">flag_circle</i>
                                            <span>{!! trans('panel.sidemenu.address_country') !!}</span>
                                            <div class="d-none mobile_hide">{!!
                                                trans('panel.sidemenu.address_country') !!}</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('state_access'))
                                    <li class="nav-link-btn {{ request()->is('state*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('state') }}">
                                            <i class="material-icons icon">location_city</i>
                                            <span>{!! trans('panel.sidemenu.address_state') !!}</span>
                                            <div class="d-none mobile_hide">{!!
                                                trans('panel.sidemenu.address_state') !!}</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('district_access'))
                                    <li
                                        class="nav-link-btn {{ request()->is('district*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('district') }}">
                                            <i class="material-icons icon">balcony</i>
                                            <span>{!! trans('panel.sidemenu.address_district') !!}</span>
                                            <div class="d-none mobile_hide">{!!
                                                trans('panel.sidemenu.address_district') !!}</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('city_access'))
                                    <li class="nav-link-btn {{ request()->is('city') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('city') }}">
                                            <i class="material-icons icon">apartment</i>
                                            <span>{!! trans('panel.sidemenu.address_city') !!}</span>
                                            <div class="d-none mobile_hide">{!!
                                                trans('panel.sidemenu.address_city') !!}</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('pincode_access'))
                                    <li
                                        class="nav-link-btn {{ request()->is('pincode*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('pincode') }}">
                                            <i class="material-icons icon">cabin</i>
                                            <span>{!! trans('panel.sidemenu.address_pincode') !!}</span>
                                            <div class="d-none mobile_hide">{!!
                                                trans('panel.sidemenu.address_pincode') !!}</div>
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                        @endif
                        @if(auth()->user()->can(['expenses_type']))
                        <!-- <li class="nav-item {{ request()->is('expenses_type') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('expenses_type') }}">
                          <i class="material-icons">dashboard</i>
                          <p>{!! trans('panel.sidemenu.expenses_type') !!}</p>
                        </a>
                        </li> -->
                        @endif
                        <li class="fk-menu-section"><span>Catalogue &amp; Supply</span></li>
                        @if(auth()->user()->can('product_access'))
                        <li
                            class="nav-link {{ request()->is('categories*') || request()->is('subcategories*') || request()->is('brands*') || request()->is('products*') || request()->is('units*') || request()->is('production*') ? 'active' : '' }}">
                            <a class="collapsed hoveradd" data-toggle="collapse" href="#productMenu"
                                aria-expanded="false">
                                <i class="material-icons icon">conveyor_belt</i>
                                <!-- <span> {!! trans('panel.sidemenu.product_master') !!}</span> -->
                                <span>Product Management</span>
                                <div class="d-none mobile_hide">{!! trans('panel.sidemenu.product_master') !!}</div>
                            </a>
                            <div class="collapse" id="productMenu" style="">
                                <ul class="navd">
                                    @if(auth()->user()->can('category_access'))
                                    <li class="nav-link-btn {{ request()->is('categories*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('categories') }}">
                                            <i class="material-icons icon">category</i>
                                            <span>{!! trans('panel.sidemenu.categories') !!}</span>
                                            <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.categories') !!}
                                            </div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('subcategory_access'))
                                    <li class="nav-link-btn {{ request()->is('subcategories*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('subcategories') }}">
                                            <i class="material-icons icon">subtitles</i>
                                            <span>{!! trans('panel.sidemenu.subcategories') !!}</span>
                                            <div class="d-none mobile_hide">{!! trans('panel.sidemenu.subcategories')
                                                !!}</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('brand_access'))
                                    <li class="nav-link-btn {{ request()->is('brands*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('brands') }}">
                                            <i class="material-icons icon">branding_watermark</i>
                                            <span>Makers</span>
                                            <div class="d-none mobile_hide">{!! trans('panel.sidemenu.brands') !!}</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('product_access'))
                                    <li class="nav-link-btn {{ request()->is('products*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('products') }}">
                                            <i class="material-icons icon">widgets</i>
                                            <span>{!! trans('panel.sidemenu.products') !!}</span>
                                            <div class="d-none mobile_hide">{!! trans('panel.sidemenu.products') !!}
                                            </div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('gift_access'))
                                    <!-- <li class="nav-item {{ request()->is('gifts*') ? 'active' : '' }}">
                                 <a class="hoveradd2" href="{{ url('gifts') }}">
                                   <i class="material-icons">donut_large</i>
                                   <p>{!! trans('panel.sidemenu.gifts') !!}</p>
                                 </a>
                                 </li> -->
                                    @endif
                                    @if(auth()->user()->can('unit_access'))
                                    <li class="nav-link-btn {{ request()->is('units*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('units') }}">
                                            <i class="material-icons icon">apartment</i>
                                            <span>{!! trans('panel.sidemenu.units') !!}</span>
                                            <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.units') !!}</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('stock_access'))
                                    <li class="nav-link-btn {{ request()->is('stock*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('stock') }}">
                                            <i class="material-icons icon">donut_small</i>
                                            <span>Stock</span>
                                            <div class="d-none mobile_hide"> Stock</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('sap_stock_access'))
                                    <li class="nav-link-btn {{ request()->is('sap_stock*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('sap_stock') }}">
                                            <i class="material-icons icon">donut_small</i>
                                            <span>SAP Stock</span>
                                            <div class="d-none mobile_hide"> SAP Stock</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('opening_stock_view'))
                                    <li class="nav-link-btn {{ request()->is('opening-stocks*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('opening-stocks') }}">
                                            <i class="material-icons icon">donut_small</i>
                                            <span>Opening Stock</span>
                                            <div class="d-none mobile_hide"> Opening Stock</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('branch_opening_qty_view'))
                                    <li class="nav-link-btn {{ request()->is('opening-quantity*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('opening-quantity') }}">
                                            <i class="material-icons icon">donut_small</i>
                                            <span>Branch Opening Quantity</span>
                                            <div class="d-none mobile_hide"> Branch Opening Quantity</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('ware_house_access'))
                                    <li class="nav-link-btn {{ request()->is('ware_house*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('ware_house') }}">
                                            <i class="material-icons icon">warehouse</i>
                                            <span>Ware House</span>
                                            <div class="d-none mobile_hide"> Ware House</div>
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                        @endif

                        @if(auth()->user()->can('dealer_product_access'))
                        <li class="nav-link hide_icon {{ request()->is('dealer_product*') ? 'active' : '' }}">
                            <a class="hoveradd" href="{{ url('dealer_product') }}">
                                <i class="material-icons icon">flaky</i>
                                <span>{!! trans('panel.sidemenu.product_master') !!}</span>
                                <div class="d-none mobile_hide">{!! trans('panel.sidemenu.product_master') !!}</div>
                            </a>
                        </li>
                        @endif
                        <li class="fk-menu-section"><span>Team</span></li>
                        @if(auth()->user()->can('target_users_access'))
                        <li
                            class="nav-link {{ request()->is('sales_users*') || request()->is('sales_dealer*') || request()->is('branches_sales_target*') || request()->is('primary_scheme') || request()->is('primary_scheme_report') || request()->is('planned-sop*') || request()->is('planned-sop-forecast*') ? 'active' : '' }}">
                            <a class="collapsed hoveradd" data-toggle="collapse" href="#salesUserMenu"
                                aria-expanded="false">
                                <i class="material-icons icon">real_estate_agent</i>
                                <span> {!! trans('panel.sidemenu.sales_users') !!} Management </span>
                                <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.sales_users') !!} Management</div>
                            </a>
                            <div class="collapse" id="salesUserMenu" style="">
                                <ul class="navd">
                                    @if(auth()->user()->can('target_users_access_sales'))
                                    <li class="nav-link-btn {{ request()->is('sales_users*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('sales_users/target_users') }}">
                                            <i class="material-icons icon">emoji_events</i>
                                            <span> {!! trans('panel.sales_users.title') !!}</span>
                                            <div class="d-none mobile_hide"> {!! trans('panel.sales_users.title') !!}
                                            </div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('sales_target_dealers_access'))
                                    <li class="nav-link-btn {{ request()->is('sales_dealer*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('sales_dealer/target_dealers') }}">
                                            <i class="material-icons icon">school</i>
                                            <span> Dealer distributor target vs achievement</span>
                                            <div class="d-none mobile_hide">Dealer distributor target vs achievement
                                            </div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('branch_wise_sales_target_access'))
                                    <li
                                        class="nav-link-btn {{ request()->is('branches_sales_target') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('branches_sales_target') }}">
                                            <i class="material-icons icon">holiday_village</i>
                                            <span> Branch Wise Sales Target</span>
                                            <div class="d-none mobile_hide"> Branch Wise Sales Target</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('primary_scheme'))
                                    <li class="nav-item-btn {{ request()->is('primary_scheme') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('primary_scheme') }}">
                                            <i class="material-icons icon">holiday_village</i>
                                            <span> Primary Scheme</span>
                                            <div class="d-none mobile_hide"> Primary Scheme</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('primary_scheme_report'))
                                    <li
                                        class="nav-item-btn {{ request()->is('primary_scheme_report') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('primary_scheme_report') }}">
                                            <i class="material-icons icon">holiday_village</i>
                                            <span> Primary Scheme Report</span>
                                            <div class="d-none mobile_hide"> Primary Scheme Report</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('forecast_access'))
                                    <li
                                        class="nav-link-btn add_icon {{ request()->is('planned-sop-forecast*') || request()->is('planned-sop*') ? 'active' : '' }}">
                                        <a class="collapsed hoveradd" data-toggle="collapse" href="#forecastMenu"
                                            aria-expanded="false">
                                            <i class="material-icons icon">online_prediction</i>
                                            <span> Forecast </span>
                                            <div class="d-none mobile_hide"> Forecast</div>
                                        </a>
                                        <div class="collapse" id="forecastMenu" style="">
                                            <ul class="navd">
                                                <li class="nav-link-btn {{ request()->is('planned-sop*') ? 'active' : '' }}">
                                                    <a class="hoveradd2" href="{{ url('planned-sop') }}">
                                                        <i class="material-icons icon">warehouse</i>
                                                        <span>Planned S&OP</span>
                                                        <div class="d-none mobile_hide"> Planned S&OP</div>
                                                    </a>
                                                </li>
                                                @if(auth()->user()->can('planned_forecast'))
                                                <li
                                                    class="nav-link-btn {{ request()->is('planned-sop-forecast*') ? 'active' : '' }}">
                                                    <a class="hoveradd2" href="{{ url('planned-sop-forecast') }}">
                                                        <i class="material-icons icon">warehouse</i>
                                                        <span>S&OP Forecast</span>
                                                        <div class="d-none mobile_hide"> S&OP Forecast</div>
                                                    </a>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                        @endif
                        @if(auth()->user()->can('tasks_access'))
                        <li class="nav-link {{ request()->is('tasks*') ? 'active' : '' }}">
                            <a class="hoveradd no-after" href="{{ url('tasks') }}">
                                <i class="material-icons icon">check_circle</i>
                                <span>{!! trans('panel.sidemenu.task') !!} Managment</span>
                                <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.task') !!} Managment</div>
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->can('hr_access'))
                        <li
                            class="nav-link {{ request()->is('reports/attendancereport*') || request()->is('reports/attendancereportSummary*') || request()->is('holidays*') || request()->is('leaves*') || request()->is('appraisal*') || request()->is('sales_weightage*') ? 'active' : '' }}">
                            <a class="collapsed hoveradd" data-toggle="collapse" href="#hr" aria-expanded="false">
                                <i class="material-icons icon">family_restroom</i>
                                <!-- <span> {!! trans('panel.sidemenu.hr') !!}</span> -->
                                <span>HR Management</span>
                                <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.hr') !!}</div>
                            </a>
                            <div class="collapse" id="hr">
                                <ul class="navd">
                                    @if(auth()->user()->can('attendance_report'))
                                    <li
                                        class="nav-link-btn {{ request()->is('reports/attendancereport') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('reports/attendancereport') }}">
                                            <i class="material-icons icon">report</i>
                                            <span>Attendance Detail Report</span>
                                            <div class="d-none mobile_hide"> Attendance Detail Report</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('attendance_summary_report'))
                                    <li
                                        class="nav-link-btn {{ request()->is('reports/attendancereportSummary') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('reports/attendancereportSummary') }}">
                                            <i class="material-icons icon">summarize</i>
                                            <span>Attendance Summary Report</span>
                                            <div class="d-none mobile_hide">Attendance Summary Report</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('holiday_access'))
                                    <li class="nav-link-btn {{ request()->is('holidays*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('holidays') }}">
                                            <i class="material-icons icon">holiday_village</i>
                                            <span>Holidays</span>
                                            <div class="d-none mobile_hide"> Holidays</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('leave_access'))
                                    <li class="nav-link-btn {{ request()->is('leaves*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('leaves') }}">
                                            <i class="material-icons icon">energy_savings_leaf</i>
                                            <span>Leaves</span>
                                            <div class="d-none mobile_hide"> Leaves</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('resignation_access'))
                                    <li class="nav-link-btn {{ request()->is('resignations*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('resignations') }}">
                                            <i class="material-icons icon">outgoing_mail</i>
                                            <span>Resignation</span>
                                            <div class="d-none mobile_hide"> Resignation</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('appraisal_pms'))
                                    <li class="nav-link-btn {{ request()->is('appraisal*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('appraisal/index') }}">
                                            <i class="material-icons icon">verified_user</i>
                                            <span>Appraisal(PMS)</span>
                                            <div class="d-none mobile_hide"> Appraisal(PMS)</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('sales_weightage'))
                                    <li class="nav-link-btn {{ request()->is('sales_weightage*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('sales_weightage') }}">
                                            <i class="material-icons icon">checkroom</i>
                                            <span>{!! trans('panel.sales_weightage.title') !!}</span>
                                            <div class="d-none mobile_hide"> {!! trans('panel.sales_weightage.title')
                                                !!}</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('branch'))
                                    <li class="nav-link-btn {{ request()->is('branches') || request()->is('branches/*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('branches') }}">
                                            <i class="material-icons icon">meeting_room</i>
                                            <span>Branch</span>
                                            <div class="d-none mobile_hide"> Branch</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('division'))
                                    <li class="nav-link-btn {{ request()->is('division*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('division') }}">
                                            <i class="material-icons icon">safety_divider</i>
                                            <span>Zone</span>
                                            <div class="d-none mobile_hide"> Zone</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('designation'))
                                    <li class="nav-link-btn {{ request()->is('designation*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('designation') }}">
                                            <i class="material-icons icon">shopping_bag</i>
                                            <span>Designation</span>
                                            <div class="d-none mobile_hide"> Designation</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('departments'))
                                    <li class="nav-link-btn {{ request()->is('departments*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('departments') }}">
                                            <i class="material-icons icon">local_fire_department</i>
                                            <span>Departments</span>
                                            <div class="d-none mobile_hide"> Departments</div>
                                        </a>
                                    </li>
                                    @endif

                                </ul>

                            </div>

                        </li>
                        @if(auth()->user()->can('user_access'))
                        <li
                            class="nav-link {{ request()->is('users*') || request()->is('targets*') || request()->is('livelocation*') || request()->is('permissions*') || request()->is('tours*') || request()->is('usercity*') || request()->is('new-joinings*') ? 'active' : '' }}">
                            <a class="hoveradd" data-toggle="collapse" href="#userMenu" aria-expanded="false">
                                <i class="material-icons icon">badge</i>
                                <!-- <span> {!! trans('panel.sidemenu.users_master') !!}</span> -->
                                <span>User Management</span>

                                <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.users_master')
                                    !!}</div>
                            </a>
                            <div class="collapse" id="userMenu" style="">
                                <ul class="navd">
                                    <!--                 @if(auth()->user()->can('appraisal_pms'))
                                          <li class="nav-item {{ request()->is('appraisal/create') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('appraisal/index') }}">
                                              <i class="material-icons">verified_user</i>
                                              <p>Appraisal(PMS)</p>
                                            </a>
                                          </li>
                                          @endif
                                          @if(auth()->user()->can('sales_weightage'))
                                          <li class="nav-item {{ request()->is('sales_weightage') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ url('sales_weightage') }}">
                                              <i class="material-icons">check</i>
                                              <p>{!! trans('panel.sales_weightage.title') !!}</p>
                                            </a>
                                          </li>
                                          @endif -->
                                    <!-- @if(auth()->user()->can('new_joining_access'))
                                    <li class="nav-link-btn {{ request()->is('new-joinings*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('new-joinings') }}">
                                            <i class="material-icons icon">verified_user</i>
                                            <span>New Joining</span>
                                            <div class="d-none mobile_hide"> New Joining</div>
                                        </a>
                                    </li>
                                    @endif -->
                                    @if(auth()->user()->can('user_access'))
                                    <li class="nav-link-btn {{ request()->is('users*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('users') }}">
                                            <i class="material-icons icon">assignment_ind</i>
                                            <!-- <span>{!! trans('panel.sidemenu.users') !!}</span> -->
                                            <span>User Details</span>
                                            <div class="d-none mobile_hide"> {!!
                                                trans('panel.sidemenu.users') !!}</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('user_app_details_access'))
                                    <li class="nav-link-btn {{ request()->is('user_app_details*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('user_app_details') }}">
                                            <i class="material-icons icon">details</i>
                                            <span>User App details</span>
                                            <div class="d-none mobile_hide"> User App details</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('target_access'))
                                    <li class="nav-link-btn {{ request()->is('targets*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('targets') }}">
                                            <i class="material-icons icon">loupe</i>
                                            <span>User Target</span>
                                            <div class="d-none mobile_hide">User Target</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('user_location'))
                                    <li class="nav-link-btn {{ request()->is('livelocation*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('livelocation') }}">
                                            <i class="material-icons icon">share_location</i>
                                            <span>User Live Activity</span>
                                            <div class="d-none mobile_hide"> User Live Location</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('tours'))
                                    <li class="nav-link-btn {{ request()->is('tours*') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ url('tours') }}">
                                            <i class="material-icons icon">tour</i>
                                            <span>Tours</span>
                                            <div class="d-none mobile_hide">Tours</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('city_assigned'))
                                    <li class="nav-link-btn {{ request()->is('usercity*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('usercity') }}">
                                            <i class="material-icons icon">location_city</i>
                                            <span>City Assigned</span>
                                            <div class="d-none mobile_hide">City Assigned</div>
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                        @endif
                        @endif
                        <li class="fk-menu-section"><span>Finance</span></li>
                        @if(auth()->user()->can(['account_access']))
                        <li
                            class="nav-link {{ request()->is('expenses*') || request()->is('tax_invoice*') || request()->is('expenses_type*') || request()->is('estimate*') ? 'active' : '' }}">
                            <a class="collapsed hoveradd" data-toggle="collapse" href="#accountMenu"
                                aria-expanded="false">
                                <i class="material-icons icon">attribution</i>
                                <span> {!! trans('panel.sidemenu.account') !!} Management
                                </span>
                                <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.account') !!} Management</div>
                            </a>
                            <div class="collapse" id="accountMenu" style="">
                                <ul class="navd">
                                    @if(auth()->user()->can(['expenses_type']))
                                    <li class="nav-link-btn {{ request()->is('expenses_type*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('expenses_type') }}">
                                            <i class="material-icons icon">dashboard</i>
                                            <span>{!! trans('panel.sidemenu.expenses_type') !!}</span>
                                            <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.expenses_type')
                                                !!}</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('expense_access'))
                                    <li class="nav-link-btn add_icon {{ request()->is('expenses') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('expenses') }}">
                                            <i class="material-icons icon">outlet</i>
                                            <span>Expense</span>
                                            <div class="d-none mobile_hide"> Expense</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('customer_outstanting'))
                                    <li
                                        class="nav-link-btn {{ request()->is('reports/customer_outstanting*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('reports/customer_outstanting') }}">
                                            <i class="material-icons icon">nature_people</i>
                                            <span>Dealer Outstanding</span>
                                            <div class="d-none mobile_hide"> Dealer Outstanding</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('dealer_account_statement'))
                                    <li
                                        class="nav-link-btn {{ request()->is('dealer_account_statement*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('dealer_account_statement') }}">
                                            <i class="material-icons icon">request_page</i>
                                            <span>Dealer Account Statement</span>
                                            <div class="d-none mobile_hide"> Dealer Account Statement</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('estimate_access'))
                                    <li class="nav-link-btn {{ request()->is('estimate*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('estimate') }}">
                                            <i class="material-icons icon">request_quote</i>
                                            <span>Estimate</span>
                                            <div class="d-none mobile_hide"> Estimate</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('invoice_access'))
                                    <li class="nav-link-btn {{ request()->is('tax_invoice*') ? 'active' : '' }}">
                                        <a class="hoveradd2" href="{{ url('tax_invoice') }}">
                                            <i class="material-icons icon">receipt_long</i>
                                            <span>Invoice</span>
                                            <div class="d-none mobile_hide"> Invoice</div>
                                        </a>
                                    </li>
                                    @endif
                                    @if(auth()->user()->can('payments_access'))
                                    <li class="nav-link-btn add_icon">
                                        <a class="hoveradd" data-toggle="collapse" href="#paymentManu"
                                            aria-expanded="false">
                                            <i class="material-icons icon">paid</i>
                                            <span>Payments</span>
                                            <div class="d-none mobile_hide"> Payments</div>
                                        </a>
                                    <li class="nav-link-btn">
                                        <div class="collapse" id="paymentManu" style="">
                                            <ul class="navd">
                                                @if(auth()->user()->can('payments_create'))
                                                <li
                                                    class="nav-link-btn {{ request()->is('payments*') ? 'active' : '' }}">
                                                    <a class="hoveradd2" href="{{ url('payments/create') }}">
                                                        <i class="material-icons icon">currency_exchange</i>
                                                        <span>Payment Recieved</span>
                                                        <div class="d-none mobile_hide"> Payment Recieved</div>
                                                    </a>
                                                </li>
                                                @endif
                                                <li
                                                    class="nav-link-btn {{ request()->is('payments*') ? 'active' : '' }}">
                                                    <a class="hoveradd2" href="{{ url('payments') }}">
                                                        <i class="material-icons icon">currency_rupee</i>
                                                        <span>Payments</span>
                                                        <div class="d-none mobile_hide"> Payments</div>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                        </li>
                        @endif
                    </ul>
                </div>
                </li>
                @endif
                <li class="fk-menu-section"><span>Services &amp; Orders</span></li>
                @if(auth()->user()->can('services_access'))
                <li
                    class="nav-link {{ request()->is('services*') || request()->is('warranty_activation*') || request()->is('complaint-type*') || request()->is('complaints*') || request()->is('service-charge*') || request()->is('service_bills*') || request()->is('end_user*') ? 'active' : '' }}">
                    <a class="collapsed hoveradd" data-toggle="collapse" href="#serviceMenu" aria-expanded="false">
                        <i class="material-icons icon">design_services</i>
                        <span> {!! trans('panel.sidemenu.services') !!} Management
                        </span>
                        <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.services') !!} Management</div>
                    </a>
                    <div class="collapse" id="serviceMenu" style="">
                        <ul class="navd">
                            @if(auth()->user()->can('serial_number_transaction'))
                            <li
                                class="nav-link-btn {{ request()->is('services/serial_number_transaction*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('services/serial_number_transaction') }}">
                                    <i class="material-icons icon">receipt_long</i>
                                    <span>{!! trans('panel.sidemenu.serial_number_transaction') !!}</span>
                                    <div class="d-none mobile_hide"> {!!
                                        trans('panel.sidemenu.serial_number_transaction') !!}</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('serial_number_history'))
                            <li
                                class="nav-link-btn {{ request()->is('services/serial_number_history*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('services/serial_number_history') }}">
                                    <i class="material-icons icon">history</i>
                                    <span>{!! trans('panel.sidemenu.serial_number_history') !!}</span>
                                    <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.serial_number_history')
                                        !!}</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('complaint_type_access'))
                            <li class="nav-link-btn {{ request()->is('complaint-type*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('complaint-type') }}">
                                    <i class="material-icons icon">mark_email_read</i>
                                    <span>{!! trans('panel.sidemenu.complaint_type') !!}</span>
                                    <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.complaint_type') !!}
                                    </div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('complaint_access'))
                            <li class="nav-link-btn {{ request()->is('complaints*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('complaints') }}">
                                    <i class="material-icons icon">checklist</i>
                                    <span>{!! trans('panel.sidemenu.complaint') !!}</span>
                                    <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.complaint') !!}</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('service_bill_access'))
                            <li class="nav-link-btn {{ request()->is('service_bills*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('service_bills') }}">
                                    <i class="material-icons icon">account_balance_wallet</i>
                                    <span>Service Bill</span>
                                    <div class="d-none mobile_hide"> Service Bill</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('service_bill_type_access'))
                            <li
                                class="nav-link-btn {{ request()->is('service-bills-complaints-type*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('service-bills-complaints-type') }}">
                                    <i class="material-icons icon">account_balance_wallet</i>
                                    <span>Service Bills Complaints Type</span>
                                    <div class="d-none mobile_hide"> Service Bills Complaints Type</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('claim_generation_access'))
                            <li class="nav-link-btn {{ request()->is('claim-generation*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('claim-generation') }}">
                                    <i class="material-icons icon">account_balance_wallet</i>
                                    <span>Claim Generation</span>
                                    <div class="d-none mobile_hide">Claim Generation</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('services_product_access'))
                            <li class="nav-link-btn add_icon {{ request()->is('service-charge*') ? 'active' : '' }}">
                                <a class="collapsed hoveradd" data-toggle="collapse" href="#serviceProductMenu"
                                    aria-expanded="false">
                                    <i class="material-icons icon">home_repair_service</i>
                                    <span> Service Charge Products</span>
                                        <div class="d-none mobile_hide"> Service Charge Products</div>
                                </a>
                                <div class="collapse" id="serviceProductMenu" style="">
                                    <ul class="navd">
                                        @if(auth()->user()->can('services_product_division'))
                                        <li
                                            class="nav-link-btn {{ request()->is('service-charge/dividsions*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('service-charge/dividsions') }}">
                                                <i class="material-icons icon">receipt_long</i>
                                                <span>Zone</span>
                                                <div class="d-none mobile_hide"> Zone</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('services_product_category'))
                                        <li
                                            class="nav-link-btn {{ request()->is('service-charge/categories*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('service-charge/categories') }}">
                                                <i class="material-icons icon">category</i>
                                                <span>Categories</span>
                                                <div class="d-none mobile_hide"> Categories</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('services_product_products'))
                                        <li
                                            class="nav-link-btn {{ request()->is('service-charge/products*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('service-charge/products') }}">
                                                <i class="material-icons icon">storefront</i>
                                                <span>Products</span>
                                                <div class="d-none mobile_hide"> Products</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('services_product_chargetype'))
                                        <li
                                            class="nav-link-btn {{ request()->is('service-charge/chargetype*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('service-charge/chargetype') }}">
                                                <i class="material-icons icon">power</i>
                                                <span>Charge Type</span>
                                                <div class="d-none mobile_hide"> Charge Type</div>
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </li>
                            @endif
                            @if(auth()->user()->can('warranty_activation_access'))
                            <li class="nav-link-btn {{ request()->is('warranty_activation*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('warranty_activation') }}">
                                    <i class="material-icons icon">history</i>
                                    <span>{!! trans('panel.sidemenu.warranty_activation') !!}</span>
                                    <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.warranty_activation') !!}
                                    </div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('end_user_access'))
                            <li class="nav-link-btn {{ request()->is('end_user*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('end_user') }}">
                                    <i class="material-icons icon">group</i>
                                    <span>End Users</span>
                                    <div class="d-none mobile_hide"> End Users</div>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif
                <!--           @if(auth()->user()->can('order_access')) 
                        <li class="nav-item {{ request()->is('orders*') ? 'active' : '' }}">
                          <a class="nav-link" href="{{ url('orders') }}">
                            <i class="material-icons">shopping_bag</i>
                            <p>{!! trans('panel.sidemenu.orders') !!}</p>
                          </a>
                        </li>
                        @endif -->
                @if(auth()->user()->can('order_access'))
                <li
                    class="nav-link {{ request()->is('orders*') || request()->is('orderschemes*') || request()->is('sales') ? 'active' : '' }}">
                    <a class="collapsed hoveradd" data-toggle="collapse" href="#orderMenu" aria-expanded="false">
                        <i class="material-icons icon">star</i>
                        <!-- <span>{!! trans('panel.sidemenu.orders') !!}</span> -->
                        <span>Order Management</span>
                        <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.orders') !!}</div>
                    </a>
                    <div class="collapse" id="orderMenu" style="">
                        <ul class="navd">
                            @if(auth()->user()->can('order_access'))
                            <li class="nav-link-btn {{ request()->is('orders*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('orders') }}">
                                    <i class="material-icons icon">shopping_bag</i>
                                    <span>{!! trans('panel.sidemenu.orders') !!}</span>
                                    <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.orders') !!}</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('orderscheme'))
                            <li class="nav-link-btn {{ request()->is('orderschemes*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('orderschemes') }}">
                                    <i class="material-icons icon">flaky</i>
                                    <span>Order Schemes</span>
                                    <div class="d-none mobile_hide"> Order Schemes</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('sale_access'))
                            <li class="nav-link-btn {{ request()->is('sales') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('sales') }}">
                                    <i class="material-icons icon">shopping_cart</i>
                                    <span>{!! trans('panel.sidemenu.order_dispatch') !!}</span>
                                    <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.order_dispatch') !!}
                                    </div>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif
                <li class="fk-menu-section"><span>Growth</span></li>
                @if(auth()->user()->can('marketing_access'))
                <li class="nav-link {{ request()->is('marketings*') ? 'active' : '' }}">
                    <a class="collapsed hoveradd" data-toggle="collapse" href="#marketingMenu" aria-expanded="false">
                        <i class="material-icons icon">local_convenience_store</i>
                        <span>Marketing</span>
                        <div class="d-none mobile_hide"> Marketing</div>
                    </a>
                    <div class="collapse" id="marketingMenu" style="">
                        <ul class="navd">
                            @if(auth()->user()->can('marketing_master_access'))
                            <li class="nav-link-btn {{ request()->is('marketings*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('marketings') }}">
                                    <i class="material-icons icon">add_business</i>
                                    <span>Marketing Master</span>
                                    <div class="d-none mobile_hide"> Marketing Master</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('marketing_new_dealer_access'))
                            <li class="nav-link-btn {{ request()->is('marketings_new_dealer*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('marketings_new_dealer') }}">
                                    <i class="material-icons icon">flaky</i>
                                    <span>New Dealer/Distributor</span>
                                    <div class="d-none mobile_hide"> New Dealer/Distributor</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('msp_activity_access'))
                            <li class="nav-link-btn {{ request()->is('msp_activity*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('msp_activity') }}">
                                    <i class="material-icons icon">celebration</i>
                                    <span>MSP Activity</span>
                                    <div class="d-none mobile_hide"> MSP Activity</div>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif
                @if(auth()->user()->can('wallet_access'))
                <!-- <li class="nav-item ">
                        <a class="collapsed hoveradd" data-toggle="collapse" href="#walletMenu" aria-expanded="false">
                          <i class="material-icons">account_balance_wallet</i>
                          <p> {!! trans('panel.sidemenu.wallet_master') !!}
                        
                          </p>
                        </a>
                        <div class="collapse" id="walletMenu" style="">
                          <ul class="nav">
                            @if(auth()->user()->can('wallet_access'))
                            <li class="nav-item ">
                              <a class="nav-link" href="{{ url('wallets') }}">
                                <i class="material-icons">account_balance_wallet</i>
                                <p>{!! trans('panel.sidemenu.wallet') !!}</p>
                              </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('redeemedpoint_access'))
                            <li class="nav-item ">
                              <a class="nav-link" href="{{ url('redeemedPoint') }}">
                                <i class="material-icons">redeem</i>
                                <p>{!! trans('panel.sidemenu.redeemedPoint') !!}</p>
                              </a>
                            </li>
                            @endif
                          </ul>
                        </div>
                        </li> -->
                @endif
                @if(auth()->user()->can('scheme_access'))
                <li
                    class="nav-link {{ request()->is('schemes*') || request()->is('transaction_history*') || request()->is('gifts*') || request()->is('gift-categories*') || request()->is('gift-subcategories*') || request()->is('gift-model*') || request()->is('gift-brands*') || request()->is('redemptions*') || request()->is('damage_entries*') || request()->is('mobile_user_login*') || request()->is('customer-kyc*') ? 'active' : '' }}">
                    <a class="collapsed hoveradd" data-toggle="collapse" href="#schemesMenu" aria-expanded="false">
                        <i class="material-icons icon">card_membership</i>
                        <span> Loyalty Engine </span>
                        <div class="d-none mobile_hide"> Loyalty Engine </div>
                    </a>
                    <div class="collapse" id="schemesMenu">
                        <ul class="navd">
                            @if(auth()->user()->can('scheme_access_list'))
                            <li class="nav-link-btn {{ request()->is('schemes*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('schemes') }}">
                                    <i class="material-icons icon">create</i>
                                    <span>Loyalty {!! trans('panel.sidemenu.scheme_master') !!}</span>
                                    <div class="d-none mobile_hide"> Loyalty {!! trans('panel.sidemenu.scheme_master')
                                        !!}</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('transaction_history_access'))
                            <li class="nav-link-btn {{ request()->is('transaction_history*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('transaction_history') }}">
                                    <i class="material-icons icon">history</i>
                                    <span>Transaction Coupon History</span>
                                    <div class="d-none mobile_hide"> Transaction Coupon History</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('loyalty_mobile_app_users_access'))
                            <li class="nav-link-btn {{ request()->is('mobile_user_login') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('mobile_user_login') }}">
                                    <i class="material-icons icon">developer_mode</i>
                                    <span>Mobile App Users</span>
                                    <div class="d-none mobile_hide"> Mobile App Users</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('damage_entry_access'))
                            <li class="nav-link-btn {{ request()->is('damage_entries*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('damage_entries') }}">
                                    <i class="material-icons icon">insert_page_break</i>
                                    <span>Damage QR Entries</span>
                                    <div class="d-none mobile_hide"> Damage QR Entries</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('redemption_access'))
                            <li class="nav-link-btn {{ request()->is('redemptions*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('redemptions') }}">
                                    <i class="material-icons icon">mp</i>
                                    <span>Redemption</span>
                                    <div class="d-none mobile_hide"> Redemption</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('gift_access'))
                            <li class="nav-link-btn {{ request()->is('gifts*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('gifts') }}">
                                    <i class="material-icons icon">model_training</i>
                                    <span>Gift Catalogue</span>
                                    <div class="d-none mobile_hide"> Gift Catalogue</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('gift_category_access'))
                            <li class="nav-link-btn {{ request()->is('gift-categories*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('gift-categories') }}">
                                    <i class="material-icons icon">redeem</i>
                                    <span>Gift Categories</span>
                                    <div class="d-none mobile_hide"> Gift Categories</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('gift_subcategory_access'))
                            <li class="nav-link-btn {{ request()->is('gift-subcategories*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('gift-subcategories') }}">
                                    <i class="material-icons icon">redeem</i>
                                    <span>Gift Sub Categories</span>
                                    <div class="d-none mobile_hide"> Gift Sub Categories</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('gift_model_access'))
                            <li class="nav-link-btn {{ request()->is('gift-model*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('gift-model') }}">
                                    <i class="material-icons icon">redeem</i>
                                    <span>Gift Model</span>
                                    <div class="d-none mobile_hide"> Gift Model</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('gift_brand_access'))
                            <li class="nav-link-btn {{ request()->is('gift-brands*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('gift-brands') }}">
                                    <i class="material-icons icon">redeem</i>
                                    <span>Gift Brand</span>
                                    <div class="d-none mobile_hide"> Gift Brand</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('customer_kyc_access'))
                            <li class="nav-link-btn {{ request()->is('customer-kyc*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('customer-kyc') }}">
                                    <i class="material-icons icon">verified</i>
                                    <span>Customer KYC</span>
                                    <div class="d-none mobile_hide"> Customer KYC</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('new_invoice_access'))
                            <li class="nav-link-btn {{ request()->is('new-invoices*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ route('new-invoices.index') }}">
                                    <i class="material-icons icon">receipt_long</i>
                                    <span>New Invoices</span>
                                    <div class="d-none mobile_hide"> New Invoices</div>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif
                <li class="fk-menu-section"><span>Operations</span></li>
                @if(auth()->user()->can('status_access'))
                <li
                    class="nav-link {{request()->is('loyalty-app-setting*') || request()->is('roles*') || request()->is('power_bi_setting*') ? 'active' : '' }}">
                    <a class="collapsed hoveradd" data-toggle="collapse" href="#settingMenu" aria-expanded="false">
                        <i class="material-icons icon">settings</i>
                        <span> {!! trans('panel.sidemenu.setting_master') !!}
                        </span>
                        <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.setting_master') !!}</div>
                    </a>
                    <div class="collapse" id="settingMenu" style="">
                        <ul class="navd">
                            @if(auth()->user()->can('setting_access'))
                            <!-- <li class="nav-item ">
                                 <a class="nav-link" href="{{ url('settings') }}">
                                   <i class="material-icons">settings</i>
                                   <p>{!! trans('panel.sidemenu.setting') !!}</p>
                                 </a>
                                 </li> -->
                            @endif
                            @if(auth()->user()->can('power_bi_setting_access'))
                            <li class="nav-link-btn {{request()->is('power_bi_setting*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('power_bi_setting') }}">
                                    <i class="material-icons icon">analytics</i>
                                    <span>Power BI {!! trans('panel.sidemenu.setting') !!}</span>
                                    <div class="d-none mobile_hide">Power BI {!! trans('panel.sidemenu.setting') !!}
                                    </div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('invoice_setting_access'))
                            <li class="nav-link-btn {{request()->is('invoice_setting*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('invoice_setting') }}">
                                    <i class="material-icons icon">settings</i>
                                    <span>Invoice {!! trans('panel.sidemenu.setting') !!}</span>
                                    <div class="d-none mobile_hide">Invoice {!! trans('panel.sidemenu.setting') !!}
                                    </div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('loyalty_app_setting_access'))
                            <li class="nav-link-btn {{request()->is('loyalty-app-setting*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('loyalty-app-setting') }}">
                                    <i class="material-icons icon">manage_accounts</i>
                                    <span>Loyalty App {!! trans('panel.sidemenu.setting') !!}</span>
                                    <div class="d-none mobile_hide">Loyalty App {!! trans('panel.sidemenu.setting') !!}
                                    </div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('loyalty_app_setting_access'))
                            <li class="nav-link-btn {{request()->is('field-konnect-app-setting*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('field-konnect-app-setting') }}">
                                    <i class="material-icons icon">admin_panel_settings</i>
                                    <span>FieldKonnect App {!! trans('panel.sidemenu.setting') !!}</span>
                                    <div class="d-none mobile_hide"> FieldKonnect App {!!
                                        trans('panel.sidemenu.setting') !!}</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('dealer_portal_setting_access'))
                            <li class="nav-link-btn {{request()->is('delar-portal-setting*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('delar-portal-setting') }}">
                                    <i class="material-icons  icon">settings_applications</i>
                                    <span>Dealer portal {!! trans('panel.sidemenu.setting') !!}</span>
                                    <div class="d-none mobile_hide">Dealer portal {!! trans('panel.sidemenu.setting')
                                        !!}</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('status_access'))
                            <li class="nav-link-btn {{ request()->is('status*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('status') }}">
                                    <i class="material-icons icon">format_paint</i>
                                    <span>{!! trans('panel.sidemenu.status') !!}</span>
                                    <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.status') !!}</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('role_access'))
                            <li class="nav-link-btn {{ request()->is('roles*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('roles') }}">
                                    <i class="material-icons icon">vertical_shades_closed</i>
                                    <span>{!! trans('panel.sidemenu.roles') !!}</span>
                                    <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.roles') !!}</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('permission_access'))
                            <!-- <li class="nav-link-btn {{ request()->is('permissions*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('permissions') }}">
                                    <i class="material-icons icon">workspace_premium</i>
                                    <span>{!! trans('panel.sidemenu.permissions') !!}</span>
                                    <div class="d-none mobile_hide">{!! trans('panel.sidemenu.permissions') !!}</div>
                                </a>
                            </li> -->
                            @endif

                        </ul>
                    </div>
                </li>
                @endif
                @if(auth()->user()->can('supports_access'))
                <li class="nav-link">
                    <a class="collapsed hoveradd" data-toggle="collapse" href="#supportMenu" aria-expanded="false">
                        <i class="material-icons icon">houseboat</i>
                        <span> {!! trans('panel.sidemenu.support_master') !!}
                        </span>
                        <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.support_master') !!}</div>
                    </a>
                    <div class="collapse" id="supportMenu" style="">
                        <ul class="navd">
                            @if(auth()->user()->can('supports_access'))
                            <li class="nav-link-btn ">
                                <a class="hoveradd2" href="{{ url('supports') }}">
                                    <i class="material-icons icon">kitesurfing</i>
                                    <span>{!! trans('panel.sidemenu.support') !!}</span>
                                    <div class="d-none mobile_hide">{!! trans('panel.sidemenu.support') !!}</div>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif
                @if(auth()->user()->can('visitreport_access') || auth()->user()->can('beat_access'))
                <li class="nav-link {{ request()->is('beats*') || request()->is('beatdetail*') ? 'active' : '' }}">
                    <a class="collapsed hoveradd" data-toggle="collapse" href="#beatMenu" aria-expanded="false">
                        <i class="material-icons icon">houseboat</i>
                        <span> Beats Management
                        </span>
                        <div class="d-none mobile_hide"> Beats Management</div>
                    </a>
                    <div class="collapse" id="beatMenu" style="">
                        <ul class="navd">
                            @if(auth()->user()->can('beat_access'))
                            <li class="nav-link-btn {{ request()->is('beats*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('beats') }}">
                                    <i class="material-icons icon">kitesurfing</i>
                                    <span>{!! trans('panel.sidemenu.beats') !!}</span>
                                    <div class="d-none mobile_hide">{!! trans('panel.sidemenu.beats') !!}</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('beatdetail_access'))
                            <li class="nav-link-btn {{ request()->is('beatdetail*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('beatdetail') }}">
                                    <i class="material-icons icon">waves</i>
                                    <span>{!! trans('panel.sidemenu.beatdetail') !!}</span>
                                    <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.beatdetail') !!}</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('checkin_access'))
                            <li class="nav-link-btn {{ request()->is('checkin*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('checkin') }}">
                                    <i class="material-icons icon">assignment_turned_in</i>
                                    <span>{!! trans('panel.sidemenu.checkin') !!}</span>
                                    <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.checkin') !!}</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('visitreport_access'))
                            <li class="nav-link-btn {{ request()->is('visitreports*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('visitreports') }}">
                                    <i class="material-icons icon">summarize</i>
                                    <span>{!! trans('panel.sidemenu.visitreport') !!}</span>
                                    <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.visitreport') !!}</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('visittype_access'))
                            <li class="nav-link-btn {{ request()->is('visittypes*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('visittypes') }}">
                                    <i class="material-icons icon">border_color</i>
                                    <span>{!! trans('panel.sidemenu.visittype') !!}</span>
                                    <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.visittype') !!}</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('visitreport_access'))
                            <li class="nav-link-btn {{ request()->is('retailers*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('mastervisitreport') }}">
                                    <i class="material-icons icon">store</i>
                                    <span>Master VisitReport</span>
                                    <div class="d-none mobile_hide"> Master VisitReport</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('adherence_report'))
                            <li class="nav-link-btn {{ request()->is('reports/beatadherence*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('reports/beatadherence') }}">
                                    <i class="material-icons icon">vrpano</i>
                                    <span>Beat Adherence </span>
                                    <div class="d-none mobile_hide"> Beat Adherence </div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('summary_report'))
                            <li class="nav-link-btn {{ request()->is('reports/adherencesummary*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('reports/adherencesummary') }}">
                                    <i class="material-icons icon">summarize</i>
                                    <span>Adherence Summary</span>
                                    <div class="d-none mobile_hide"> Adherence Summary</div>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif
                <li class="fk-menu-section"><span>Reports</span></li>
                @if(auth()->user()->can('reports'))
                <li class="nav-link {{ request()->is('reports*') ? 'active' : '' }}">
                    <a class="collapsed hoveradd" data-toggle="collapse" href="#tasksMenu" aria-expanded="false">
                        <i class="material-icons icon">airplay</i>
                        <span> Reports
                        </span>
                        <div class="d-none mobile_hide"> Reports</div>
                    </a>
                    <div class="collapse" id="tasksMenu" style="">
                        <ul class="navd">
                            <!--                 @if(auth()->user()->can('attendance_report'))
                                 <li class="nav-item {{ request()->is('reports/attendancereport*') ? 'active' : '' }}">
                                   <a class="nav-link" href="{{ url('reports/attendancereport') }}">
                                     <i class="material-icons">check_circle</i>
                                     <p>Attendance Report</p>
                                   </a>
                                 </li>
                                 @endif -->
                            @if(auth()->user()->can('reports_sale'))
                            <li
                                class="nav-link-btn add_icon {{ request()->is('reports/reports_sale*') || request()->is('reports/fos_rating*') || request()->is('reports/primary_sales*') || request()->is('reports/secondary_sales*') || request()->is('reports/product_analysis_qty*') || request()->is('reports/product_analysis_branch*') || request()->is('reports/product_analysis_value*') || request()->is('reports/group_wise_analysis*') || request()->is('reports/asm_rating*') || request()->is('reports/ch_rating*') ? 'active' : '' }}">
                                <a class="hoveradd" data-toggle="collapse" href="#salesReportsMenu"
                                    aria-expanded="false">
                                    <i class="material-icons icon">point_of_sale</i>
                                    <span>User</span>
                                    <div class="d-none mobile_hide"> Sales</div>
                                </a>
                            <li class="nav-link-btn ">
                                <div class="collapse" id="salesReportsMenu" style="">
                                    <ul class="navd">

                                        @if(auth()->user()->can('attendance_report'))
                                        <li
                                            class="nav-link-btn {{ request()->is('reports/attendancereport') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('reports/attendancereport') }}">
                                                <i class="material-icons icon">report</i>
                                                <span>Attendance Detail</span>
                                                <div class="d-none mobile_hide"> Attendance Detail Report</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('attendance_summary_report'))
                                        <li
                                            class="nav-link-btn {{ request()->is('reports/attendancereportSummary') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('reports/attendancereportSummary') }}">
                                                <i class="material-icons icon">summarize</i>
                                                <span>Attendance Summary</span>
                                                <div class="d-none mobile_hide">Attendance Summary Report</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('tours'))
                                        <li class="nav-link-btn {{ request()->is('tours*') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ url('tours') }}">
                                                <i class="material-icons icon">tour</i>
                                                <span>Tours</span>
                                                <div class="d-none mobile_hide">Tours</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('order_access'))
                                        <li class="nav-link-btn {{ request()->is('orders*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('orders') }}">
                                                <i class="material-icons icon">shopping_bag</i>
                                                <span>{!! trans('panel.sidemenu.orders') !!}</span>
                                                <div class="d-none mobile_hide"> {!! trans('panel.sidemenu.orders') !!}</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('visit_report'))
                                        <li class="nav-link-btn {{ request()->is('reports/customervisit*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('reports/customervisit') }}">
                                                <i class="material-icons icon">dashboard_customize</i>
                                                <span>Check In & Check Out</span>
                                                <div class="d-none mobile_hide"> Customer Visit</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('ASR_report_Download'))
                                        <li class="nav-link-btn {{ request()->is('reports/adherencesummary*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('reports/adherencesummary?type=asr') }}">
                                                <i class="material-icons icon">summarize</i>
                                                <span>ASR Performance</span>
                                                <div class="d-none mobile_hide"> Adherence Summary</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('user_working_report'))
                                        <li
                                            class="nav-link-btn {{ request()->is('reports/reports_sale*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('reports/reports_sale') }}">
                                                <i class="material-icons icon">hub</i>
                                                <span>User working report</span>
                                                <div class="d-none mobile_hide"> User working report</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('fos_rating_report'))
                                        <li
                                            class="nav-link-btn {{ request()->is('reports/fos_rating*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('reports/fos_rating') }}">
                                                <i class="material-icons icon">trending_down</i>
                                                <span>FOS Rating Report</span>
                                                <div class="d-none mobile_hide"> FOS Rating Report</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('asm_rating_report'))
                                        <li
                                            class="nav-link-btn {{ request()->is('reports/asm_rating*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('reports/asm_rating') }}">
                                                <i class="material-icons icon">trending_down</i>
                                                <span>Rating Report</span>
                                                <div class="d-none mobile_hide">Rating Report</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('ch_rating_report'))
                                        <!-- <li class="nav-link-btn {{ request()->is('reports/ch_rating*') ? 'active' : '' }}">
                                 <a class="hoveradd2" href="{{ url('reports/ch_rating') }}">
                                    <i class="material-icons icon">trending_down</i>
                                    <span>CH/BM Rating Report</span>
                                    <div class="d-none mobile_hide"> CH/BM Rating Report</div>
                                 </a>
                              </li> -->
                                        @endif
                                        @if(auth()->user()->can('dashboard_primary_sales_access'))
                                        <li
                                            class="nav-link-btn {{ request()->is('reports/primary_sales*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('reports/primary_sales') }}">
                                                <i class="material-icons icon">stay_primary_landscape</i>
                                                <span>Primary Sales</span>
                                                <div class="d-none mobile_hide"> Primary Sales</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('dashboard_secondary_sales_access'))
                                        <li
                                            class="nav-link-btn {{ request()->is('reports/secondary_sales*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('reports/secondary_sales') }}">
                                                <i class="material-icons icon">cases</i>
                                                <span>Secondary Sales </span>
                                                <div class="d-none mobile_hide"> Secondary Sales </div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('product_analysis_branch_access'))
                                        <li
                                            class="nav-link-btn {{ request()->is('reports/product_analysis_branch*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('reports/product_analysis_branch') }}">
                                                <i class="material-icons icon">store</i>
                                                <span>Product Analysis Branch Wise</span>
                                                <div class="d-none mobile_hide"> Product Analysis Branch Wise</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('product_analysis_qty_access'))
                                        <li
                                            class="nav-link-btn {{ request()->is('reports/product_analysis_qty*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('reports/product_analysis_qty') }}">
                                                <i class="material-icons icon">insert_chart</i>
                                                <span>Product Analysis Qty</span>
                                                <div class="d-none mobile_hide"> Product Analysis Qty</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('product_analysis_value_access'))
                                        <li
                                            class="nav-link-btn {{ request()->is('reports/product_analysis_value*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('reports/product_analysis_value') }}">
                                                <i class="material-icons icon">add_chart</i>
                                                <span>Product Analysis Value</span>
                                                <div class="d-none mobile_hide"> Product Analysis Value</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('group_wise_analysis_access'))
                                        <li
                                            class="nav-link-btn {{ request()->is('reports/group_wise_analysis*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('reports/group_wise_analysis') }}">
                                                <i class="material-icons icon">query_stats</i>
                                                <span>Group Wise Analysis</span>
                                                <div class="d-none mobile_hide"> Group Wise Analysis</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('per_employee_costing_access'))
                                        <li
                                            class="nav-link-btn {{ request()->is('reports/per_employee_costing*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('reports/per_employee_costing') }}">
                                                <i class="material-icons icon">temple_buddhist</i>
                                                <span>Per Employee Costing</span>
                                                <div class="d-none mobile_hide"> Per Employee Costing</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('top_dealer_access'))
                                        <li
                                            class="nav-link-btn {{ request()->is('reports/top_dealer*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('reports/top_dealer') }}">
                                                <i class="material-icons icon">lightbulb_circle</i>
                                                <span>Top Dealer</span>
                                                <div class="d-none mobile_hide"> Top Dealer</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('dealer_growth_access'))
                                        <li
                                            class="nav-link-btn {{ request()->is('reports/dealer_growth*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('reports/dealer_growth') }}">
                                                <i class="material-icons icon">diversity_2</i>
                                                <span>Dealer Growth</span>
                                                <div class="d-none mobile_hide"> Dealer Growth</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('new_dealer_sale_access'))
                                        <li
                                            class="nav-link-btn {{ request()->is('reports/new_dealer_sale*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('reports/new_dealer_sale') }}">
                                                <i class="material-icons icon">expand</i>
                                                <span>New Dealer Sale</span>
                                                <div class="d-none mobile_hide"> New Dealer Sale</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if(auth()->user()->can('user_incentive_access'))
                                        <li
                                            class="nav-link-btn {{ request()->is('reports/user_incentive*') ? 'active' : '' }}">
                                            <a class="hoveradd2" href="{{ url('reports/user_incentive') }}">
                                                <i class="material-icons icon">biotech</i>
                                                <span>User Incentive</span>
                                                <div class="d-none mobile_hide"> User Incentive</div>
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </li>
                </li>
                @endif


                @if(auth()->user()->can('fielda_ctivity_report'))
                <li class="nav-link-btn  " style="display:none;">
                    <a class="hoveradd2" href="{{ url('reports/fieldactivity') }}">
                        <i class="material-icons icon">store</i>
                        <span>Field Activity Report</span>
                        <div class="d-none mobile_hide"> Field Activity Report</div>
                    </a>
                </li>
                @endif
                @if(auth()->user()->can('tour_programme_report'))
                <li class="nav-link-btn " style="display:none;">
                    <a class="hoveradd2" href="{{ url('reports/tourprogramme') }}">
                        <i class="material-icons icon">store</i>
                        <span>Tour Programme Report</span>
                        <div class="d-none mobile_hide"> Tour Programme Report</div>
                    </a>
                </li>
                @endif
                @if(auth()->user()->can('monthly_movement_report'))
                <li class="nav-link-btn " style="display:none;">
                    <a class="hoveradd2" href="{{ url('reports/monthlymovement') }}">
                        <i class="material-icons icon">store</i>
                        <span>Monthly Movement Report</span>
                        <div class="d-none mobile_hide"> Monthly Movement Report</div>
                    </a>
                </li>
                @endif
                @if(auth()->user()->can('point_collections_report'))
                <li class="nav-link-btn " style="display:none;">
                    <a class="hoveradd2" href="{{ url('reports/pointcollections') }}">
                        <i class="material-icons icon">store</i>
                        <span>Point Collections Report</span>
                        <div class="d-none mobile_hide"> Point Collections Report</div>
                    </a>
                </li>
                @endif
                @if(auth()->user()->can('territory_coverage_report'))
                <li class="nav-link-btn" style="display:none;">
                    <a class="hoveradd2" href="{{ url('reports/territorycoverage') }}">
                        <i class="material-icons icon">store</i>
                        <span>Territory Coverage Report</span>
                        <div class="d-none mobile_hide"> Territory Coverage Report</div>
                    </a>
                </li>
                @endif
                @if(auth()->user()->can('performance_parameter_report'))
                <li class="nav-link-btn" style="display:none;">
                    <a class="hoveradd2" href="{{ url('reports/performanceparameter') }}">
                        <i class="material-icons icon">store</i>
                        <span>Performance Parameter</span>
                        <div class="d-none mobile_hide"> Performance Parameter</div>
                    </a>
                </li>
                @endif
                @if(auth()->user()->can('mechanics_points_report'))
                <li class="nav-link-btn " style="display:none;">
                    <a class="hoveradd2" href="{{ url('reports/asmwisemechanicspoints') }}">
                        <i class="material-icons icon">store</i>
                        <span>Mechanics Points Report</span>
                        <div class="d-none mobile_hide"> Mechanics Points Report</div>
                    </a>
                </li>
                @endif
                @if(auth()->user()->can('targetvs_sales_report'))
                <li class="nav-link-btn " style="display:none;">
                    <a class="hoveradd2" href="{{ url('reports/targetvssales') }}">
                        <i class="material-icons icon">store</i>
                        <span>Target Vs Sales Report</span>
                        <div class="d-none mobile_hide"> Target Vs Sales Report</div>
                    </a>
                </li>
                @endif
                @if(auth()->user()->can('survey_analysis_report'))
                <li class="nav-link-btn " style="display:none;">
                    <a class="hoveradd2" href="{{ url('reports/surveyanalysis') }}">
                        <i class="material-icons icon">store</i>
                        <span>Survey Analysis Report</span>
                        <div class="d-none mobile_hide"> Survey Analysis Report</div>
                    </a>
                </li>
                @endif
                @if(auth()->user()->can('customers_report_access'))
                <li class="nav-link-btn add_icon">
                    <a class="hoveradd" data-toggle="collapse" href="#customerReportsMenu" aria-expanded="false">
                        <i class="material-icons icon">support_agent</i>
                        <span>Customers</span>
                        <div class="d-none mobile_hide"> Customers</div>
                    </a>
                <li class="nav-link-btn">
                    <div class="collapse" id="customerReportsMenu" style="">
                        <ul class="navd">
                            <li class="nav-link-btn {{ request()->is('retailers*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ route('retailers.index') }}">
                                    <i class="material-icons icon">storefront</i>
                                    <span>Retailer</span>
                                    <div class="d-none mobile_hide">Retailer</div>
                                </a>
                            </li>
                            @if(auth()->user()->can('retailer_productivity_report'))
                            <li class="nav-link-btn {{ request()->is('reports/adherencesummary*') && request('type') === 'retailer' ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('reports/adherencesummary?type=retailer') }}">
                                    <i class="material-icons icon">summarize</i>
                                    <span>Retailer Productivity Report</span>
                                    <div class="d-none mobile_hide">Retailer Productivity Report</div>
                                </a>
                            </li>
                            <li class="nav-link-btn {{ request()->is('reports/adherencesummary*') && request('type') === 'dealer' ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('reports/adherencesummary?type=dealer') }}">
                                    <i class="material-icons icon">summarize</i>
                                    <span>Distributors Productivity Report</span>
                                    <div class="d-none mobile_hide">Distributors Productivity Report</div>
                                </a>
                            </li>
                            @endif

                            @if(auth()->user()->can('customers_report'))
                            <li class="nav-link-btn ">
                                <a class="hoveradd2" href="{{ url('reports/customersreport') }}">
                                    <i class="material-icons icon">contact_emergency</i>
                                    <span>Customer Master</span>
                                    <div class="d-none mobile_hide">Customer Master</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('calling_report'))
                            <li class="nav-link-btn ">
                                <a class="hoveradd2" href="{{ url('notes') }}">
                                    <i class="material-icons icon">dialpad</i>
                                    <span>Calling Report</span>
                                    <div class="d-none mobile_hide"> Calling Report</div>
                                </a>
                            </li>
                            @endif
                            <li class="nav-link-btn {{ request()->is('reports/marketIntelligence*') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('reports/marketIntelligence') }}">
                                    <i class="material-icons icon">nature_people</i>
                                    <span> Market Intelligence</span>
                                    <div class="d-none mobile_hide"> Market Intelligence</div>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                </li>
                @endif
                @if(auth()->user()->can('loyalty_report_access'))
                <li class="nav-link-btn add_icon ">
                    <a class="hoveradd" data-toggle="collapse" href="#loyaltyMenu" aria-expanded="false">
                        <i class="material-icons icon">loyalty</i>
                        <span>Loyalty</span>
                        <div class="d-none mobile_hide"> Loyalty</div>
                    </a>
                <li class="nav-link-btn">
                    <div class="collapse" id="loyaltyMenu" style="">
                        <ul class="navd">
                            @if(auth()->user()->can('loyalty_summary_report'))
                            <li
                                class="nav-link-btn {{ request()->is('reports/loyalty_summary_report') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('reports/loyalty_summary_report') }}">
                                    <i class="material-icons icon">card_membership</i>
                                    <span>Loyalty Summary Report</span>
                                    <div class="d-none mobile_hide"> Loyalty Summary Report</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('loyalty_dealer_wise_summary_report'))
                            <li
                                class="nav-link-btn {{ request()->is('reports/loyalty_dealer_wise_summary_report') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('reports/loyalty_dealer_wise_summary_report') }}">
                                    <i class="material-icons icon">sign_language</i>
                                    <span>Loyalty Dealer Wise Summary Report</span>
                                    <div class="d-none mobile_hide"> Loyalty Dealer Wise Summary Report</div>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->can('loyalty_retailer_wise_summary_report'))
                            <li
                                class="nav-link-btn {{ request()->is('reports/loyalty_retailer_wise_summary_report') ? 'active' : '' }}">
                                <a class="hoveradd2" href="{{ url('reports/loyalty_retailer_wise_summary_report') }}">
                                    <i class="material-icons icon">point_of_sale</i>
                                    <span>Retailer Wise Loyalty Summary Report</span>
                                    <div class="d-none mobile_hide"> Retailer Wise Loyalty Summary Report</div>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                </li>
                @endif
                </ul>
            </div>
            </li>
            @endif
            </ul>
    </div>
    <!--   <div class="bottom-content">
                  <li class="">
                    <a href="#">
                      <i class='bx bx-log-out'></i>
                      <span class="text nav-text">Logout</span>
                    </a>
                  </li>
                  
                  </div> -->
    </div>
    </nav>
    <div class="main-panel">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top ">
            <div class="container-fluid" style="background: transparent; !important">
                @auth
                @php
                $userRoleName = auth()->user()->roles->pluck('name')->first() ?? 'Super Administrator';
                $userInitials = collect(explode(' ', trim(auth()->user()->name)))->filter()->map(function ($part) {
                return strtoupper(substr($part, 0, 1));
                })->take(2)->implode('');
                @endphp
                <div class="fk-header-left">
                    <button class="fk-header-btn fk-sidebar-toggle" type="button" aria-label="Toggle sidebar">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                </div>
                <div class="fk-header-right">
                    <button class="fk-live-sync" type="button">Live Sync</button>
                    <button class="fk-bell-btn" type="button" aria-label="Notifications">
                        <span class="material-symbols-outlined">notifications</span>
                    </button>
                    <div class="dropdown fk-user-menu">
                        <a class="fk-user-trigger" href="javascript:;" id="navbarDropdownProfile" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <span class="fk-user-avatar">
                                @if (auth()->user()->getMedia('profile_image')->count() > 0 &&
                                Storage::disk('s3')->exists(auth()->user()->getMedia('profile_image')[0]->getPath()))
                                <img src="{{ auth()->user()->getMedia('profile_image')[0]->getFullUrl() }}"
                                    alt="{{ auth()->user()->name }}">
                                @else
                                {{ $userInitials ?: 'AD' }}
                                @endif
                            </span>
                            <span class="fk-user-copy">
                                <span class="fk-user-name">{{ auth()->user()->name }}</span>
                                <span class="fk-user-role">{{ $userRoleName }}</span>
                            </span>
                            <span class="material-symbols-outlined fk-chevron">expand_more</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownProfile">
                            <a class="dropdown-item" href="{{ url('logout') }}">
                                <span class="material-symbols-outlined">logout</span>
                                <span>Log Out</span>
                            </a>
                        </div>
                    </div>
                </div>
                @endauth
            </div>
        </nav>
        <!-- End Navbar -->
        <div class="content">
            <div class="container-fluid">
                {{ $slot }}
            </div>
        </div>
        <footer class="footer">
            <div class="baseurl" data-baseurl="{{ url('/')}}"></div>
            <div class="token" data-token="{{ csrf_token() }}"></div>
            <div class="container-fluid">
                <nav class="float-left">
                </nav>
                <div class="copyright float-right">
                </div>
            </div>
        </footer>
    </div>
    </div>
    <div class="modal fade bd-example-modal-lg" id="previewimageInModel" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content card">
                <div class="card-header">
                    <span class="pull-right">
                        <a href="javascript:void(0)" class="btn btn-just-icon btn-danger" data-dismiss="modal"><i
                                class="material-icons">clear</i></a>
                    </span>
                </div>
                <div class="modal-body"> <img class="modal-content" id="img01"> </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/jquery.validate.min.js"></script>
    <!-- Bootstrap -->
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <!-- overlayScrollbars -->
    <script src="{{ asset('assets/js/core/bootstrap-material-design.min.js') }}"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap-tagsinput.js') }}"></script>
    <!-- OPTIONAL SCRIPTS -->
    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/sweetalert2.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/additional-methods.min.js"></script>
    <!-- jquery-validation -->
    <script src="{{ asset('assets/js/plugins/jquery.bootstrap-wizard.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/js/bootstrap-select.min.js"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap-datetimepicker.min.js') }}"></script>
    <!-- OPTIONAL SCRIPTS -->
    <script src="{{ asset('assets/js/plugins/chartist.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap-notify.js') }}"></script>
    <script src="{{ asset('assets/js/material-dashboard.js?v=2.1.2') }}"></script>
    <script src="{{ asset('assets/demo/demo.js') }}"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css"
        integrity="sha512-hievggED+/IcfxhYRSr4Auo1jbiOczpqpLZwfTVL/6hFACdbI3WQ8S9NCX50gsM9QVE+zLk/8wb9TlgriFbX+Q=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"
        integrity="sha512-F636MAkMAhtTplahL9F6KmTfxTmYcAcjcCkyu0f0voT3N/6vzAuJ4Num55a0gEJ+hRLHhdz3vDvZpf6kqgEa5w=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        $(function() {
            //Initialize Select2 Elements
            $('.select2').select2()

            $('#toggle-one').bootstrapToggle();
            $('.datetimepicker').datetimepicker({
                format: 'YYYY-MM-DD HH:mm'
            });
            $(".datepicker").datepicker({
                createButton: false,
                displayClose: true,
                closeOnSelect: false,
                selectMultiple: true,
                dateFormat: 'yy-mm-dd',
                beforeShow: function(input) {
                    $(input).css({
                        "position": "relative",
                        "z-index": 999999
                    });
                },
                onClose: function() {
                    $('.ui-datepicker').css({
                        'z-index': 0
                    });
                }
            });


            //Initialize Select2 Elements
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            })
            $('.timepicker').datetimepicker({
                format: 'HH:mm'
            });
        })
        $('body').on('click', '.imageDisplayModel', function() {
            var imgPath = $(this).attr("src");
            var modal = document.getElementById('previewimageInModel');
            $('#previewimageInModel').modal('show');
            document.getElementById("img01").src = imgPath;
        });

        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("loader").style.display = "block";
            document.querySelector(".content").style.display = "none";
        });

        // Hide loader and show content when the page is fully loaded
        window.addEventListener("load", function() {
            document.getElementById("loader").style.display = "none";
            document.querySelector(".content").style.display = "block";
        });
    </script>
    <script>
        const body = document.querySelector('body'),
            sidebar = body.querySelector('.sidebar'),
            toggle = body.querySelector(".toggle"),
            headerToggle = body.querySelector(".fk-sidebar-toggle"),
            // searchBtn = body.querySelector(".search-box"),
            modeSwitch = body.querySelector(".toggle-switch"),
            modeText = body.querySelector(".mode-text");
        if (toggle && sidebar) {
            toggle.addEventListener("click", () => {
                sidebar.classList.toggle("close");
            });
        }
        if (headerToggle && sidebar) {
            headerToggle.addEventListener("click", () => {
                sidebar.classList.toggle("close");
            });
        }

        function cleanListingTitle(rawTitle) {
            return (rawTitle || '')
                .replace(/\(\s*\d+\s*\)/g, '')
                .replace(/\bAdd\b|\bEdit\b|\bCreate\b/gi, '')
                .replace(/\s+/g, ' ')
                .trim();
        }

        function getActiveSectionLabel() {
            const activeTrigger = document.querySelector('.sidebar li.nav-link.active > a > span');
            const activeSubmenu = document.querySelector('.sidebar ul.navd li.active');
            const parentLi = activeSubmenu ? activeSubmenu.closest('li.nav-link') : null;
            const parentTrigger = parentLi ? parentLi.querySelector('a > span') : null;
            const label = (parentTrigger && parentTrigger.textContent) || (activeTrigger && activeTrigger.textContent) || 'Dashboard';
            return label.replace(/\s+/g, ' ').trim();
        }

        function getListingCount(card) {
            const info = card.querySelector('.dataTables_info');
            const text = info ? info.textContent : '';
            const match = text.match(/\bof\s+([\d,]+)\b/i) || text.match(/Showing\s+[\d,]+\s+(?:to|–|-)\s+[\d,]+\s+of\s+([\d,]+)/i);
            return match ? match[1].replace(/,/g, '') : '';
        }

        function updateListingCount(card, chip) {
            const count = getListingCount(card);
            if (!count) return;
            chip.textContent = count + ' records';
            chip.classList.add('is-visible');
        }

        function buildListingTitleText(titleEl) {
            const clone = titleEl.cloneNode(true);
            clone.querySelectorAll('button, a, form, input, select, textarea, .btn, .header-frm-btn, .next-btn, .float-right, .dropdown, .bootstrap-select, .select2, .collapse').forEach(function(node) {
                node.remove();
            });
            return cleanListingTitle(clone.textContent);
        }

        function isCreateAction(node) {
            if (!node || !node.matches) return false;
            const href = (node.getAttribute('href') || '').toLowerCase();
            const title = (node.getAttribute('title') || node.textContent || '').toLowerCase();
            const icon = node.querySelector ? node.querySelector('.material-icons') : null;
            const iconText = icon ? icon.textContent.trim() : '';
            return node.matches('a') && (href.indexOf('/create') !== -1 || href.indexOf('create') !== -1 || iconText === 'add_circle' || title.indexOf('add') !== -1);
        }

        function isFilterForm(form) {
            if (!form || !form.matches || !form.matches('form')) return false;
            const action = (form.getAttribute('action') || '').toLowerCase();
            if (action.indexOf('upload') !== -1 || action.indexOf('template') !== -1) return false;
            if (action.indexOf('download') !== -1) return true;
            if (form.querySelector('select, textarea, input[type="text"], input[type="date"], input.datepicker')) return true;
            const fileInputs = form.querySelectorAll('input[type="file"]');
            return fileInputs.length === 0 && !!form.querySelector('input, select, textarea');
        }

        function createFilterButton(drawer) {
            const button = document.createElement('button');
            button.className = 'btn fk-filter-trigger';
            button.type = 'button';
            button.innerHTML = '<span class="material-icons">tune</span><span>Filters</span>';
            button.addEventListener('click', function() {
                drawer.classList.add('is-open');
                document.body.classList.add('fk-filter-open');
            });
            return button;
        }

        function getCreateLabel(link, titleText) {
            const explicitTitle = (link.getAttribute('title') || '').replace(/\s+/g, ' ').trim();
            const titleMatch = explicitTitle.match(/\badd\s+(.+)$/i);
            if (titleMatch && titleMatch[1]) return 'Add New ' + titleMatch[1].replace(/\s+list$/i, '').trim();
            const base = (titleText || 'Record').replace(/\s+list$/i, '').trim();
            return 'Add New ' + base;
        }

        function enhanceCreateAction(link, titleText) {
            link.classList.remove('btn-just-icon');
            link.classList.add('fk-create-action');
            link.innerHTML = '<span class="material-icons">add_circle</span><span>' + getCreateLabel(link, titleText) + '</span>';
        }

        function isTemplateAction(link) {
            const href = (link.getAttribute('href') || '').toLowerCase();
            const title = (link.getAttribute('title') || link.textContent || '').toLowerCase();
            const icon = link.querySelector ? link.querySelector('.material-icons') : null;
            const iconText = icon ? icon.textContent.trim() : '';
            return href.indexOf('template') !== -1 || title.indexOf('template') !== -1 || iconText === 'text_snippet' || iconText === 'description';
        }

        function isExportAction(link) {
            const href = (link.getAttribute('href') || '').toLowerCase();
            const title = (link.getAttribute('title') || link.textContent || '').toLowerCase();
            const icon = link.querySelector ? link.querySelector('.material-icons') : null;
            const iconText = icon ? icon.textContent.trim() : '';
            return href.indexOf('download') !== -1 || href.indexOf('export') !== -1 || title.indexOf('download') !== -1 || title.indexOf('export') !== -1 || iconText === 'cloud_download';
        }

        function isUploadForm(form) {
            const action = (form.getAttribute('action') || '').toLowerCase();
            return action.indexOf('upload') !== -1 || !!form.querySelector('input[type="file"]');
        }

        function decorateFilterDrawer(drawer) {
            drawer.querySelectorAll('.fk-filter-drawer-body .p-2, .fk-filter-drawer-body .col, .fk-filter-drawer-body [class*="col-"], .fk-filter-drawer-body .d-flex > div, .fk-filter-drawer-body .row > div, .fk-filter-drawer-body .search').forEach(function(field) {
                const control = field.querySelector('select, input, textarea');
                if (!control || field.dataset.label) return;
                const label = control.getAttribute('title') ||
                    control.getAttribute('placeholder') ||
                    control.getAttribute('name') ||
                    control.getAttribute('id') ||
                    '';
                if (label) field.dataset.label = label.replace(/[_-]+/g, ' ').replace(/\s+/g, ' ').trim();
            });
        }

        function hasFilterControls(node) {
            return !!(node && node.querySelector && node.querySelector('select, textarea, input[type="search"], input[type="text"], input[type="date"], input.datepicker, .select2, .selectpicker'));
        }

        function collectFilterSection(container) {
            const sections = [];
            if (!container || !container.querySelectorAll) return sections;
            container.querySelectorAll('.collapse, .filter-box').forEach(function(section) {
                if (hasFilterControls(section)) sections.push(section);
            });
            return sections;
        }

        function createFilterDrawer(titleText) {
            const drawer = document.createElement('aside');
            drawer.className = 'fk-filter-drawer';
            drawer.innerHTML = '<div class="fk-filter-drawer-head"><div class="fk-filter-drawer-icon"><span class="material-icons">tune</span></div><div><h3>Advanced Filters</h3><p>Applied live to the directory</p></div><button type="button" class="fk-filter-close" aria-label="Close filters"><span class="material-icons">close</span></button></div><div class="fk-filter-drawer-body"></div><div class="fk-filter-drawer-tools"></div><div class="fk-filter-drawer-foot"><button class="btn fk-filter-reset" type="button">Reset</button><button class="btn fk-filter-apply" type="button">Apply Filters</button></div>';
            drawer.querySelector('.fk-filter-close').addEventListener('click', function() {
                drawer.classList.remove('is-open');
                document.body.classList.remove('fk-filter-open');
            });
            return drawer;
        }

        function formatInlineFilterLabel(field) {
            const raw = field.getAttribute('title') ||
                field.getAttribute('placeholder') ||
                field.getAttribute('name') ||
                field.getAttribute('id') ||
                'Filter';
            return raw
                .replace(/\[\]$/g, '')
                .replace(/\.+$/g, '')
                .replace(/[_-]+/g, ' ')
                .replace(/\s+/g, ' ')
                .trim()
                .toUpperCase();
        }

        function moveInlineTableFilters(card, drawerBody) {
            const fields = Array.from(card.querySelectorAll('thead input.table-input, thead select.table-input, thead textarea.table-input'));
            if (!fields.length || !drawerBody) return false;

            const section = document.createElement('div');
            section.className = 'fk-inline-table-filters';

            fields.forEach(function(field) {
                const row = field.closest('tr');
                const select2Companion = field.nextElementSibling && field.nextElementSibling.classList.contains('select2') ? field.nextElementSibling : null;
                if (row) row.classList.add('fk-list-extra-row');

                field.removeAttribute('style');
                field.classList.add('fk-filter-control');

                const wrap = document.createElement('div');
                wrap.className = 'fk-filter-field';

                const label = document.createElement('label');
                label.textContent = formatInlineFilterLabel(field);

                wrap.appendChild(label);
                wrap.appendChild(field);
                if (select2Companion) wrap.appendChild(select2Companion);
                section.appendChild(wrap);
            });

            drawerBody.appendChild(section);
            return true;
        }

        function normalizeListingActions(titleEl, actions, card, titleText) {
            const candidates = [titleEl];
            const headerEl = titleEl.closest('.card-header');
            if (headerEl) {
                headerEl.querySelectorAll(':scope > span, :scope > .float-right, :scope > .header-frm-btn, :scope > .next-btn, :scope > .card, :scope > .row, :scope > form').forEach(function(node) {
                    candidates.push(node);
                });
            }
            titleEl.querySelectorAll('.float-right, .header-frm-btn, .next-btn').forEach(function(node) {
                candidates.push(node);
            });
            card.querySelectorAll(':scope > .card-body .date_sarch, :scope > .card-body .pream_entry, :scope > .card-body .well').forEach(function(node) {
                candidates.push(node);
            });
            if (!candidates.length) {
                Array.from(titleEl.children).forEach(function(node) {
                    if (node.querySelector && node.querySelector('button, a.btn, form, input, select, .btn')) {
                        candidates.push(node);
                    }
                });
            }
            if (!candidates.length) {
                Array.from(titleEl.children).forEach(function(node) {
                    if (node.matches && (node.matches('form') || node.matches('button.btn') || node.matches('a.btn'))) {
                        candidates.push(node);
                    }
                });
            }

            const drawer = createFilterDrawer(titleText);
            const drawerBody = drawer.querySelector('.fk-filter-drawer-body');
            const drawerTools = drawer.querySelector('.fk-filter-drawer-tools');
            const preservedAdds = [];
            const preservedAddSet = new Set();
            const appended = new Set();
            const toolKeys = new Set();

            function appendDrawerTool(kind, tool) {
                if (!tool || toolKeys.has(kind)) return;
                toolKeys.add(kind);
                drawerTools.appendChild(tool);
            }

            function makeToolButton(kind, source) {
                const button = document.createElement(source && source.matches && source.matches('a') ? 'a' : 'button');
                button.className = 'btn fk-tool-' + kind;
                if (button.tagName === 'A') button.href = source.getAttribute('href') || '#';
                else button.type = 'button';
                const icon = kind === 'template' ? 'description' : (kind === 'upload' ? 'cloud_upload' : 'cloud_download');
                const label = kind === 'template' ? 'Template' : (kind === 'upload' ? 'Import' : 'Export');
                button.innerHTML = '<span class="material-icons">' + icon + '</span><span>' + label + '</span>';
                if (source && kind === 'upload') {
                    button.fkUploadForm = source;
                    button.addEventListener('click', function() {
                        const file = source.querySelector('input[type="file"]');
                        if (file) {
                            source.dataset.fkAutoSubmitFile = '1';
                            file.click();
                        }
                    });
                } else if (source && source.matches && source.matches('form') && kind === 'export') {
                    button.addEventListener('click', function() {
                        source.submit();
                    });
                }
                return button;
            }

            candidates.forEach(function(container) {
                Array.from(container.querySelectorAll('a')).forEach(function(link) {
                    if (isCreateAction(link) && !preservedAddSet.has(link)) {
                        preservedAdds.push(link);
                        preservedAddSet.add(link);
                    } else if (isTemplateAction(link)) {
                        appendDrawerTool('template', makeToolButton('template', link));
                    } else if (isExportAction(link)) {
                        appendDrawerTool('export', makeToolButton('export', link));
                    }
                });
                Array.from(container.querySelectorAll('form')).forEach(function(form) {
                    if (isUploadForm(form)) {
                        appendDrawerTool('upload', makeToolButton('upload', form));
                    } else if (isFilterForm(form) && !appended.has(form)) {
                        drawerBody.appendChild(form);
                        appended.add(form);
                    }
                });
                collectFilterSection(container).forEach(function(section) {
                    if (!appended.has(section)) {
                        section.classList.remove('collapse');
                        section.classList.add('fk-filter-section');
                        drawerBody.appendChild(section);
                        appended.add(section);
                    }
                });
                if (container.matches && isFilterForm(container) && !appended.has(container)) {
                    drawerBody.appendChild(container);
                    appended.add(container);
                }
            });

            moveInlineTableFilters(card, drawerBody);

            if (drawerBody.children.length) {
                if (!drawerTools.children.length) drawerTools.remove();
                decorateFilterDrawer(drawer);
                document.body.appendChild(drawer);
                actions.appendChild(createFilterButton(drawer));
            }

            preservedAdds.forEach(function(link) {
                enhanceCreateAction(link, titleText);
                actions.appendChild(link);
            });
        }

        function singularizeListingLabel(label) {
            const clean = (label || 'Directory')
                .replace(/\s+list$/i, '')
                .replace(/\s+/g, ' ')
                .trim();
            if (/report$/i.test(clean)) return clean;
            if (/ies$/i.test(clean)) return clean.replace(/ies$/i, 'y');
            if (/sses$/i.test(clean)) return clean.replace(/es$/i, '');
            if (/s$/i.test(clean) && !/ss$/i.test(clean)) return clean.replace(/s$/i, '');
            return clean;
        }

        function getDirectoryTitle(titleText) {
            const base = singularizeListingLabel(titleText);
            return /directory$/i.test(base) || /report$/i.test(base) ? base : base + ' Directory';
        }

        function getTablePageInfo(card) {
            const table = card.querySelector('table');
            const info = {
                page: 1,
                pages: 1
            };
            if (window.jQuery && table && window.jQuery.fn && window.jQuery.fn.DataTable && window.jQuery.fn.DataTable.isDataTable(table)) {
                const pageInfo = window.jQuery(table).DataTable().page.info();
                info.page = (pageInfo.page || 0) + 1;
                info.pages = pageInfo.pages || 1;
                return info;
            }

            const lengthSelect = card.querySelector('.dataTables_length select');
            const total = parseInt(getListingCount(card) || '0', 10);
            const pageSize = lengthSelect ? parseInt(lengthSelect.value || '10', 10) : 10;
            if (total && pageSize) {
                info.pages = Math.max(1, Math.ceil(total / pageSize));
            }
            return info;
        }

        function updateTableMeta(card) {
            const metaSubline = card.querySelector('.fk-table-meta-subline');
            if (!metaSubline) return;
            const pageInfo = getTablePageInfo(card);
            metaSubline.textContent = 'Live directory · page ' + pageInfo.page + ' of ' + pageInfo.pages;
        }

        function normalizeDataTableMarkup(card) {
            card.querySelectorAll('table').forEach(function(table) {
                table.classList.add('fk-glass-table');
                table.classList.remove(
                    'table-striped',
                    'table-striped-',
                    'table-bordered',
                    'table-hover',
                    'table-checkable',
                    'table-sm',
                    'table-condensed',
                    'responsive',
                    'no-wrap',
                    'nowrap',
                    'w-100',
                    'display'
                );
                table.removeAttribute('border');
                table.removeAttribute('cellpadding');
                table.removeAttribute('cellspacing');
            });

            card.querySelectorAll('.dataTables_length, .dataTables_filter').forEach(function(control) {
                control.classList.add('fk-list-extra');
                const row = control.closest('.row');
                if (row) row.classList.add('fk-dt-controls-row');
            });

            card.querySelectorAll('thead, tbody, tr, th, td').forEach(function(node) {
                node.classList.remove(
                    'text-primary',
                    'text-rose',
                    'thead-light',
                    'table-info',
                    'table-active',
                    'bg-white',
                    'bg-light',
                    'description-column'
                );
            });

            card.querySelectorAll(':scope > .card-body > .well, :scope > .card-body > .pream_entry, :scope > .card-body > .date_sarch, :scope > .card-body > .sort_btn, :scope > .card-body > .filter-box, :scope > .card-body > .header-frm-btn, :scope > .card-body > .next-btn').forEach(function(node) {
                node.classList.add('fk-list-extra');
            });

            card.querySelectorAll(':scope > .card-body > form, :scope > .card-body > .collapse, :scope > .card-body > .row').forEach(function(node) {
                if (!node.querySelector('table')) {
                    node.classList.add('fk-list-extra');
                }
            });
        }

        function ensureTableMeta(card, titleText) {
            const bodyEl = card.querySelector(':scope > .card-body');
            if (!bodyEl || bodyEl.querySelector(':scope > .fk-table-meta')) return;

            const meta = document.createElement('div');
            meta.className = 'fk-table-meta';

            const icon = document.createElement('div');
            icon.className = 'fk-table-meta-icon';
            icon.innerHTML = '<span class="material-icons">storefront</span>';

            const copy = document.createElement('div');
            copy.className = 'fk-table-meta-copy';

            const title = document.createElement('h2');
            title.textContent = getDirectoryTitle(titleText);

            const subline = document.createElement('p');
            subline.className = 'fk-table-meta-subline';
            subline.textContent = 'Live directory · page 1 of 1';

            copy.appendChild(title);
            copy.appendChild(subline);
            meta.appendChild(icon);
            meta.appendChild(copy);

            const tableWrap = bodyEl.querySelector('.table-responsive') || bodyEl.querySelector('table');
            if (tableWrap) bodyEl.insertBefore(meta, tableWrap);
            else bodyEl.insertBefore(meta, bodyEl.firstChild);
            updateTableMeta(card);
        }

        function normalizeListingHeaders() {
            document.querySelectorAll('.content .card').forEach(function(card) {
                if (card.closest('.modal') || card.dataset.fkListingReady === '1' || !card.querySelector('table')) return;

                const header = Array.from(card.children).find(function(child) {
                    return child.classList &&
                        child.classList.contains('card-header') &&
                        (child.classList.contains('card-header-theme') || child.classList.contains('card-header-icon'));
                });
                if (!header) return;

                header.querySelectorAll('i.material-icons').forEach(function(icon) {
                    if (icon.textContent.trim() === 'perm_identity') {
                        const iconWrap = icon.closest('.card-icon');
                        if (iconWrap) iconWrap.remove();
                        else icon.remove();
                    }
                });

                const titleEl = header.querySelector('.card-title');
                if (!titleEl) return;

                const titleText = buildListingTitleText(titleEl);
                if (!titleText) return;

                const pageHead = document.createElement('div');
                pageHead.className = 'fk-list-page-head';

                const headingBlock = document.createElement('div');
                headingBlock.className = 'fk-list-heading-block';

                const breadcrumb = document.createElement('div');
                breadcrumb.className = 'fk-list-breadcrumb';
                const sectionLabel = getActiveSectionLabel();
                const currentLabel = titleText.replace(/\s+List$/i, '').trim();
                breadcrumb.innerHTML = '<span>' + sectionLabel.toUpperCase() + '</span><span>&rsaquo;</span><span class="fk-current">' + currentLabel.toUpperCase() + '</span>';

                const titleRow = document.createElement('div');
                titleRow.className = 'fk-list-title-row';

                const heading = document.createElement('h1');
                heading.className = 'fk-list-title';
                heading.textContent = titleText;

                const countChip = document.createElement('span');
                countChip.className = 'fk-list-count';
                const titleCount = (titleEl.textContent || '').match(/\(\s*(\d+)\s*\)/);
                if (titleCount) {
                    countChip.textContent = titleCount[1] + ' records';
                    countChip.classList.add('is-visible');
                }

                titleRow.appendChild(heading);
                titleRow.appendChild(countChip);
                headingBlock.appendChild(breadcrumb);
                headingBlock.appendChild(titleRow);

                const actions = document.createElement('div');
                actions.className = 'fk-list-actions';
                normalizeListingActions(titleEl, actions, card, titleText);

                pageHead.appendChild(headingBlock);
                pageHead.appendChild(actions);
                card.parentNode.insertBefore(pageHead, card);
                card.classList.add('fk-listing-card');
                card.dataset.fkListingReady = '1';
                header.classList.add('fk-card-header-processed');
                normalizeDataTableMarkup(card);
                ensureTableMeta(card, titleText);

                updateListingCount(card, countChip);
                updateTableMeta(card);
                setTimeout(function() {
                    updateListingCount(card, countChip);
                    updateTableMeta(card);
                }, 600);
                setTimeout(function() {
                    updateListingCount(card, countChip);
                    updateTableMeta(card);
                }, 1600);

                if (window.jQuery) {
                    window.jQuery(card).find('table').on('draw.dt', function() {
                        normalizeDataTableMarkup(card);
                        updateListingCount(card, countChip);
                        updateTableMeta(card);
                    });
                }
            });
        }

        normalizeListingHeaders();
        window.addEventListener('load', normalizeListingHeaders);
        document.querySelectorAll('.fk-filter-drawer').forEach(decorateFilterDrawer);

        function getFooterEntityLabel(wrapper) {
            const card = wrapper.closest('.fk-listing-card, .card');
            const section = wrapper.closest('.fk-manual-listing, .content');
            const manualTitle = section ? section.querySelector('.fk-list-page-head .fk-list-title') : null;
            const cardTitle = card ? card.querySelector('.card-title, .fk-table-meta h2') : null;
            const title = cleanListingTitle((manualTitle && manualTitle.textContent) || (cardTitle && cardTitle.textContent) || '')
                .replace(/([a-z])([A-Z])/g, '$1 $2')
                .replace(/([A-Z]+)([A-Z][a-z])/g, '$1 $2');
            const clean = title
                .replace(/\s*(list|listing|directory|report)$/i, '')
                .replace(/\s+/g, ' ')
                .trim();

            if (!clean || /report$/i.test(title)) return 'records';
            if (/y$/i.test(clean)) return clean.replace(/y$/i, 'ies').toLowerCase();
            if (/ies$/i.test(clean)) return clean.toLowerCase();
            if (/s$/i.test(clean)) return clean.toLowerCase();
            return clean.toLowerCase() + 's';
        }

        function formatDataTableFooter(wrapper) {
            if (!wrapper) return;
            const infoEl = wrapper.querySelector('.dataTables_info');
            const table = wrapper.querySelector('table');

            if (infoEl && window.jQuery && table && window.jQuery.fn && window.jQuery.fn.DataTable && window.jQuery.fn.DataTable.isDataTable(table)) {
                const info = window.jQuery(table).DataTable().page.info();
                const total = info.recordsDisplay || info.recordsTotal || 0;
                const start = total ? (info.start + 1) : 0;
                const end = info.end || 0;
                infoEl.textContent = 'Showing ' + start + '-' + end + ' of ' + total + ' ' + getFooterEntityLabel(wrapper);
            }

            wrapper.querySelectorAll('.paginate_button.previous, .page-item.previous .page-link').forEach(function(button) {
                button.innerHTML = '<span class="material-icons">chevron_left</span>';
                button.setAttribute('aria-label', 'Previous page');
            });

            wrapper.querySelectorAll('.paginate_button.next, .page-item.next .page-link').forEach(function(button) {
                button.innerHTML = '<span class="material-icons">chevron_right</span>';
                button.setAttribute('aria-label', 'Next page');
            });
        }

        function formatAllDataTableFooters() {
            document.querySelectorAll('body.fk-shell div.dataTables_wrapper').forEach(formatDataTableFooter);
        }

        formatAllDataTableFooters();
        window.addEventListener('load', formatAllDataTableFooters);

        if (window.jQuery) {
            window.jQuery(document).on('draw.dt xhr.dt init.dt', function(event, settings) {
                const wrapper = settings && settings.nTableWrapper ? settings.nTableWrapper : (event.target ? event.target.closest('div.dataTables_wrapper') : null);
                setTimeout(function() {
                    formatDataTableFooter(wrapper);
                }, 0);
            });
        }

        function closeFilterDrawers() {
            document.querySelectorAll('.fk-filter-drawer.is-open').forEach(function(drawer) {
                drawer.classList.remove('is-open');
            });
            document.body.classList.remove('fk-filter-open');
        }

        document.addEventListener('click', function(event) {
            const trigger = event.target.closest('.fk-filter-trigger');
            if (trigger) {
                const targetSelector = trigger.getAttribute('data-filter-target');
                const drawer = targetSelector ? document.querySelector(targetSelector) : trigger.fkFilterDrawer;
                if (drawer) {
                    drawer.classList.add('is-open');
                    document.body.classList.add('fk-filter-open');
                }
            }

            if (event.target.closest('.fk-filter-close')) {
                closeFilterDrawers();
            }

            const applyButton = event.target.closest('.fk-filter-apply');
            if (applyButton) {
                const drawer = applyButton.closest('.fk-filter-drawer');
                if (drawer && window.jQuery) {
                    window.jQuery(drawer).find('input, select, textarea').trigger('change');
                }
                closeFilterDrawers();
            }

            const resetButton = event.target.closest('.fk-filter-reset');
            if (resetButton) {
                const drawer = resetButton.closest('.fk-filter-drawer');
                if (drawer && window.jQuery) {
                    window.jQuery(drawer).find('input[type="text"], input[type="date"], textarea').val('').trigger('change');
                    window.jQuery(drawer).find('select').val('').trigger('change');
                    window.jQuery(drawer).find('.selectpicker').selectpicker('refresh');
                }
            }
        });

        (function() {
            if (window.__fileInputSubmitGuardInstalled) return;
            window.__fileInputSubmitGuardInstalled = true;

            let lastFileChangeAt = 0;
            let lastFileChangeForm = null;
            let lastExplicitSubmitAt = 0;
            let lastExplicitSubmitForm = null;
            const guardMs = 3000;

            function closest(target, selector) {
                return target && target.closest ? target.closest(selector) : null;
            }

            function isFileChangeSubmit(form) {
                if (!form || form !== lastFileChangeForm) return false;
                if (Date.now() - lastFileChangeAt > guardMs) return false;
                return !(form === lastExplicitSubmitForm && Date.now() - lastExplicitSubmitAt < guardMs);
            }

            function rememberExplicitSubmit(form) {
                lastExplicitSubmitAt = Date.now();
                lastExplicitSubmitForm = form;
            }

            document.addEventListener('click', function(event) {
                const submitter = closest(event.target, 'button[type="submit"], input[type="submit"], button:not([type])');
                if (submitter && submitter.form) {
                    rememberExplicitSubmit(submitter.form);
                }

                const fileControl = closest(event.target, 'form [data-dismiss="fileinput"]');
                if (fileControl && !(event.target.matches && event.target.matches('input[type="file"]'))) {
                    event.preventDefault();
                    if (fileControl.tagName === 'BUTTON') {
                        fileControl.type = 'button';
                    }
                }
            }, true);

            document.addEventListener('change', function(event) {
                if (event.target.matches && event.target.matches('form input[type="file"]')) {
                    lastFileChangeAt = Date.now();
                    lastFileChangeForm = event.target.form;
                    if (
                        lastFileChangeForm &&
                        lastFileChangeForm.dataset.fkAutoSubmitFile === '1' &&
                        event.target.files &&
                        event.target.files.length
                    ) {
                        delete lastFileChangeForm.dataset.fkAutoSubmitFile;
                        rememberExplicitSubmit(lastFileChangeForm);
                        lastFileChangeForm.submit();
                    }
                }
            }, true);

            document.addEventListener('submit', function(event) {
                if (isFileChangeSubmit(event.target)) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                }
            }, true);

            const nativeSubmit = HTMLFormElement.prototype.submit;
            HTMLFormElement.prototype.submit = function() {
                if (isFileChangeSubmit(this)) {
                    return false;
                }

                return nativeSubmit.call(this);
            };

            if (HTMLFormElement.prototype.requestSubmit) {
                const nativeRequestSubmit = HTMLFormElement.prototype.requestSubmit;
                HTMLFormElement.prototype.requestSubmit = function(submitter) {
                    if (submitter) {
                        rememberExplicitSubmit(this);
                    }
                    if (isFileChangeSubmit(this)) {
                        return false;
                    }

                    return nativeRequestSubmit.call(this, submitter);
                };
            }
        })();

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') closeFilterDrawers();
        });

        function normalizeSidebarPath(path) {
            return String(path || '')
                .replace(/^https?:\/\/[^/]+/i, '')
                .replace(/^\/+/, '')
                .replace(/\/+$/, '');
        }

        function sanitizeSidebarActiveState() {
            const sidebarEl = document.querySelector('.sidebar');
            if (!sidebarEl) return;

            const currentPath = normalizeSidebarPath(window.location.pathname);
            let bestLink = null;
            let bestScore = -1;

            sidebarEl.querySelectorAll('a[href]').forEach(function(link) {
                const href = link.getAttribute('href') || '';
                if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) return;

                const linkUrl = new URL(link.href, window.location.origin);
                if (linkUrl.origin !== window.location.origin) return;

                const linkPath = normalizeSidebarPath(linkUrl.pathname);
                if (!linkPath) return;

                const isMatch = currentPath === linkPath || currentPath.indexOf(linkPath + '/') === 0;
                if (!isMatch) return;

                const score = linkPath.length;
                if (score > bestScore) {
                    bestScore = score;
                    bestLink = link;
                }
            });

            if (!bestLink) return;

            sidebarEl.querySelectorAll('li.active').forEach(function(item) {
                item.classList.remove('active');
            });
            sidebarEl.querySelectorAll('.collapse.show').forEach(function(menu) {
                menu.classList.remove('show');
            });
            sidebarEl.querySelectorAll('[data-toggle="collapse"]').forEach(function(trigger) {
                trigger.setAttribute('aria-expanded', 'false');
                trigger.classList.add('collapsed');
            });

            const activeItem = bestLink.closest('li');
            if (activeItem) activeItem.classList.add('active');

            let menu = bestLink.closest('.collapse');
            while (menu) {
                menu.classList.add('show');
                const parentItem = menu.closest('li');
                if (parentItem) parentItem.classList.add('active');

                if (menu.id) {
                    const trigger = sidebarEl.querySelector('a[href="#' + menu.id + '"]');
                    if (trigger) {
                        trigger.setAttribute('aria-expanded', 'true');
                        trigger.classList.remove('collapsed');
                    }
                }

                menu = parentItem ? parentItem.parentElement.closest('.collapse') : null;
            }
        }

        sanitizeSidebarActiveState();

        document.querySelectorAll('.sidebar .collapse').forEach(function(menu) {
            if (menu.querySelector('li.active')) {
                menu.classList.add('show');
                const trigger = document.querySelector('.sidebar a[href="#' + menu.id + '"]');
                if (trigger) {
                    trigger.setAttribute('aria-expanded', 'true');
                    trigger.classList.remove('collapsed');
                }
            }
        });

        function scrollSidebarToActive() {
            const menuBar = document.querySelector('.sidebar .menu-bar');
            if (!menuBar) return;

            const activeItem = document.querySelector('.sidebar ul.navd li.active') ||
                document.querySelector('.sidebar li.nav-link.active') ||
                document.querySelector('.sidebar .active');

            if (!activeItem) return;

            const menuRect = menuBar.getBoundingClientRect();
            const itemRect = activeItem.getBoundingClientRect();
            const offset = itemRect.top - menuRect.top - (menuRect.height / 2) + (itemRect.height / 2);

            menuBar.scrollTo({
                top: Math.max(0, menuBar.scrollTop + offset),
                behavior: 'smooth'
            });
        }

        setTimeout(scrollSidebarToActive, 250);
        window.addEventListener('load', function() {
            setTimeout(scrollSidebarToActive, 250);
        });

        document.querySelectorAll('.sidebar [data-toggle="collapse"]').forEach(function(trigger) {
            trigger.addEventListener('click', function() {
                const target = document.querySelector(trigger.getAttribute('href'));
                if (!target) return;
                setTimeout(function() {
                    trigger.setAttribute('aria-expanded', target.classList.contains('show') ? 'true' : 'false');
                }, 250);
            });
        });

        const navbarToggle = document.querySelector('.navbar-toggler');
        if (navbarToggle && sidebar) {
            navbarToggle.addEventListener('click', function() {
                if (window.innerWidth <= 996) {
                    sidebar.classList.toggle('close');
                }
            });
        }
        // searchBtn.addEventListener("click", () => {
        //   sidebar.classList.remove("close");
        // })
        // modeSwitch.addEventListener("click", () => {
        //   body.classList.toggle("dark");

        //   if (body.classList.contains("dark")) {
        //     modeText.innerText = "Light mode";
        //   } else {
        //     modeText.innerText = "Dark mode";

        //   }
        // });
    </script>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-8D1DXGE6Z6"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        // gtag('config', 'G-8D1DXGE6Z6');
    </script>
</body>
@yield('script')

</html>
