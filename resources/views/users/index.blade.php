@extends('layouts.layout')

@section('content')
<section id="dashboard-page" class="wrap">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<div class= "page-heading kv-page-hero">
					<h3 class="heading"> 
						<a class="v-btn v-btn-primary pull-right"
							style="margin-right:8px"
							href="javascript:history.back();">
							<i class="fa fa-arrow-left"></i>
							BACK
						</a>
						<!-- <a class="v-btn v-btn-primary pull-right " style="margin-right:8px" href="{{ url('/menu') }}">
							<i class="fa fa-arrow-left " style="margin-right: 5px"> </i>
							BACK
						</a>  -->
						USER
						<!-- <a class="v-btn v-btn-primary pull-right" href="{{ url('/dashboard') }}">MENU</a> -->
						<a class="v-btn v-btn-primary pull-right" href="{{ url('/user/create') }}">NEW USER</a>
					</h3>
				</div>
				<br />
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
								<table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
							        <thead>
							            <tr>
							                <th>NAME</th>
							                <th>EMAIL</th>
							                <th>PHONE</th>
							                <th>ROLE</th>
							                <th>ACTION</th>
							            </tr>
							        </thead>
							        <tbody>
							            @foreach($users as $user)
			                            <tr>
			                                <td>{{ $user->name }}</td>
			                                <td>{{ $user->email }}</td>
			                                <td>{{ $user->phone }}</td>
			                                <td>{{ $user->role }}</td>
			                                <td>
			                                    {!! Form::open(array('route' => ['user.destroy', $user->id], 'method'=>'DELETE', 'class' => 'delete')) !!}

			                                        {{ link_to_route('user.edit','',[$user->id],['class' => 'fa fa-pencil','data-toggle'=>"tooltip", 'data-placement'=>"left", 'title'=>"edit"]) }}

													|

													{{ link_to_route('user.archive', $user->status == 1 ? 'Archive' : 'Unarchive', ['id' => $user->id, 'st' => $user->status]) }}
			                                        <!-- |
			                                        {{ Form::button('',['class'=>'fa fa-close','type'=>'submit','data-toggle'=>"tooltip", 'data-placement'=>"right", 'title'=>"delete"]) }} -->

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
