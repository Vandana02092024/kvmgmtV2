@extends('layouts.layout')
@section('content')
<section id="dashboard-page" class="wrap">
    <div class="container-fluid">
        <div class="row">
			<div class="col-sm-12">
				<div class= "page-heading kv-page-hero">
					<h3 class="heading">PAYROLL CATEGORIES
                        <a class="v-btn v-btn-primary pull-right"
                            style="margin-right:8px"
                            href="javascript:history.back();">
                            <i class="fa fa-arrow-left" style="margin-right: 5px"></i>
                            BACK
                        </a>
                        <!-- <a class="v-btn v-btn-primary pull-right bg-light" href="{{ url('/menu') }}">
                            BACK
                        </a> -->
                        <!-- <a class="v-btn v-btn-primary pull-right" href="{{ url('/dashboard') }}">DASHBOARD</a> -->
                        <a class="v-btn v-btn-primary payroll-add-trigger pull-right" href="{{ url('/payrollcategory/create') }}">NEW PAYROLL CATEGORY</a>
                    </h3>
				</div>
				<br />
			</div>
		</div>
        @if(Session::has('message'))
        <div class="alert alert-success">{{ Session::get('message') }}</div>@endif
        <div class="row">
            <div class="col-sm-12 col-xs-12">
                <div class="widget">
                    <div class="widget-content">
                        <div class="responsive-table">
                            <table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>PAYROLL CATEGORY</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payrollcategories as $payrollcat)
                                    <tr>
                                        <td>{{ $payrollcat->name }}</td>
                                        <td>
                                        {!! Form::open(array('route' => ['payrollcategory.destroy', $payrollcat->id], 'method'=>'DELETE', 'class' => 'delete')) !!}
                                        
                                        {{ link_to_route('payrollcategory.edit', '', [$payrollcat->id], ['class' => 'fa fa-pencil', 'data-toggle' => 'tooltip', 'data-placement' => 'left', 'title' => 'Edit']) }}

                                        <!-- {{ link_to_route('payrollcategory.edit','',[$payrollcat->id],['class' => 'fa fa-pencil','data-toggle'=>'tooltip','data-placement'=>'left','title'=>'Edit']) }} &nbsp;|&nbsp; -->
                                        {{ link_to_route('payrollcategory.archive', $payrollcat->status == 1 ? 'Archive' : 'Unarchive', ['id' => $payrollcat->id, 'st' => $payrollcat->status]) }}
                                        {!! Form::close() !!}</td>
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
@php $payrollLabel = 'Payroll category'; $payrollRoute = 'payrollcategory'; $payrollItems = $payrollcategories; $payrollHasMultiplier = true; @endphp
@include('shared.payroll-category-department-modals')
<script>$(function(){ $('#example').DataTable({ordering:false}); });</script>
@endsection
