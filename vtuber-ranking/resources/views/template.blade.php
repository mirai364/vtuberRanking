<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.5.1/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.27.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@0.1.1"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <style>
        .headerMenue {
            z-index: 30;
            background-color: #009688;
            height: 5rem;
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            left: 0;
            display: flex;
        }

        .top {
            height: 5rem;
            width: 20rem;
            font-size: larger;
            line-height: 5rem;
            display: flex;
        }

        .pageTitle {
            height: 5rem;
            line-height: 5rem;
            margin: 0px auto;
            width: 40rem;
            font-size: larger;
            color: white;
            text-align: center;
            vertical-align: middle;
        }

        .sideMenue {
            z-index: 20;
            background-color: #007668;
            width: 20rem;
            height: 100%;
            position: -webkit-sticky;
            position: fixed;
            top: 0;
            left: 0;
        }

        .contents {
            margin-left: 20rem;
        }

        .contentBlock {
            height: 6rem;
            vertical-align: middle;
            line-height: 6rem;
        }

        .contentBlock:hover {
            background-color: #70d6c8;
            transition: 0.2s;
        }

        .subContentBlock {
            height: 2.5rem;
            vertical-align: middle;
            line-height: 2.5rem;
        }

        .subContentBlock:hover {
            background-color: #70d6c8;
            transition: 0.2s;
        }

        summary {
            position: relative;
            display: block;
            /* 矢印を消す */
            padding: 10px 10px 10px 30px;
            /* アイコンの余白を開ける */
            cursor: pointer;
            /* カーソルをポインターに */
            font-weight: bold;
            transition: 0.2s;
        }

        summary:hover {
            background-color: #70d6c8;
        }

        summary::-webkit-details-marker {
            display: none;
            /* 矢印を消す */
        }

        /* 疑似要素でアイコンを表示 */
        summary:before,
        summary:after {
            content: "";
            margin: auto 0 auto 9rem;
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
        }

        summary:before {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            background-color: #1da1ff;
        }

        summary:after {
            left: 6px;
            width: 5px;
            height: 5px;
            border: 4px solid transparent;
            border-left: 5px solid #fff;
            box-sizing: border-box;
            transition: .1s;
            transform: rotate(90deg);
            /* アイコンを回転 */
            left: 4px;
            /* 位置を調整 */
            top: 5px;
            /* 位置を調整 */
        }

        /* オープン時のスタイル */
        details[open] summary {
            background-color: #70d6c8;
        }

        /* アニメーション */
        details[open] .details-content {
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(-10px);
            }

            100% {
                opacity: 1;
                transform: none;
            }
        }

        .link {
            display: block;
        }

        .link:hover {
            text-decoration: none;
        }

        .sideMenueText {
            margin: 3rem;
            color: white;
            font-size: larger;
        }

        .sideMenueSubText {
            margin: 1rem 0rem 1rem 5rem;
            color: white;
            font-size: small
        }

.select {
    background-color: #70d6c8;
}

    </style>
    <title>VAnalysis</title>
</head>

<body>
    <!-- ヘッダー -->
    <div class="headerMenue">
        <div class="top">
            <a href="/" style="color: white;display: block;width: 20rem;text-decoration: none;">
            <div style="margin-left: 3rem;"><img src="{{ asset('logo.png') }}"style="height: 4rem;"> VAnalysis</div>
        </a></div>
        <div class="pageTitle">@yield('title')</div>
    </div>
    <!-- サイドメニュー -->
    <div class="sideMenue">
        <div style="margin: 10rem 3rem 0 3rem;"></div>
        <div class="contentBlock"><a href="/" class="link @yield('streamRanking')"><span class="sideMenueText">放送中一覧</span></a></div>
        <div class="contentBlock"><a href="/channel" class="link @yield('channel')"><span
                    class="sideMenueText">チャンネル一覧</span></a></div>
        <div class="contentBlock"><a href="/video/hourly-stream-ranking" class="link @yield('hourlyStreamRanking')"><span
                    class="sideMenueText" style="margin: 3rem 3rem 1rem 3rem;">時間別ランキング</span></a></div>
        <details @yield('hourlyStreamRankingSubMenu')>
            <summary style="text-align: center;"></summary>
            <div class="details-content">
                <?php
                $now = new DateTime();
                for($i=1;$i<=7;$i++):
            ?>
                <?php $nowDate = (clone $now)->modify('- ' . $i . 'Days')->format('Y-m-d'); ?>
                <div class="subContentBlock"><a href="/video/hourly-stream-ranking/{{ $nowDate }}"
                        class="link"><span class="sideMenueSubText">・{{ $nowDate }}</span></a></div>
                <?php endfor; ?>
            </div>
        </details>

        <div class="contentBlock"><a href="/video/daily-stream-ranking" class="link @yield('dailyStreamRanking')"><span
                    class="sideMenueText" style="margin: 3rem 3rem 1rem 3rem;">日別ランキング</span></a></div>
        <details @yield('dailyStreamRankingSubMenu')>
            <summary style="text-align: center;"></summary>
            <div class="details-content">
                <?php
                $now = new DateTime();
                for($i=1;$i<=7;$i++):
            ?>
                <?php $nowDate = (clone $now)->modify('- ' . $i . 'Days')->format('Y-m-d'); ?>
                <div class="subContentBlock"><a href="/video/daily-stream-ranking/{{ $nowDate }}"
                        class="link"><span class="sideMenueSubText">・{{ $nowDate }}</span></a></div>
                <?php endfor; ?>
            </div>
        </details>
    </div>

    <div class="contents">
        <div class="container ops-main">
            <div class="row">
                @yield('content')
            </div>
        </div>
    </div>
</body>
