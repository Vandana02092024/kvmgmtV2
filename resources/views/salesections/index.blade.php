@extends('layouts.layout')

@section('content')


<style>
    #example_filter {
        display: none;
    }

    /* Keep the Show Entries and date filters on one line */
    .kv-sales-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: nowrap;
        gap: 15px;
        margin: 10px 0 10px 0;
    }

    .kv-sales-toolbar .kv-sales-length {
        flex: 0 0 auto;
        width: auto;
    }

    .kv-sales-toolbar .kv-sales-filters {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        flex: 1;
        min-width: 0;
    }

    .kv-sales-toolbar .kv-sales-filters input {
        height: 40px;
        margin: 0;
        box-sizing: border-box;
    }

    #from_date,
    #to_date {
        width: 190px;
        min-width: 150px;
    }

    #search {
        width: 180px;
        min-width: 120px;
    }

    #searchButton {
        cursor: pointer;
        font-size: 18px;
        padding: 5px;
    }

    /* Keep DataTables Show Entries on one line */
    #example_length {
        white-space: nowrap;
        margin: 0;
    }

    #example_length label {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        white-space: nowrap;
        font-weight: normal;
    }

    #example_length select {
        display: inline-block;
        width: auto;
        min-width: 90px;
        margin: 0;
    }

    .pagination {
        float: right;
        margin: -25px 0 !important;
    }

    @media (max-width: 900px) {
        .kv-sales-toolbar {
            flex-wrap: wrap;
        }

        .kv-sales-toolbar .kv-sales-filters {
            flex-wrap: wrap;
            justify-content: flex-start;
        }
    }
</style>


<section id="dashboard-page" class="wrap">
    <div class="container-fluid">

        <div class="row">
            <div class="col-sm-12">
                <div class="page-heading kv-page-hero kv-sales-hero">
                    <h3 class="heading">
                        <span class="kv-hero-title">
                            <i class="fa fa-usd"></i>
                            <span>Weekly Sales Reports</span>
                        </span>

                        <a class="v-btn v-btn-primary pull-right"
                           href="{{ url('/salesection/create') }}">
                            <i class="fa fa-plus"></i> New Week Report
                        </a>
                    </h3>
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
					<div class="row kv-sales-toolbar">

						<div class="kv-sales-length">
							<!-- DataTables will place Show Entries here -->
						</div>

						<div class="kv-sales-filters">
							<input
								type="date"
								title="From Date"
								name="from_date"
								id="from_date"
								class="form-control"
								value="{{ empty($from_date) ? '' : $from_date }}"
							/>

							<input
								type="date"
								title="To Date"
								name="to_date"
								id="to_date"
								class="form-control"
								value="{{ empty($to_date) ? '' : $to_date }}"
							/>

							<input
								type="search"
								name="search"
								id="search"
								class="form-control"
								placeholder="WEEK #"
							/>

							<label style="margin: 0;">
								<i class="fa fa-search" id="searchButton"></i>
							</label>
						</div>

					</div>
                    <div class="widget-content">
                        <div class="responsive-table">

                            <table id="example"
                                   class="table table-bordered"
                                   cellspacing="0"
                                   width="100%">

                                <thead>
                                    <tr>
                                        <th>CALENDAR WEEK</th>
                                        <th>WEEK #</th>
                                        <th>START DATE</th>
                                        <th>END DATE</th>
                                        <th>Gross Sales</th>
                                        <th>Net Sales</th>
                                        <th>Labor %</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($data as $dat)

                                        @php
                                            $week = getWeekByDate($dat->dataDate);

                                            $weekdate = getStartAndEndDatebyweek(
                                                $week,
                                                date('Y-m-d', strtotime($dat->dataDate))
                                            );

                                            $tot_sale = getLaberRate(
                                                $dat->location_id,
                                                $dat->storeNo
                                            );

                                            $range = $dat->storeNo;

                                            $gsale = number_format($dat->amount, 2);

                                            $sale_tax = getSaleTax(
                                                $dat->location_id,
                                                $weekdate['start_date'],
                                                $weekdate['end_date']
                                            );

                                            $netsale = getNetSale(
                                                $dat->location_id,
                                                $weekdate['start_date'],
                                                $weekdate['end_date']
                                            ) - $sale_tax;
                                        @endphp

                                        <tr>
                                            <td>{{ $week }}</td>

                                            <td>{{ $dat->storeNo }}</td>

                                            <td>
                                                {{ date('m-d-Y', strtotime($weekdate['start_date'])) }}
                                            </td>

                                            <td>
                                                {{ date('m-d-Y', strtotime($weekdate['end_date'])) }}
                                            </td>

                                            <td>{{ $gsale }}</td>

                                            <td>{{ number_format($netsale, 2) }}</td>

                                            <td>
                                                @if($netsale > 0 && $tot_sale > 0)
                                                    {{ number_format(($tot_sale / $netsale) * 100, 2) }}
                                                @else
                                                    0.00
                                                @endif
                                            </td>

                                            <td>
                                                {{ link_to_route(
                                                    'salesection.edit',
                                                    '',
                                                    ['record-'.$dat->id],
                                                    [
                                                        'class' => 'fa fa-pencil',
                                                        'data-toggle' => 'tooltip',
                                                        'data-placement' => 'left',
                                                        'title' => 'Edit'
                                                    ]
                                                ) }}

                                                &nbsp;

                                                {{ link_to_route(
                                                    'salesection.printData',
                                                    '',
                                                    [$dat->storeNo],
                                                    [
                                                        'class' => 'fa fa-print',
                                                        'data-toggle' => 'tooltip',
                                                        'data-placement' => 'left',
                                                        'title' => 'Print'
                                                    ]
                                                ) }}

                                                &nbsp;

                                                {{ link_to_route(
                                                    'labor_report',
                                                    '',
                                                    [$range],
                                                    [
                                                        'class' => 'fa fa-file',
                                                        'data-toggle' => 'tooltip',
                                                        'data-placement' => 'left',
                                                        'title' => 'Labor Report'
                                                    ]
                                                ) }}
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
    $(document).ready(function () {

        // // Initialize DataTable
        // var table = $('#example').DataTable({
        //     aaSorting: []
        // });

		var table = $('#example').DataTable({
			"aaSorting": [],
			"dom": 'lrtip'
		});

		$('#example_length').appendTo('.kv-sales-length');

        // Move "Show entries" into the left side of the toolbar
        // $('#example_length').appendTo('#sales-length-container');

        // Search using week number and date filters
        $('#searchButton').on('click', function () {

            var searchValue = $('#search').val();
            var from_date = $('#from_date').val();
            var to_date = $('#to_date').val();

            $.ajax({
                type: 'GET',
                url: '{!! route('salesection.filter.data') !!}',
                data: {
                    search: searchValue,
                    from_date: from_date,
                    to_date: to_date
                },
                success: function () {
                    window.location.href =
                        "{!! route('salesection.filter.data') !!}" +
                        "?search=" + encodeURIComponent(searchValue) +
                        "&from=" + encodeURIComponent(from_date) +
                        "&to=" + encodeURIComponent(to_date);
                },
                error: function () {
                    alert('Unable to filter sales reports. Please try again.');
                }
            });
        });

        // Search when Enter is pressed
        $('#search').on('keypress', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                $('#searchButton').click();
            }
        });

        // Change number of entries per page
        $(document).on('change', 'select[name="example_length"]', function () {

            var count = this.value;

            $.ajax({
                type: 'GET',
                url: '{!! route('salesection.filter.data') !!}',
                data: {
                    count: count
                },
                success: function () {
                    window.location.href =
                        "{!! route('salesection.filter.data') !!}/" +
                        encodeURIComponent(count);
                },
                error: function () {
                    alert('Unable to change the number of entries.');
                }
            });
        });

    });
</script>

@endsection