
<!doctype html>
<html lang="en" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.88.1">
    <title>Review App</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/5.1/examples/cover/">



    <!-- Bootstrap core CSS -->
    <link href="{{ URL::asset('/asset/review/css/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('/asset/review/css/style.css') }}" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="cover.css" rel="stylesheet">
</head>
<body class=" h-100">
<div class="header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="logo text-center py-5">
                    <img class="m-auto" src="{{ URL::asset('/asset/review/images/dummylogo.png') }}"/>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="main-body">
    <div class="container">
        <div class="row align-content-center justify-content-center">
            <div class="col-8 col-sm-9 col-md-9 col-lg-6 col-xl-6">
                <div class="card">
                    <div class="header-name">
                        <div class="row align-items-center">
                            <div class="col-1 p-0">
                                <div class="avartar"><img src="{{ URL::asset('/asset/review/images/avatar.png') }}"/> </div>
                            </div>
                            <div class="col-11 ">
                                <div class="name">Alice Krejčová</div>
                                <div class="time">26 minutes ago</div>
                            </div>
                        </div>
                    </div>
                    <div class="image-slider">
                        <div class="img"><img src="{{ URL::asset('/asset/review/images/img.png') }}"></div>
                    </div>
                    <div class="content-body">
                        <div class="title">Parrot In The Oven - <span>#Victor</span> <span>#Martinez<span></div>
                        <div class="description"><p>It’s obvious that Amazon is trying to make its Alexa personal assistant as ubiquitous as possible. At today’s marathon product introduction, the company debuted a wide variety of its Echo-branded #Alexa-powered #products, both for the home and for the outdoors. It’s obvious that Amazon is trying to make its Alexa personal assistant as ubiquitous as possible. </p></div>
                        <div class="link-block"><img src="{{ URL::asset('/asset/review/images/img.png') }}"> www.amazon.com/apple/iphone14 </div>
                    </div>
                    <div class="content-footer">
                        <div class="cta-block">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="like"><img src="{{ URL::asset('/asset/review/images/like.png') }}"> 203 </div>
                                    <div class="comment"><img src="{{ URL::asset('/asset/review/images/comment.png') }}"> 203 </div>
                                    <div class="share"><img src="{{ URL::asset('/asset/review/images/share.png') }}"></div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="like">40.5k Views</div>
                                    <div class="follow-btn"><button class="btn btn-primary">follow</button></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="footer mt-auto py-3 bg-light">
    <h3>See more of post on our Reviewapp</h3>
    <div class="text-center"><img src="{{ URL::asset('/asset/review/images/google-play.png') }}"/> </div>
</footer>

</body>
</html>
