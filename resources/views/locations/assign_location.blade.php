@extends('layouts.layout')

@section('content')
<section id="dashboard-page" class="wrap">
	<div class="container-fluid">
    <!-- <div class="row">
			<div class="col-sm-12">
				<div class= "page-heading kv-page-hero">
					<h3 class="heading kv-assign-menu-title">ASSIGN MENU</h3>
					<div class="kv-assign-menu-actions">
						<form class="widget ng-pristine ng-valid" method="POST" id="form" action="{{ url('/asign_loaction') }}">
							{{ csrf_field() }}
							<select name="location" class="form-control" aria-label="Select location" onchange="$('#form').submit()">
								<option value="">Select location</option>
							@foreach ($list as $value)
								<option value="{{ $value->id }}" @if(isset($location) && $location == $value->id) selected @endif>{{ $value->location }}</option>
							@endforeach
							</select>
						</form>
						<a class="v-btn v-btn-primary kv-assign-menu" href="{{ url('/dashboard') }}">Menu</a>
            <a class="v-btn v-btn-primary kv-assign-back" href="{{ url('/location') }}"><i class="fa fa-arrow-left"></i> Back</a>
					</div>
				</div>
				<br />
			</div>
		</div> -->
		<div class="row">
			<div class="col-sm-12">
				<div class="page-heading kv-assign-menu-heading kv-page-hero">
					<h3 class="heading kv-assign-menu-title">ASSIGN MENU</h3>
					<div class="kv-assign-menu-actions">
						<form class="widget ng-pristine ng-valid" method="POST" id="form" action="{{ url('/asign_loaction') }}">
							{{ csrf_field() }}
							<select name="location" class="form-control" aria-label="Select location" onchange="$('#form').submit()">
								<option value="">Select location</option>
							@foreach ($list as $value)
								<option value="{{ $value->id }}" @if(isset($location) && $location == $value->id) selected @endif>{{ $value->location }}</option>
							@endforeach
							</select>
						</form>
						<!-- <a class="v-btn v-btn-primary kv-assign-menu" href="{{ url('/dashboard') }}">Menu</a> -->
            <a class="v-btn v-btn-primary pull-right"
              style="margin-right:8px"
              href="javascript:history.back();">
                <i class="fa fa-arrow-left"></i>
                BACK
            </a>
            <!-- <a class="v-btn v-btn-primary kv-assign-back" href="{{ url('/location') }}"><i class="fa fa-arrow-left"></i> Back</a> -->
					</div>
				</div>
			</div>
		</div>
		<div class="row">

			@if(Session::has('message'))
	            <div class="alert alert-success">
	                {{ Session::get('message') }}
	            </div>
	        @endif


			<div class="col-sm-12 ">
				<div class="widget kv-assign-menu-table">
					<div class="widget-content">
            <div class="responsive-table">
              <table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>LOCATION</th>
                            <th>MENU</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($locations as $location)
                                <tr>
                                    <td>{{ $location->location }} </td>
                                    <td>{{ $location->name }}</td>
                                    <td>
                                      <label class="switch" >
                                      <input type="checkbox"
                                        {{ $location->status == 1 ? 'checked' : '' }}
                                        onchange="changeStatus(this, {{ $location->id }})"
                                      >
                                     <!-- <input type="checkbox" @if($location->status==1)checked @endif onclick="changeStatus({{$location->id}})"> -->
                                     <span class="slider round"></span>
                                     </label>
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
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css">
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/moment.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="{{  URL::asset('public/js/bootbox.min.js') }}"></script>


<script type="text/javascript">
 $("#weeklyDatePicker").datetimepicker({
      format: 'YYYY-MM-DD',
	  maxDate: 'now'
   });

   	$(document).ready(function() {
   	    $('#example').DataTable({
   			"aaSorting": []
   		});
   	} );
    // function changeStatus(id){
    //   bootbox.confirm('Are you sure want to update it?',
    //    function(result) {
    //        if(result){
    //                $.ajax({
    //                url : "{{url('asign_loaction_status')}}/"+id,
    //                method : 'get',
    //                success : function(resp){
    //                    bootbox.alert('Status updated successfully.');
    //                   // location.reload()
    //                },
    //                error : function(){
    //                    bootbox.alert('Server error. Please try again.');
    //                }
    //            });
    //        }

    //    });
    // }
    

  function changeStatus(checkbox, id) {
      var newStatus = checkbox.checked;
      var previousStatus = !newStatus;

      // Restore the old checkbox state until confirmation.
      checkbox.checked = previousStatus;

      bootbox.confirm({
          message: 'Are you sure you want to update it?',
          backdrop: false,
          callback: function (result) {
              if (!result) {
                  checkbox.checked = previousStatus;
                  return;
              }

              $.ajax({
                  url: "{{ url('asign_loaction_status') }}/" + id,
                  method: 'GET',

                  success: function (resp) {
                      checkbox.checked = newStatus;

                      // Show success message after the request succeeds.
                      var successBox = bootbox.alert({
                          message: 'Status updated successfully.',
                          backdrop: false,
                          callback: function () {
                              // The alert closes when OK is clicked.
                          }
                      });

                  },

                  error: function () {
                      checkbox.checked = previousStatus;

                      bootbox.alert({
                          message: 'Server error. Please try again.',
                          backdrop: false
                      });
                  }
              });
          }
      });
  }

  $(document).on('click', '.bootbox .modal-footer button', function () {
      var modal = $(this).closest('.bootbox');

      modal.modal('hide');

      setTimeout(function () {
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open');
          $('body').css('padding-right', '');
      }, 300);
  });


</script>
<style>
.switch {
  position: relative;
  display: inline-block;
  width: 45px;
  height: 20px;
  vertical-align: middle;
  margin-top: 8px;
}

.switch input {display:none;}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #A1A6AB;
  -webkit-transition: .4s;
  transition: .4s;

}

.slider:before {
  position: absolute;
  content: "";
 height: 16px;
width: 14px;
left: 2px;
bottom: 2px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #4f4e8f;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}

/* Keep the page clear while the confirmation dialog is open */
.modal-backdrop {
    opacity: 0 !important;
    background-color: transparent !important;
}

/* Keep the confirmation dialog readable */
.bootbox .modal-content {
    opacity: 1 !important;
    filter: none !important;
}

.bootbox .modal-dialog {
    filter: none !important;
}

</style>
@endsection
