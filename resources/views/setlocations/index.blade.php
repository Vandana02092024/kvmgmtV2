@extends('layouts.layout')
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>    
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
<style type="text/css">
    .text-transform-uppercase {
    text-transform: uppercase;
}
</style>

@section('content')
<section id="loginPage" style="padding-top: 40px;" class="wrap">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <h4 class="mt-4 mb-5" style="font-size: 32px"> SELECT A LOCATION</h4>
            </div>
        </div>
        <div class="row" style="margin-top: 40px;">
            <?php // var_dump($locations); ?>
            @if($locations->isEmpty())
                <div class="col-md-12 text-center">
                    <div class="alert alert-warning" role="alert">
                        No active locations are available for your account. Please contact an administrator.
                    </div>
                </div>
            @endif
            @foreach($locations as $key => $location)
            
            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 ">
                <div>
                    <form role="form" method="POST" action="{{ url('/setlocation') }}">
                    {{ csrf_field() }}
                    <input type="hidden" name="location" value="{{ $key }}">
                    <button type="submit" class="stat d-block py-5 w-100 desc ml-0 text-transform-uppercase">{{ $location }}</button>
                    </form>
                </div>
            </div>
            
            @endforeach

        </div>
               
        <!-- <div class="row">
            <div class="col-sm-4 col-sm-offset-4">
                <div class="brand-logo">
                    <h2 class="text-center uppercase">KV Management LLC</h2>
                </div>
                <form class="widget" role="form" method="POST" action="{{ url('/setlocation') }}">
                        {{ csrf_field() }}
                    <div class="widget-header">
                        <h4 class="inline">SET LOCATION</h4>
                    </div>

                    <div class="widget-content">
                            <div class="row form-group{{ $errors->has('location') ? ' has-error' : '' }}">
                                <div class="col-sm-12">
                                    {{ Form::select('location', $locations, old('location'),['class' => 'form-control', 'id' => 'locationid']) }}

                                    @if ($errors->has('location'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('location') }}</strong>
                                        </span>
                                    @endif

                                </div>
                            </div>


                                    
                                    
                                    
                            <div id="showSession">
                                @php
                                    session()->put('location_id', 0);
                                @endphp
                            </div>

                        </div>
                    
                    <div class="widget-footer skin-grey">
                        <div class="checkbox inline">
                        </div>
                        <button type="submit" class="v-btn v-btn-primary pull-right">
                            SUBMIT
                        </button>
                    </div>
                </form>
                
            </div>
        </div> -->
    </div>
</section>  
<script type="text/javascript">

// $('#locationid').on('change', function(event){
//     change_session($(this).find(":selected").val());
// });

// function change_session(id)
// {
//     $.ajax({
//         type:       "POST",
//         dataType:   "html",
//         url:        "{{ url('/') }}/changeSessionValue",
//         data: {location: id},
//         success:    function(data)
//         {
//             // alert(data+'HI');
//             // $("#showSession").html(data);
//         }
//     });
// }

</script>
@endsection
