<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TravelRequest;
use App\Models\Arrival;
use App\Models\Output;
use App\Models\Travel;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TravelController extends Controller
{
    public function getTravel($travel_id)
    {
        try {
            $travel = Travel::where('id_travel', $travel_id)
                ->with(['arrival', 'output', 'vehicle'])
                ->first();

            return response()->json([
                'travel' => $travel
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error has occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllTravels($user_id)
    {
        try {
            $travel = Travel::where('user_id', $user_id)
                ->with(['arrival', 'output', 'vehicle'])->get();

            return response()->json([
                'travel' => $travel
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error has occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(TravelRequest $request, $user_id)
    {
        try {
            $validated = $request->validated();
            DB::beginTransaction();

            $output = Output::create([
                'city' => $validated['saida']['cidade'],
                'state' => $validated['saida']['estado'],
                'address' => $validated['saida']['endereco'],
                'latitude' => $validated['saida']['latitude'],
                'longitude' => $validated['saida']['longitude'],
            ]);

            $arrival = Arrival::create([
                'city' => $validated['chegada']['cidade'],
                'state' => $validated['chegada']['estado'],
                'address' => $validated['chegada']['endereco'],
                'latitude' => $validated['chegada']['latitude'],
                'longitude' => $validated['chegada']['longitude'],
            ]);

            $vehicle = Vehicle::create([
                'plate' => $validated['veiculo']['placa'],
                'vehicle_type' => $validated['veiculo']['tipo_veiculo'],
                'brand' => $validated['veiculo']['marca'],
                'model' => $validated['veiculo']['modelo'],
                'model_year' => $validated['veiculo']['ano_modelo'],
                'year_manufacture' => $validated['veiculo']['ano_fabricacao'],
            ]);

            Travel::create([
                'user_id' => $user_id,
                'arrival_id' => $arrival->id_arrival,
                'output_id' => $output->id_output,
                'vehicle_id' => $vehicle->id_vehicle,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Viagem cadastrada com sucesso buscando pacotes compativeis.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error has occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteTravel($travel_id)
    {
        try {
            DB::beginTransaction();
            $travel = Travel::where('id_travel', '=', $travel_id)->first();

            if ($travel) {
                $travel->arrival()->delete();
                $travel->output()->delete();
                $travel->vehicle()->delete();

                $travel->delete();
                DB::commit();
                return response()->json(['message' => 'Viagem apagada com sucesso!']);
            } else {
                DB::rollBack();
                return response()->json(['message' => 'Viagem não encontrada!']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error has occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
