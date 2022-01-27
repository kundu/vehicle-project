<?php

namespace App\Service;

use App\Http\Controllers\API\BaseController;
use App\Http\Resources\VehicleDetailResource;
use App\Http\Resources\VehicleDetailWithDeletedDataResource;
use App\Models\VehicleDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VehicleService implements VehicleInterface
{
    private $baseController;

    function __construct() {
        $this->baseController = new BaseController();
    }
    public function getVehicles(){
        $vehicleDetails = VehicleDetail::with('createdBy')->orderBy('id', 'desc')->get();
        return $this->baseController->sendResponse(VehicleDetailResource::collection($vehicleDetails), 'All vehicle data');
    }

    public function createVehicle(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'manufacturer'       => 'required|string|max:150',
                'model'              => 'required|string|max:150',
                'fin'                => 'required|string|max:150',
                'first_registration' => 'nullable|string|max:150',
                'kilometers_stand'   => 'nullable|string|max:150',
            ]);

            if($validator->fails()){
                return $this->baseController->sendError('Input validation error', $validator->errors());
            }

            $vehicleDetail                     = new VehicleDetail();
            $vehicleDetail->manufacturer       = $request->manufacturer;
            $vehicleDetail->model              = $request->model;
            $vehicleDetail->fin                = $request->fin;
            $vehicleDetail->first_registration = $request->first_registration;
            $vehicleDetail->kilometers_stand   = $request->kilometers_stand;
            $vehicleDetail->created_by         = auth()->user()->id;
            $vehicleDetail->save();

            return $this->baseController->sendResponse(new VehicleDetailResource($vehicleDetail), 'Vehicle data is created successfully.');
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
    }

    public function showVehicle(int $id){
        $vehicleDetail = VehicleDetail::with('createdBy')->find($id);
        if(!$vehicleDetail)
            return $this->baseController->sendError('Vehicle not found');

        return $this->baseController->sendResponse(new VehicleDetailResource($vehicleDetail), 'Vehicle details');
    }

    public function updateVehicle(Request $request, int $id){
        try {
            $validator = Validator::make($request->all(), [
                'manufacturer'       => 'required|string|max:150',
                'model'              => 'required|string|max:150',
                'fin'                => 'required|string|max:150',
                'first_registration' => 'nullable|string|max:150',
                'kilometers_stand'   => 'nullable|string|max:150',
            ]);

            if($validator->fails()){
                return $this->baseController->sendError('Input validation error', $validator->errors());
            }

            $vehicleDetail = VehicleDetail::find($id);
            if(!$vehicleDetail)
                return $this->baseController->sendError('Vehicle not found');

            $vehicleDetail->manufacturer = $request->manufacturer;
            $vehicleDetail->model        = $request->model;
            $vehicleDetail->fin          = $request->fin;

            if($request->first_registration)
                $vehicleDetail->first_registration = $request->first_registration;
            if($request->kilometers_stand)
                $vehicleDetail->kilometers_stand = $request->kilometers_stand;

            $vehicleDetail->save();

            return $this->baseController->sendResponse(new VehicleDetailResource($vehicleDetail), 'Vehicle data is updated successfully.');
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
    }

    public function deleteVehicle(int $id){
        try{
            $vehicleDetail = VehicleDetail::find($id);
            if(!$vehicleDetail)
                return $this->baseController->sendError('Vehicle not found');

            DB::beginTransaction();
            $vehicleDetail->last_edited_by = auth()->user()->id;
            $vehicleDetail->save();
            $vehicleDetail->delete();
            DB::commit();
            return $this->baseController->sendResponse([], 'Vehicle is deleted successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new Exception($th);
        }
    }

    public function deletedVehicleList(){
        $vehicleDeletedData = VehicleDetail::with('lastEditedBy')->onlyTrashed()->get();
        return $this->baseController->sendResponse(VehicleDetailWithDeletedDataResource::collection($vehicleDeletedData), 'All deleted vehicle data');
    }

}
