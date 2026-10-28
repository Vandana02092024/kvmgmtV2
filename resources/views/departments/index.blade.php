@extends('layouts.layout')

@section('content')

<section id="dashboard-page" class="wrap">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<div class="page-heading">
					<h3 class="heading">DEPARTMENTS
					<a class="v-btn v-btn-primary pull-right" href="{{ url('/menu') }}">BACK</a>
					<a class="v-btn v-btn-primary pull-right" href="{{ url('/dashboard') }}">DASHBOARD</a>
                	<a class="v-btn v-btn-primary pull-right" href="{{ url('/department/create') }}"> NEW DEPARTMENT</a>
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
			<div class="col-sm-12">
				<div class="widget">
					<div class="widget-content">
							<div class="responsive-table">
								<table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
							        <thead>
							            <tr>
							                <th>SR. NO</th>
							                <th>DEPARTMENT</th>
							                <th>ACTION</th>
							            </tr>
							        </thead>
							          <tbody>
										<?php $i = 0; ?>
							            @foreach($departments as $dep)
										<?php $i++; ?>
 			                            <tr>
											<td><?php echo $i; ?> </td>
			                                <td>{{ $dep->department }}</td>
			                                <td>
			                                    {!! Form::open(array('route' => ['department.destroy', $dep->id], 'method'=>'DELETE', 'class' => 'delete')) !!}
			                                        
			                                        {{ link_to_route('department.edit','',[$dep->id],['class' => 'fa fa-pencil','data-toggle'=>"tooltip", 'data-placement'=>"left", 'title'=>"edit"]) }}
			                                        
			                                        |   
													
													{{ link_to_route('department.depArchive', $dep->status == 1 ? 'Archive' : 'Unarchive', ['id' => $dep->id, 'st' => $dep->status]) }}
													
													
													
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
	    $('#example').DataTable();
	} );
	$(".delete").on("submit", function(){
        return confirm("Do you want to delete this user?");
    });
</script>
@endsection
