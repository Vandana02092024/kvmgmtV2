@extends('layouts.layout')

@section('content')
<section id="dashboard-page" class="wrap">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-heading">
                    <h3 class="heading">EDIT PAYROLL DEPARTMENT
                    <a class="v-btn v-btn-primary pull-right bg-light" href="{{ url('/payrollemployee') }}">BACK</a>
                    <a class="v-btn v-btn-primary pull-right" href="{{ url('/payrollemployee/create') }}">NEW PAYROLL DEPARTMENT</a>
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
                        {!! Form::model($payrollemployee, array('route' => ['payrollemployee.update', 'payrollemployee' => $payrollemployee->id], 'method' => 'PUT')) !!}
                        {{ csrf_field() }}



                            <div class="widget-content">

                               

                                <div class="row form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                    <div class="col-sm-4">
                                        <label for="name">PAYROLL EMPLOYEE</label>
                                        {!! Form::text('name', null, ['class' => 'form-control']) !!}

                                        @if ($errors->has('name'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('name') }}</strong>
                                            </span>
                                        @endif
                                    </div>                          
                                </div>
								
								<div class="row form-group{{ $errors->has('salary') ? ' has-error' : '' }}">
                                    <div class="col-sm-4">
                                        <label for="salary">SALARY</label>
                                        {!! Form::text('salary', null, ['class' => 'form-control']) !!}

                                        @if ($errors->has('salary'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('salary') }}</strong>
                                            </span>
                                        @endif
                                    </div>                          
                                </div>
								
								<!--<div class="row form-group{{ $errors->has('working_hour') ? ' has-error' : '' }}">
                                    <div class="col-sm-4">
                                        <label for="working_hour">WORKING HOURS</label>
                                        {!! Form::text('working_hour', null, ['class' => 'form-control']) !!}

                                        @if ($errors->has('working_hour'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('working_hour') }}</strong>
                                            </span>
                                        @endif
                                    </div>                          
                                </div>-->
								
								 <div class="row form-group{{ $errors->has('rateperhour') ? ' has-error' : '' }}">
                                    <div class="col-sm-4">
                                        <label for="rateperhour">PAYROLL EMPLOYEE RATE/HOUR</label>
                                        {!! Form::text('rateperhour', null, ['class' => 'form-control']) !!}

                                        @if ($errors->has('rateperhour'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('rateperhour') }}</strong>
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
                               
								
								
								
								
                               
                                <div class="row form-group{{ $errors->has('department') ? ' has-error' : '' }}">
                                    <div class="col-sm-12">
                                        <label>SELECT DEPARTMENT</label>
                                        @foreach($departments as $id => $department)
                                        <div class="checkbox">
                                            @if(is_array($selDepartments) && ($x = array_search($id, $selDepartments)))
                                            <label id="div{{ $id }}">
                                                {!!link_to_route('removeSelectedDepartment', '', ['id' => $x, 'payrollemployee' => $payrollemployee->id], ['class' => 'fa fa-close department_cl anchor','data-toggle'=>"tooltip", 'data-placement'=>"left", 'title'=>"reset department"])!!}
                                                {{ $department }}
                                            </label>
                                            @else
                                            <label>
                                                <input type="checkbox" name="department[]" value="{{ $id }}"  >{{ $department }}
                                            </label>
                                            @endif
                                            
                                        </div>
                                        @endforeach

                                        @if ($errors->has('department'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('department') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
								
								
								<div class="row form-group{{ $errors->has('location') ? ' has-error' : '' }}">
                                    <div class="col-sm-12">
                                        <label>SELECT LOCATION</label>
                                        @foreach($locations as $id => $location)
										<div class="checkbox">
                                            @if(is_array($sellocation) && ($x = array_search($id, $sellocation)))
                                            <label id="div{{ $id }}">
                                                {!!link_to_route('removeEmployeeLocation', '', ['id' => $x, 'payrollemployee' => $payrollemployee->id], ['class' => 'fa fa-close location anchor','data-toggle'=>"tooltip", 'data-placement'=>"left", 'title'=>"reset location"])!!}
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
$(".department_cl").on("click", function(){
        if(confirm("Do you want to reset this department?")){
            return true;
        }else{
            return false;
        }
    });
$(".location").on("click", function(){
        if(confirm("Do you want to reset this location?")){
            return true;
        }else{
            return false;
        }
    });	
</script>
@endsection
