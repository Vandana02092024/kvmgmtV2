@extends('layouts.layout')

@section('content')

<section id="dashboard-page" class="wrap">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<div class= "page-heading kv-page-hero">
					<h3 class="heading">
						<!-- <a class="v-btn v-btn-primary pull-right" style="margin-right:8px" href="{{ url('/menu') }}">
							<i class="fa fa-arrow-left " style="margin-right: 5px"> </i>
							BACK
						</a> -->
						<a class="v-btn v-btn-primary pull-right"
							style="margin-right:8px"
							href="javascript:history.back();">
							<i class="fa fa-arrow-left"></i>
							BACK
						</a>
						 CATEGORIES
						<!-- <a class="v-btn v-btn-primary pull-right" href="{{ url('/dashboard') }}">MENU</a> -->
						<a class="v-btn v-btn-primary pull-right" href="#addCategoryModal" data-toggle="modal">NEW CATEGORY</a>
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
			<div class="col-sm-12">
				<div class="widget kv-category-table">
					<div class="widget-content">
							<div class="responsive-table">
								<table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
							        <thead>
							            <tr>
							                <th>RANK</th>
							                <th>CATEGORY</th>
							                <th>ACTION</th>
							            </tr>
							        </thead>
							        <tbody>
							            @foreach($categories as $category)
			                            <tr>
			                                <td>{{ $category->rank }}</td>
			                                <td>{{ $category->category }}</td>
			                                <td>
			                                    {!! Form::open(array('route' => ['category.destroy', $category->id], 'method'=>'DELETE', 'class' => 'delete')) !!}
			                                        
		                                <a href="#editCategoryModal{{ $category->id }}" class="fa fa-pencil" data-toggle="modal" data-placement="left" title="Edit category" aria-label="Edit {{ $category->category }}"></a>
			                                        
			                                        |   
													
													{{ link_to_route('category.catArchive', $category->status == 1 ? 'Archive' : 'Unarchive', ['id' => $category->id, 'st' => $category->status]) }}
													
													
													
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

		<div class="modal fade kv-location-modal" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryTitle">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<form method="POST" action="{{ url('/category') }}">
						{{ csrf_field() }}
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="addCategoryTitle">Add Category</h4>
						</div>
						<div class="modal-body">
							<div class="row">
								<div class="col-md-6">
									<div class="form-group{{ $errors->has('category') ? ' has-error' : '' }}">
										<label for="new-category-name">CATEGORY</label>
										<input type="text"
											id="new-category-name"
											name="category"
											class="form-control"
											value="{{ old('category') }}"
											required>

										@if ($errors->has('category'))
											<span class="help-block">{{ $errors->first('category') }}</span>
										@endif
									</div>
								</div>

								<div class="col-md-6">
									<div class="form-group">
										<label for="new-category-rank">RANK</label>
										<input id="new-category-rank"
											type="number"
											name="rank"
											class="form-control"
											value="{{ old('rank') }}">
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn kv-category-cancel" data-dismiss="modal">Cancel</button>
							<button type="submit" class="btn kv-category-submit">Add Category</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		@foreach($categories as $category)
			<div class="modal fade kv-location-modal" id="editCategoryModal{{ $category->id }}" tabindex="-1" role="dialog" aria-labelledby="editCategoryTitle{{ $category->id }}">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<!-- <form method="POST" action="{{ url('/category/' . $category->id) }}">
							{{ csrf_field() }}
							{{ method_field('PUT') }}
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title" id="editCategoryTitle{{ $category->id }}">Edit Category</h4>
							</div>
							<div class="modal-body">
								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<label for="category-name-{{ $category->id }}">CATEGORY</label>
											<input type="text"
												id="category-name-{{ $category->id }}"
												name="category"
												class="form-control"
												value="{{ $category->category }}"
												required>
										</div>
									</div>

									<div class="col-md-6">
										<div class="form-group">
											<label for="category-rank-{{ $category->id }}">RANK</label>
											<input id="category-rank-{{ $category->id }}"
												type="number"
												name="rank"
												class="form-control"
												value="{{ $category->rank }}">
										</div>
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn kv-category-cancel" data-dismiss="modal">Cancel</button>
								<button type="submit" class="btn kv-category-submit">Save Changes</button>
							</div>
						</form> -->

						<form method="POST" action="{{ url('/category/' . $category->id) }}">
							@csrf
							@method('PUT')

							<div class="modal-body">
								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<label for="category-name-{{ $category->id }}">CATEGORY</label>
											<input
												type="text"
												id="category-name-{{ $category->id }}"
												name="category"
												class="form-control"
												value="{{ $category->category }}"
												required>
										</div>
									</div>

									<div class="col-md-6">
										<div class="form-group">
											<label for="category-rank-{{ $category->id }}">RANK</label>
											<input
												type="number"
												id="category-rank-{{ $category->id }}"
												name="rank"
												class="form-control"
												value="{{ $category->rank }}">
										</div>
									</div>
								</div>
							</div>

							<div class="modal-footer">
								<button type="button" class="btn kv-category-cancel" data-dismiss="modal">
									Cancel
								</button>
								<button type="submit" class="btn kv-category-submit">
									Save Changes
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		@endforeach
	</div>
</section>

<script type="text/javascript">
	$(document).ready(function() {
	    $('#example').DataTable();
		@if (isset($openAddCategoryModal) && $openAddCategoryModal)
			$('#addCategoryModal').modal('show');
		@endif
		@if (isset($openEditCategoryId))
			$('#editCategoryModal{{ $openEditCategoryId }}').modal('show');
		@endif
	});
	@if ($errors->has('category'))
		$('#addCategoryModal').modal('show');
	@endif
	$(".delete").on("submit", function(){
        return confirm("Do you want to delete this user?");
    });
</script>
@endsection
