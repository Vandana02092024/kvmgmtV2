<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Location;
use App\Menu;
use App\MenuLocation;
use App\Http\Requests;
use App\Http\Requests\LocationRequest;
use Illuminate\Support\Facades\DB;


class LocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		return $this->locationIndexView();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
		return $this->locationIndexView(['openAddLocationModal' => true]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(LocationRequest $request)
    {
        $result = Location::create($request->all());
        $this->menu_loaction($result->id);
        return redirect()->route('location.index')->with('message','Location has been added successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Location $location)
    {
		return $this->locationIndexView(['openEditLocationId' => $location->id]);
    }

	private function locationIndexView(array $modalState = [])
	{
		$locations = Location::orderBy('rank')->get();
		$salesReportCounts = DB::table('salesections')
			->select('location_id', DB::raw('COUNT(id) as count'))
			->where('nameDep', '=', 'sale')
			->groupBy('location_id')
			->pluck('count', 'location_id');

		return view('locations.index', array_merge(compact('locations', 'salesReportCounts'), $modalState));
	}

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, Location $location)
    // {
    //     $location->update($request->all());
    //     return redirect()->route('location.index')->with('message','Location has been updated successfully!');
    // }

      public function update(Request $request, Location $location)
      {
          $validated = $request->validate([
              'location_code' => 'required|string|max:255',
              'location'      => 'required|string|max:255',
              'startdate'     => 'nullable|date',
              'rank'          => 'nullable',
              'rate'          => 'nullable',
              'entity_name'   => 'nullable|string|max:255',
          ]);

          // Prevent changing the start date after sales data exists.
          $salesReportCount = DB::table('salesections')
              ->where('location_id', $location->id)
              ->where('nameDep', 'sale')
              ->count();

          if ($salesReportCount > 0) {
              unset($validated['startdate']);
          }

          try {
              $location->fill($validated);
              $location->save();

              return redirect()
                  ->route('location.index')
                  ->with('message', 'Location has been updated successfully!');
          } catch (\Exception $e) {
              report($e);

              return redirect()
                  ->route('location.index')
                  ->withInput()
                  ->with('error', 'Location could not be updated. Please check the application log.');
          }
      }

    public function archive($id , Request $request)
    {

		$statusCode = $request->st;


		if($statusCode == 1) {
		Location::where('id', '=', $id)->update(['status'   => 0]);
		return redirect()->route('location.index')->with('message','Archived successfully!');

		} else {
		Location::where('id', '=', $id)->update(['status'   => 1]);
		return redirect()->route('location.index')->with('message','Unarchived successfully!');
		}
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy(Location $location)
    {
        try {
          $location->delete();
          return redirect()->route('location.index')->with('message','Location deleted successfully!!');
        }
        catch (\Exception $e) {
            return redirect()->route('location.index')->with('message','Location can not be deleted as it is already in use by some user!!');
        }

    }

    public function menu_loaction($location_id){
        $menu = Menu::where('status',1)->get();
        foreach ($menu as  $value) {
          $locations = Location::where('status',1)->where('id',$location_id)->get();
          foreach ($locations as  $location) {
            MenuLocation::Create([
              'location_id'=>$location->id,
              'menu_id'=>$value->id,
              'status'=>1,
            ]);
          }
        }
    }

    public function asign_loaction(Request $request,$id=''){

     $location = MenuLocation::select(['menu_locations.id','menu_locations.status','menu_locations.menu_id','menu_locations.location_id','menus.name','locations.location'])->join('menus', 'menus.id', '=', 'menu_locations.menu_id')
      ->join('locations', 'locations.id', '=', 'menu_locations.location_id');
      if($request->location!=''){
        $location->where('menu_locations.location_id',$request->location);
        $data['location'] = $request->location;
      }elseif ($id!='') {
          $location->where('menu_locations.location_id',$id);
          $data['location'] = $id;
      }
        $location->where('locations.status','1');
        $location->where('menus.status','1');
        $data['locations']=   $location->get();
        $data['list'] = Location::where('status','1')->get();
        return view('locations.assign_location',$data);
    }


    public function asign_loaction_status($id){
      $location = MenuLocation::where('id', '=', $id)->first();

      $status = ($location->status==1)?0:1;
    return  MenuLocation::where('id', '=', $id)->update(['status'   => $status]);
    }
}
