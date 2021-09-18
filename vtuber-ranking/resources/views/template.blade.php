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
            font-size: larger;
            line-height: 5rem;
            padding-left: 3rem;
            display: flex;
        }

        .pageTitle {
            margin: 15px auto;
            width: 20rem;
            font-size: larger;
            color: white;
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

    </style>
    <title>VtuberRanking</title>
</head>

<body>
    <!-- ヘッダー -->
    <div class="headerMenue">
        <div class="top"><a href="/" style="color: white;">VtuberRanking</a></div>
        <div class="pageTitle">@yield('title')</div>
    </div>
    <!-- サイドメニュー -->
    <div class="sideMenue">
        <div style="margin: 10rem 3rem 0 3rem;"><a href="/" style="color: white;font-size: larger;">放送中一覧</a></div>
        <div style="margin: 3rem;"><a href="/channel" style="color: white;font-size: larger;">チャンネル一覧</a></div>
        <div style="margin: 3rem;"><a href="/video/hourly-stream-ranking"
                style="color: white;font-size: larger;">時間別ランキング</a></div>
    </div>

    <div class="contents">
        <div class="container ops-main">
            <div class="row">
                @yield('content')
            </div>
        </div>
    </div>
</body>
