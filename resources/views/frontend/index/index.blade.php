@extends('layouts.app')

@section('content')

    <div class="container-fluid bg-primary pt-60 pb-40">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-sm-12">
                    <div class="card hover-shadow-2">
                        <h4 class="card-title"><strong>关于学习的看法</strong></h4>
                        <div class="card-body text-dark fw-500">
                            <p>
                                很多人对花几百块钱吃一顿饭毫不犹豫，但是对花几百块钱投入学习却犹豫不决。你说TA舍得消费，但是TA又不舍得消费。其实，这很正常，花钱吃饭是一件享受的事情，
                                但是花钱学习却是一件痛苦的事情，学习从来都不是快乐的事情，这点毋庸置疑。所以，很多情况下，人们总是待在自己的舒适区内，并不是TA不愿意投入，而是不愿意逃离
                                自己的舒适圈。
                            </p>
                            <p>
                                2019年很多互联网公司都在裁员，裁员规模颇大，很多人失业。其实互联网发展到现在，早期的红利到现在已经分的差不多了，如果技术能力不过关，很有可能就在这次大浪之中被淘汰。
                                这并不是危言耸听，你会发现，现在很多大学生的技术能力都超过一线的工作人员，这个现象想在越来越普遍，究其原因主要是高校的专业化培养和越来越多的学习资料。一线中的技术人员
                                如果没有持续学习的能力，很快就会被技术能力强的年轻人所取代。
                            </p>
                            <p>
                                做技术必须要保持终生学习的习惯！
                            </p>
                            <p class="text-right">

                            </p>
                        </div>
                    </div>
                <div class="col-md-4 col-sm-12" style="padding-top: 160px;">
                    <h1 style="padding: 10px 20px; background-color: #fff; color: #4ed2c5; font-weight: 800;">
                        一切，从这里开始。</h1>
                    <p class="mt-4">
                        这里是一些关于我们的介绍
                    </p>
                    <p class="mt-4">
                        <a href="{{route('courses')}}" class="btn btn-lg btn-danger">全部课程</a>
                    </p>
                </div>

                <div class="col-md-8 col-sm-12">
                    <img src="/images/index-banner-img.svg">
                </div>
            </div>
        </div>
    </div>

    <div class="container pt-40 pb-40">
        <div class="row">
            <div class="col-sm-12">
                <div class="divider"><a class="fs-24" href="#">最新课程</a></div>
            </div>
            <div class="col-sm-12">
                <div class="card-deck">
                    @foreach($gLatestCourses as $index => $course)
                        @if($index > 2)
                            @break
                        @endif
                        <div class="col-sm-4">
                            <div class="card hover-shadow-2">
                                <img class="card-img-top" src="{{ image_url($course['thumb']) }}"
                                     alt="{{$course['title']}}">
                                <div class="card-body">
                                    <h4 class="card-title b-0 px-0">
                                        <a href="{{ route('course.show', [$course['id'], $course['slug']]) }}">{{$course['title']}}</a>
                                    </h4>
                                    <p>
                                        <small>最后更新：{{$course['updated_at']}}</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid pt-40 pb-40 bg-dark">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="divider"><span class="fs-24">评价</span></div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-inverse" style="background-color: #3b5998">
                        <div class="card-header no-border">
                            <h5 class="card-title card-title-bold">张三</h5>
                        </div>
                        <blockquote class="blockquote blockquote-inverse no-border card-body m-0">
                            <p>这是个好课程 </p>
                            <div class="flexbox">
                                <time class="text-white" datetime="2017-10-02 20:00">2019/3/31</time>
                            </div>
                        </blockquote>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card card-inverse" style="background-color: #3b5998">
                        <div class="card-header no-border">
                            <h5 class="card-title card-title-bold">张三</h5>
                        </div>
                        <blockquote class="blockquote blockquote-inverse no-border card-body m-0">
                            <p>这是个好课程 </p>
                            <div class="flexbox">
                                <time class="text-white" datetime="2017-10-02 20:00">2019/3/31</time>
                            </div>
                        </blockquote>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card card-inverse" style="background-color: #3b5998">
                        <div class="card-header no-border">
                            <h5 class="card-title card-title-bold">张三</h5>
                        </div>
                        <blockquote class="blockquote blockquote-inverse no-border card-body m-0">
                            <p>这是个好课程</p>
                            <div class="flexbox">
                                <time class="text-white" datetime="2017-10-02 20:00">2019/3/31</time>
                            </div>
                        </blockquote>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container pt-70 pb-40">
        <div class="row justify-content-center">
            @foreach($gRoles as $index => $role)
                <div class="col-lg-4">
                    <div class="card hover-shadow-2">
                        <div class="card-body text-center">
                            <h5 class="text-uppercase text-muted">{{$role['name']}}</h5>
                            <br>
                            <h3 class="price">
                                <sup>￥</sup>{{$role['charge']}}
                                <span>&nbsp;</span>
                            </h3>
                            <hr>
                            @foreach(explode("\n", $role['description']) as $row)
                                <p>{{$row}}</p>
                            @endforeach
                            <br><br>
                            <a class="btn btn-bold btn-block btn-primary" href="{{route('role.index')}}">立即订阅</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if($links)
        <div class="container pt-40 pb-40">
            <div class="row">
                <div class="col-sm-12">
                    <h5>友情链接</h5>
                    @foreach($links as $link)
                        <a href="{{$link['url']}}" target="_blank"
                           style="margin-right: 2px; margin-bottom: 2px;">{{$link['name']}}</a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

@endsection
