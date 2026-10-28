@extends('layouts.layout')

@section('content')
<section id="dashboard-page" class="wrap">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<div class="page-heading">
					<h3 class="heading">VIEW EMPLOYEE
					<a class="v-btn v-btn-primary pull-right" href="{{ url('/menu') }}">BACK</a>
					<a class="v-btn v-btn-primary pull-right" href="{{ url('/dashboard') }}">DASHBOARD</a>
                	<a class="v-btn v-btn-primary pull-right" href="{{ url('/employee/create') }}">CREATE NEW EMPLOYEE</a>
					</h3>
	                <br /> 
				</div>
			</div>
		</div>

		@if(Session::has('message'))
            <div class="alert alert-success">
                {{ Session::get('message') }}
            </div>
        @endif

        
		<div class="row">
			<div class="col-sm-12 col-xs-12">
				<div class="widget">
					<div class="widget-content">
							<div class="responsive-table">
								<table id="exampleTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
							        <thead>
							            <tr>
							                <th>NAME</th>
							                <th>EMAIL</th>
							                <th>ADDRESS</th>
							                <th>PAYMENT RATE</th>
											<th>LOATION</th>
							                <th>ACTION</th>
							            </tr>
							        </thead>
							        <tbody>
							            @foreach($employees as $employee)
			                            <tr>
			                                <td>{{ $employee->empname }}</td>
			                                <td>{{ $employee->empemail }}</td>
			                                <td>{{ $employee->empaddress }}</td>
			                                <td>{{ $employee->paymentrate }}</td>
											<td>{{ $employee->location }}</td>
			                                <td>
			                                    {!! Form::open(array('route' => ['employee.destroy', $employee->id], 'method'=>'DELETE', 'class' => 'delete')) !!}
			                                        
			                                        {{ link_to_route('employee.edit','',[$employee->id],['class' => 'fa fa-pencil','data-toggle'=>"tooltip", 'data-placement'=>"left", 'title'=>"edit"]) }}
			                                        
			                                        |   
													
													{{ link_to_route('employee.empArchive', $employee->status == 1 ? 'Archive' : 'Unarchive', ['id' => $employee->id, 'st' => $employee->status]) }}
													
													
													
			                                         |
			                                        {{ Form::button('',['class'=>'fa fa-close','type'=>'submit','data-toggle'=>"tooltip", 'data-placement'=>"right", 'title'=>"delete"]) }} 

			                                    {!! Form::close() !!}
			                                     
			                                </td>
			                            </tr>
			                            @endforeach
							        </tbody>
							    </table>
							</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<script type="text/javascript">
	$(document).ready(function() {
	    $('#exampleTable').DataTable();
	} );

	$(".delete").on("submit", function(){
        return confirm("Do you want to delete this user?");
    });
</script>
@endsection
