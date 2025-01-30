@extends('landing-page.layouts.default')

@section('content')
    
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-8 mt-3">
                <h2 class="heading_text">{{ $jobrequest->title }}</h2>
                <div class="d-flex justify-content-between">
                    <p class="mb-0 text-dark"><b>
                        
                   
                    <p>Created By: <span>{{ $jobrequest->provider->username??'N/A' }}</span></p>
                </div>
                <img src="https://noorhantrdg.com/wp-content/uploads/2021/10/mechanic-changing-engine-oil-car-vehicle-1-1.jpg" alt="" class="img-fluid" style="border-radius: 12px;">
                <img src="https://noorhantrdg.com/wp-content/uploads/2021/10/mechanic-changing-engine-oil-car-vehicle-1-1.jpg" style="width: 100px; border-radius: 10px;" class="mt-3" alt="">
            </div> 
            <div class="col-md-4 mt-3">
                <h5 class="mt-4">$ {{ $jobrequest->price }} <span class="text-dark"><b>/ Hour</b></span> </h5>
                <p class="mb-0">{{ $jobrequest->description }}</p>
                <a href="#">Read More</a>
                <hr>
                <button class="btn btn-cont text-white col-md-12" style="background: #5F60BA; padding: 10px;">Continue</button>
                <img src="https://www.shutterstock.com/shutterstock/videos/1045125877/thumb/12.jpg?ip=x480" style="width: 200px;" alt="">
                <button class="btn btn-job col-md-12" style="background: #018E27; border-radius: 50px; color: red;padding: 10px;">About Job Request</button>
                <ul class="pl-0 mt-3" style="list-style: none; font-weight: 500; ">
                    <li><a href="#" class="text-dark"> <b>Published at: </b>  NYC
                    </a></li>
                    <li><a href="#" class="text-dark"> <b>Customer :</b> {{ $jobrequest->customer->username?? 'N/A'}}
                    </a></li>
                   
                    <li><a href="#" class="text-dark"> <b>Location:</b> Germany
                    </a></li>
                    <li><a href="#" class="text-dark"> <b>Category: </b>{{ $jobrequest->category->name?? 'N/A'}}
                    </a></li>
                    <li><a href="#" class="text-dark"> <b>Type:</b>  <span> {{ $jobrequest->type?? 'N/A'}}</span>
                    </a></li>
                    <li><a href="#" class="text-dark"> <b>Start Date:</b> {{ $jobrequest->start_date?? 'N/A'}}
                    </a></li>
                    <li><a href="#" class="text-dark"> <b>End Date:</b> {{ $jobrequest->end_date?? 'N/A'}}
                    </a></li>
                    <li><a href="#" class="text-dark"><b>Status:</b>  {{ $jobrequest->status?? 'N/A'}}
                    </a></li>
                    <li><a href="#" class="text-dark"> <b>Total Bids:</b> {{ $totalBids }}
                    </a></li>
                    <li><a href="#" class="text-dark"> <b>Total Views:</b>
                    </a></li>
                </ul>
                <button class="btn btn-green" style="background: #59E054; color: #fff; border: 5px solid #F0F0F0; padding: 10px 30px; border-radius: 50px; font-weight: 800; display: flex; margin: auto;">APPLY NOW</button>
                <p class="text-dark text-center"><b>(can apply only if Status "Open")</b></p>
            </div> 
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
 

    @endsection