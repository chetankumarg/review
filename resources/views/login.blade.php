<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Review - Login</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ URL::asset('/asset/css/sb-admin-2.min.css') }}" rel="stylesheet">

</head>

<body class="bg-gradient-primary">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                    </div>
                                    <form action="{{url('post-login')}}" method="POST" id="logForm"> 
                                            {{ csrf_field() }} 
                                            <div class="form-label-group">
                                            <label for="inputEmail">Email address</label>
                                            <input type="email" name="email" id="inputEmail" class="form-control" placeholder="Email address" >
                                            
                            
                                            @if ($errors->has('email'))
                                            <span class="error">{{ $errors->first('email') }}</span>
                                            @endif    
                                            </div> 
                            
                                            <div class="form-label-group">
                                            <label for="inputPassword">Password</label>
                                            <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password">
                                            
                                            
                                            @if ($errors->has('password'))
                                            <span class="error">{{ $errors->first('password') }}</span>
                                            @endif  
                                            </div>
                            
                                            <button class="btn btn-lg btn-primary btn-block btn-login text-uppercase font-weight-bold mb-2" type="submit">Login In</button>
                                            <!-- <div class="text-center">If you have an account?
                                            <a class="small" href="{{url('registration')}}">Login</a></div> -->
                                    </form>
                                    <hr>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{ URL::asset('/asset/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ URL::asset('/asset/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ URL::asset('/asset/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{ URL::asset('/asset/js/sb-admin-2.min.js') }}"></script>

</body>

</html>