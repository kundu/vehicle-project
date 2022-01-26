<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\VehicleDetailResource;
use App\Http\Resources\VehicleDetailWithDeletedDataResource;
use App\Models\VehicleDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class VehicleController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return App\Http\Controllers\API\BaseController method
     */
    public function index(){
        $vehicleDetails = VehicleDetail::with('createdBy')->orderBy('id', 'desc')->get();
        return $this->sendResponse(VehicleDetailResource::collection($vehicleDetails), 'All vehicle data');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return App\Http\Controllers\API\BaseController method
     */
    public function store(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'manufacturer'       => 'required|string|max:150',
                'model'              => 'required|string|max:150',
                'fin'                => 'required|string|max:150',
                'first_registration' => 'nullable|string|max:150',
                'kilometers_stand'   => 'nullable|string|max:150',
            ]);

            if($validator->fails()){
                return $this->sendError('Input validation error', $validator->errors());
            }

            $vehicleDetail                     = new VehicleDetail();
            $vehicleDetail->manufacturer       = $request->manufacturer;
            $vehicleDetail->model              = $request->model;
            $vehicleDetail->fin                = $request->fin;
            $vehicleDetail->first_registration = $request->first_registration;
            $vehicleDetail->kilometers_stand   = $request->kilometers_stand;
            $vehicleDetail->created_by         = auth()->user()->id;
            $vehicleDetail->save();

            return $this->sendResponse(new VehicleDetailResource($vehicleDetail), 'Vehicle data is created successfully.');
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->sendError('Internal Server Error. Please inform admin to check log file.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return App\Http\Controllers\API\BaseController method
     */
    public function show($id){
        $vehicleDetail = VehicleDetail::with('createdBy')->find($id);
        if(!$vehicleDetail)
            return $this->sendError('Vehicle not found');

        return $this->sendResponse(new VehicleDetailResource($vehicleDetail), 'Vehicle details');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return App\Http\Controllers\API\BaseController method
     */
    public function update(Request $request, $id){
        try {
            $validator = Validator::make($request->all(), [
                'manufacturer'       => 'required|string|max:150',
                'model'              => 'required|string|max:150',
                'fin'                => 'required|string|max:150',
                'first_registration' => 'nullable|string|max:150',
                'kilometers_stand'   => 'nullable|string|max:150',
            ]);

            if($validator->fails()){
                return $this->sendError('Input validation error', $validator->errors());
            }

            $vehicleDetail = VehicleDetail::find($id);
            if(!$vehicleDetail)
                return $this->sendError('Vehicle not found');

            $vehicleDetail->manufacturer = $request->manufacturer;
            $vehicleDetail->model        = $request->model;
            $vehicleDetail->fin          = $request->fin;

            if($request->first_registration)
                $vehicleDetail->first_registration = $request->first_registration;
            if($request->kilometers_stand)
                $vehicleDetail->kilometers_stand = $request->kilometers_stand;

            $vehicleDetail->save();

            return $this->sendResponse(new VehicleDetailResource($vehicleDetail), 'Vehicle data is updated successfully.');
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->sendError('Internal Server Error. Please inform admin to check log file.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return App\Http\Controllers\API\BaseController method
     */
    public function destroy($id){
        try{
            $vehicleDetail = VehicleDetail::find($id);
            if(!$vehicleDetail)
                return $this->sendError('Vehicle not found');

            DB::beginTransaction();
            $vehicleDetail->last_edited_by = auth()->user()->id;
            $vehicleDetail->save();
            $vehicleDetail->delete();
            DB::commit();
            return $this->sendResponse([], 'Vehicle is deleted successfully');
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex);
            return $this->sendError('Internal Server Error. Please inform admin to check log file.');
        }
    }

    /**
     * Display a listing of the deleted resource.
     *
     * @return App\Http\Controllers\API\BaseController method
     */
    public function deletedData(){
        $vehicleDeletedData = VehicleDetail::with('lastEditedBy')->onlyTrashed()->get();
        return $this->sendResponse(VehicleDetailWithDeletedDataResource::collection($vehicleDeletedData), 'All deleted vehicle data');
    }
}
