@extends('layouts.layout')

@section('content')
<style type="text/css">
	.loading-buffering{
        background: url("{{asset('img/loading.gif')}}") no-repeat center center;
        background-size: 20px 20px;
    }
</style>
<section id="dashboard-page" class="wrap">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<div class= "page-heading kv-page-hero">
					<h3 class="heading">
						 <!-- <a class="v-btn v-btn-primary pull-right " style="margin-right:8px" href="{{ url('/menu') }}">
							<i class="fa fa-arrow-left " style="margin-right: 5px"> </i>
							BACK
						</a> -->
						<a class="v-btn v-btn-primary pull-right"
							style="margin-right:8px"
							href="javascript:history.back();">
							<i class="fa fa-arrow-left" style="margin-right: 5px"></i>
							BACK
						</a>
						PRODUCT
						<!-- <a class="v-btn v-btn-primary pull-right" href="{{ url('/dashboard') }}">MENU</a> -->
						<a class="v-btn v-btn-primary pull-right" href="#addProductModal" data-toggle="modal">NEW PRODUCT</a>
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
				<div class="widget">
					<div class="widget-content">
						<div class="responsive-table">
							<table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
						        <thead>
						            <tr>
										<th>RANK</th>
						                <th>CODE</th>
						                <th>PRODUCT NAME</th>
						                <th>CATEGORY</th>
						                <th>VENDOR</th>
						                <th>UNIT</th>
						                <th>PRICE</th>
						                <th>ACTION</th>
						            </tr>
						        </thead>
						        <tbody>
						            @foreach($products as $product)
		                            <tr>
										<td>{{ $product->rank }}</td>
		                                <td>{{ $product->code }}</td>
		                                <td>{{ $product->name }}</td>
		                                <td>{{ $product->category }}</td>
		                                <td>{{ $product->vname }}</td>

		                                <td>{{ $product->unit }}</td>
		                                <td> <input type="text" name="price" idval="{{ $product->id }}" value="{{ $product->price }}" size="4" onchange="getPmtMethod(this)"></td>
		                                <td>
		                                    {!! Form::open(array('route' => ['product.destroy', $product->id], 'method'=>'DELETE', 'class' => 'delete')) !!}

		                                        <a href="#editProductModal{{ $product->id }}" class="fa fa-pencil" data-toggle="modal" data-placement="left" title="edit"></a>

		                                        |

												{{ link_to_route('product.proArchive', $product->status == 1 ? 'Archive' : 'Unarchive', ['id' => $product->id, 'st' => $product->status]) }}

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

	<div class="modal fade kv-product-modal" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalTitle">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="addProductModalTitle">Add Product</h4></div>
				<form method="POST" action="{{ url('/product') }}">
					{{ csrf_field() }}
					<div class="modal-body"><div class="kv-product-form-grid">
						<div class="form-group{{ $errors->has('code') ? ' has-error' : '' }}"><label for="add-product-code">Code</label><input id="add-product-code" class="form-control" name="code" value="{{ old('code') }}" required>@if($errors->has('code'))<span class="help-block">{{ $errors->first('code') }}</span>@endif</div>
						<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}"><label for="add-product-name">Product name</label><input id="add-product-name" class="form-control" name="name" value="{{ old('name') }}" required>@if($errors->has('name'))<span class="help-block">{{ $errors->first('name') }}</span>@endif</div>
						<div class="form-group{{ $errors->has('category') ? ' has-error' : '' }}"><label for="add-product-category">Category</label>{{ Form::select('category', $category, old('category'), ['id' => 'add-product-category', 'class' => 'form-control', 'required' => 'required']) }}@if($errors->has('category'))<span class="help-block">{{ $errors->first('category') }}</span>@endif</div>
						<div class="form-group{{ $errors->has('vendor') ? ' has-error' : '' }}"><label for="add-product-vendor">Vendor</label>{{ Form::select('vendor', $seller, old('vendor'), ['id' => 'add-product-vendor', 'class' => 'form-control']) }}@if($errors->has('vendor'))<span class="help-block">{{ $errors->first('vendor') }}</span>@endif</div>
						<div class="form-group{{ $errors->has('unit') ? ' has-error' : '' }}"><label for="add-product-unit">Unit</label><input id="add-product-unit" class="form-control" name="unit" value="{{ old('unit') }}" required>@if($errors->has('unit'))<span class="help-block">{{ $errors->first('unit') }}</span>@endif</div>
						<div class="form-group{{ $errors->has('price') ? ' has-error' : '' }}"><label for="add-product-price">Price</label><input id="add-product-price" type="number" step="0.01" class="form-control" name="price" value="{{ old('price') }}">@if($errors->has('price'))<span class="help-block">{{ $errors->first('price') }}</span>@endif</div>
						<div class="form-group"><label for="add-product-rank">Rank</label><input id="add-product-rank" class="form-control" name="rank" value="{{ old('rank') }}"></div>
					</div></div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="submit" class="btn v-btn-primary kv-product-submit">Add Product</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	@foreach($products as $product)
	<div class="modal fade kv-product-modal" id="editProductModal{{ $product->id }}" tabindex="-1" role="dialog" aria-labelledby="editProductModalTitle{{ $product->id }}">
		<div class="modal-dialog" role="document"><div class="modal-content">
			<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="editProductModalTitle{{ $product->id }}">Edit Product</h4></div>
			
			{!! Form::model($product, [
				'url' => url('/product/' . $product->id),
				'method' => 'PUT'
			]) !!}
			<div class="modal-body"><div class="kv-product-form-grid">
				<div class="form-group"><label>Code</label>{!! Form::text('code', null, ['class' => 'form-control']) !!}@if($errors->has('code'))<span class="help-block">{{ $errors->first('code') }}</span>@endif</div>
				<div class="form-group"><label>Product name</label>{!! Form::text('name', null, ['class' => 'form-control']) !!}@if($errors->has('name'))<span class="help-block">{{ $errors->first('name') }}</span>@endif</div>
				<div class="form-group"><label>Category</label>{{ Form::select('category', $category, $product->product_category_id, ['class' => 'form-control']) }}@if($errors->has('category'))<span class="help-block">{{ $errors->first('category') }}</span>@endif</div>
				<div class="form-group"><label>Unit</label>{!! Form::text('unit', null, ['class' => 'form-control']) !!}@if($errors->has('unit'))<span class="help-block">{{ $errors->first('unit') }}</span>@endif</div>
				<div class="form-group"><label>Price</label>{!! Form::text('price', null, ['class' => 'form-control']) !!}@if($errors->has('price'))<span class="help-block">{{ $errors->first('price') }}</span>@endif</div>
				<div class="form-group"><label>Vendor</label>{{ Form::select('vendor', $seller, $product->product_vendor_id, ['class' => 'form-control']) }}@if($errors->has('vendor'))<span class="help-block">{{ $errors->first('vendor') }}</span>@endif</div>
				<div class="form-group"><label>Rank</label>{!! Form::text('rank', null, ['class' => 'form-control']) !!}</div>
			</div></div>
			<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button><button type="submit" class="v-btn v-btn-primary kv-product-submit">Save Changes</button></div>
			{!! Form::close() !!}
		</div></div>
	</div>
	@endforeach
</section>

<style>
	.kv-product-modal { z-index: 1300 !important; }
	.kv-product-modal .modal-dialog { width: min(720px, calc(100% - 24px)); margin: 30px auto; }
	.kv-product-modal .modal-content { border: 1px solid #d8e2ef; border-radius: 12px; overflow: hidden; box-shadow: 0 18px 48px rgba(16, 39, 68, .24); }
	.kv-product-modal .modal-header { padding: 10px 22px; color: #fff; background: linear-gradient(110deg, #142e4c, #20436d); border-bottom: 0; }
	.kv-product-modal .modal-header h4,
	.kv-product-modal .modal-header .modal-title { margin: 0 0 4px; color: #fff !important; font-weight: 700; }
	.kv-product-modal .modal-header p { margin: 0; color: #d6e2ef; font-size: 12px; }
	.kv-product-modal .modal-header .close { color: #fff; opacity: .85; text-shadow: none; }
	.kv-product-modal .kv-product-submit,
	.kv-product-modal .kv-product-submit:hover,
	.kv-product-modal .kv-product-submit:focus,
	.kv-product-modal .kv-product-submit:active { min-height: 30px; margin: 0; padding: 8px 18px !important; border: 1px solid #f2bd3b !important; border-radius: 7px; color: #17263b !important; background: #f2bd3b !important; box-shadow: none !important; font-size: 12px; font-weight: 700; line-height: 20px; }
	.kv-product-modal .modal-footer .btn-default { min-height: 30px; margin: 0; padding: 8px 18px; border-radius: 7px; }
	.kv-product-modal .modal-body { padding: 20px 22px 8px; }
	.kv-product-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px 18px; }
	.kv-product-form-grid .form-group { margin: 0; }
	.kv-product-form-grid label { display: block; margin-bottom: 6px; color: #34506f; font-size: 12px; font-weight: 600; text-transform: uppercase; }
	.kv-product-form-grid .form-control { height: 40px; border-color: #cbd8e7; border-radius: 6px; box-shadow: none; }
	.kv-product-modal .modal-footer { padding: 14px 22px; border-top: 1px solid #e7edf4; }
	@media (max-width: 560px) { .kv-product-form-grid { grid-template-columns: 1fr; } }
</style>

<script>
	$(function () {
		@if(isset($openAddProductModal) && $openAddProductModal)
			$('#addProductModal').modal('show');
		@elseif($errors->any())
			$('#addProductModal').modal('show');
		@endif
		@if(isset($openEditProductId))
			$('#editProductModal{{ $openEditProductId }}').modal('show');
		@endif
	});
</script>

<script type="text/javascript">
	$(document).ready(function() {
	var tbl=$('#example').DataTable({
				 stateSave: true,
			"aaSorting": []
			});

	});

	$(".delete").on("submit", function(){
        return confirm("Do you want to delete this product?");
    });


    function getPmtMethod(e)
    {
        var price 	= $(e).val();
        var id 		= $(e).attr('idVal');
        $(e).attr('class', 'loading-buffering');
        $.ajax({
            url : "{{ route( 'update-product-price' ) }}",
            data: {
                "_token" : "{{ csrf_token() }}",
                "price" : price,
                "id" : id,
            },
            type    : 'post',
            dataType: 'json',
            success: function( result )
            {
                if(result['result'] == 1)
                	$(e).attr('class', '');
                else
                {
                	$(e).attr('class', '');
                	alert("There is some issue while changing record. Please reload the page and try again later.");
                }
            },
            error: function()
            {
                //handle errors
                alert('error...');
            }
        });
    }
</script>
@endsection
