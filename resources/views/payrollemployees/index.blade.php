@extends('layouts.layout')
@section('content')
<style>ul.inline-list>li{display:inline-block;vertical-align:middle}ul.inline-list{margin-bottom:10px}</style>
<section id="dashboard-page" class="wrap">
    <div class="container-fluid">
        <div class="row">
			<div class="col-sm-12">
				<div class= "page-heading kv-page-hero">
					<h3 class="heading">PAYROLL EMPLOYEES
                        <a class="v-btn v-btn-primary pull-right"
                            style="margin-right:8px"
                            href="javascript:history.back();">
                            <i class="fa fa-arrow-left"></i>
                            BACK
                        </a>
                        <!-- <a class="v-btn v-btn-primary pull-right bg-light" href="{{ url('/menu') }}">BACK</a> -->
                        <!-- <a class="v-btn v-btn-primary pull-right" href="{{ url('/dashboard') }}">DASHBOARD</a> -->
                        <a class="v-btn v-btn-primary payroll-add-trigger pull-right" href="{{ url('/payrollemployee/create') }}">NEW PAYROLL EMPLOYEE</a>
                    </h3>
				</div>
				<br />
			</div>
		</div>
    <div class="row"><div class="col-sm-12"><ul class="inline-list pull-right"><li>SELECT OPTION : </li><li><select class="pull-right" name="archiveValue" id="archiveValue"><option value="all">All</option><option value="archive">Archive</option><option value="unarchive">Unarchive</option></select></li></ul></div></div>
    @if(Session::has('message'))<div class="alert alert-success">{{ Session::get('message') }}</div>@endif
    <div class="row"><div class="col-sm-12 col-xs-12"><div class="widget"><div class="widget-content"><div class="responsive-table" id="showData">
        <table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%"><thead><tr><th>PAYROLL EMPLOYEE</th><th>ACTION</th></tr></thead><tbody>
            @foreach($payrollemployees as $payrollemp)<tr><td>{{ $payrollemp->name }}</td><td>
                {!! Form::open(array('route' => ['payrollemployee.destroy', $payrollemp->id], 'method'=>'DELETE', 'class' => 'delete')) !!}
                {{ link_to_route('payrollemployee.edit','',[$payrollemp->id],['class' => 'fa fa-pencil','data-toggle'=>'tooltip','data-placement'=>'left','title'=>'Edit']) }} &nbsp;|&nbsp;
                {{ link_to_route('payrollemployee.archive', $payrollemp->status == 1 ? 'Archive' : 'Unarchive', ['id' => $payrollemp->id, 'st' => $payrollemp->status]) }}
                {!! Form::close() !!}
            </td></tr>@endforeach
        </tbody></table>
    </div></div></div></div></div>
</div></section>
@include('shared.payroll-employee-modals')
<script>
$(function(){ $('#example').DataTable({ordering:false}); });
$('.delete').on('submit',function(){return confirm('Do you want to delete this user?');});
$('#archiveValue').on('change',function(){var selectedOption=$(this).val();$.ajax({type:'POST',dataType:'html',url:"{{ url('/') }}/viewAllData",data:{selectedOption:selectedOption},success:function(data){$('#showData').html(data);}});});
</script>
@endsection
