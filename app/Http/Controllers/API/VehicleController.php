<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Service\VehicleService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VehicleController extends BaseController
{
    private $vehicleService;

    function __construct() {
        $this->vehicleService = new VehicleService();
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index(){
        return ($this->vehicleService->getVehicles());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request){
        try {
            return ($this->vehicleService->createVehicle($request));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->sendError('Internal Server Error. Please inform admin to check log file.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id){
        return ($this->vehicleService->showVehicle($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(Request $request, $id){
        try {
            return ($this->vehicleService->updateVehicle($request, $id));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->sendError('Internal Server Error. Please inform admin to check log file.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy($id){
        try{
            return ($this->vehicleService->deleteVehicle($id));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->sendError('Internal Server Error. Please inform admin to check log file.');
        }
    }

    /**
     * Display a listing of the deleted resource.
     *
     */
    public function deletedVehicleList(){
        return ($this->vehicleService->deletedVehicleList());
    }
}
