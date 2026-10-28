@extends('layouts.layout')

@section('content')
<section id="dashboard-page" class="wrap">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-heading">
                    <h3 class="heading">EDIT PAYROLL CATEGORY
                    <a class="v-btn v-btn-primary pull-right bg-light" href="{{ url('/payrollcategory') }}">BACK</a>
                    <a class="v-btn v-btn-primary pull-right" href="{{ url('/payrollcategory/create') }}">PAYROLL CATEGORY</a>
					</h3>
                    <br /> 
                </div>
            </div>
        </div>
        <div class="row">

            @if(Session::has('message'))
                <div class="alert alert-success">
                    {{ Session::get('message') }}
                </div>
            @endif

            <div class="col-sm-8 col-sm-offset-2">
                <div class="widget">
                    <div class="">
                        {!! Form::model($payrollcategory, array('route' => ['payrollcategory.update', 'payrollcategory' => $payrollcategory->id], 'method' => 'PUT')) !!}
                        {{ csrf_field() }}



                            <div class="widget-content">

                               

                                <div class="row form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                    <div class="col-sm-4">
                                        <label for="name">PAYROLL CATEGORY</label>
                                        {!! Form::text('name', null, ['class' => 'form-control']) !!}

                                        @if ($errors->has('name'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('name') }}</strong>
                                            </span>
                                        @endif
                                    </div>                          
                                </div>
								
								
								 <div class="row form-group{{ $errors->has('pay_rate_multiplier') ? ' has-error' : '' }}">
                                    <div class="col-sm-4">
                                        <label for="pay_rate_multiplier">PAY RATE MULTIPLIER</label>
                                        {!! Form::text('pay_rate_multiplier', null, ['class' => 'form-control']) !!}

                                        @if ($errors->has('pay_rate_multiplier'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('pay_rate_multiplier') }}</strong>
                                            </span>
                                        @endif
                                    </div>                          
                                </div>
                                <div class="row form-group{{ $errors->has('sorting_order') ? ' has-error' : '' }}">
                                    <div class="col-sm-4">
                                        <label for="sorting_order">PAYROLL DEPARTMENT SORTING NO.</label>
                                        {!! Form::text('sorting_order', null, ['class' => 'form-control']) !!}

                                        @if ($errors->has('sorting_order'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('sorting_order') }}</strong>
                                            </span>
                                        @endif
                                    </div>                          
                                </div>
                               
                               
								
								
								
								
                               
                                <div class="row form-group{{ $errors->has('location') ? ' has-error' : '' }}">
                                    <div class="col-sm-12">
                                        <label>SELECT LOCATION</label>
                                        @foreach($locations as $id => $location)
                                        <div class="checkbox">
                                            @if(is_array($selLocations) && ($x = array_search($id, $selLocations)))
                                            <label id="div{{ $id }}">
                                                {!!link_to_route('resetlocationpayrollcat', '', ['id' => $x, 'payrollcategory' => $payrollcategory->id], ['class' => 'fa fa-close anchor','data-toggle'=>"tooltip", 'data-placement'=>"left", 'title'=>"reset location"])!!}
                                                {{ $location }}
                                            </label>
                                            @else
                                            <label>
                                                <input type="checkbox" name="location[]" value="{{ $id }}"  >{{ $location }}
                                            </label>
                                            @endif
                                            
                                        </div>
                                        @endforeach

                                        @if ($errors->has('location'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('location') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="widget-footer">
                                <button class="v-btn v-btn-primary">UPDATE</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
$(".fa-close").on("click", function(){
        if(confirm("Do you want to reset this location?")){
            return true;
        }else{
            return false;
        }
    });
</script>
@endsection
