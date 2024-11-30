<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProposalRequest;
use App\Models\Proposal;
use App\Models\Travel;
use App\Models\TravelProposal;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProposalController extends Controller
{
    public function proposal(ProposalRequest $request, $client_travel_id, $travel_id)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();

            $proposal = Proposal::create([
                'accepted' => 0,
                'date_arrival' => $validated['data_chegada'],
                'date_output' => $validated['data_saida'],
                'price' => $validated['preco'],
                'client_travel_id' => $client_travel_id,
            ]);

            TravelProposal::create([
                'proposal_id' => $proposal->id_proposal,
                'travel_id' => $travel_id,
            ]);
            DB::commit();
            return response()->json([
                'message' => 'Proposta enviada com sucesso!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error has occurred',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getUserProposals($user_id)
    {
        try {
            $proposals = DB::table('proposal')
                ->join('travel_proposal', 'proposal.id_proposal', '=', 'travel_proposal.proposal_id')
                ->join('travel', 'travel_proposal.travel_id', '=', 'travel.id_travel')
                ->join('output', 'travel.output_id', '=', 'output.id_output')
                ->join('arrival', 'travel.arrival_id', '=', 'arrival.id_arrival')
                ->join('vehicles', 'travel.vehicle_id', '=', 'vehicles.id_vehicle')
                ->where('travel.user_id', $user_id)
                ->select(
                    'proposal.*',
                    'output.city as output_city', 'output.state as output_state', 'output.address as output_address',
                    'arrival.city as arrival_city', 'arrival.state as arrival_state', 'arrival.address as arrival_address',
                    'vehicles.plate as vehicle_plate', 'vehicles.vehicle_type as vehicle_type', 'vehicles.brand as vehicle_brand', 'vehicles.model as vehicle_model', 'vehicles.model_year as vehicle_model_year'
                )
                ->get();

            $acceptedProposals = $proposals->filter(function ($proposal) {
                return $proposal->accepted;
            });

            $notAcceptedProposals = $proposals->filter(function ($proposal) {
                return !$proposal->accepted;
            });

            return response()->json([
                'accepted_proposals' => $acceptedProposals,
                'not_accepted_proposals' => $notAcceptedProposals,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error has occurred',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getTravelProposal(Request $request, $travel_id)
    {
        try {
            $proposals = DB::table('proposal')
                ->join('travel_proposal', 'proposal.id_proposal', '=', 'travel_proposal.proposal_id')
                ->join('travel', 'travel_proposal.travel_id', '=', 'travel.id_travel')
                ->join('output', 'travel.output_id', '=', 'output.id_output')
                ->join('arrival', 'travel.arrival_id', '=', 'arrival.id_arrival')
                ->join('vehicles', 'travel.vehicle_id', '=', 'vehicles.id_vehicle')
                ->where('proposal.client_travel_id', $travel_id)
                ->select(
                    'proposal.*',
                    'output.city as output_city', 'output.state as output_state', 'output.address as output_address',
                    'arrival.city as arrival_city', 'arrival.state as arrival_state', 'arrival.address as arrival_address',
                    'vehicles.plate as vehicle_plate', 'vehicles.vehicle_type as vehicle_type', 'vehicles.brand as vehicle_brand', 'vehicles.model as vehicle_model', 'vehicles.model_year as vehicle_model_year',
                    'travel.user_id as carrier_user_id'
                )
                ->get();

            $bearerToken = $request->bearerToken();
            $client = new Client();

            $proposals->map(function ($proposal) use ($client, $bearerToken) {
                if (isset($proposal->carrier_user_id)) {
                    try {
                        $response = $client->request('GET', 'http://54.198.88.58:82/api/user/' . $proposal->carrier_user_id, [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $bearerToken,
                                'Accept' => 'application/json',
                            ],
                        ]);
                        if ($response->getStatusCode() == 200) {
                            $user = json_decode($response->getBody(), true);
                            $proposal->user = $user;
                        } else {
                            $proposal->user = ['error' => 'Usuário não encontrado'];
                        }
                    } catch (\Exception $e) {
                        $proposal->user = ['error' => 'Falha ao buscar o usuário'];
                        \Log::error("Erro ao carregar usuário: {$e->getMessage()}");
                    }
                } else {
                    $proposal->user = null;
                }
            });

            return response()->json([
                'proposals' => $proposals,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error has occurred',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getCarrierProposal(Request $request, $travel_id)
    {
        try {
            $travelproposals = TravelProposal::where('travel_id', '=', $travel_id)
                ->with(
                    'carrier',
                    'proposal',
                    'carrier.vehicle',
                    'carrier.arrival',
                    'carrier.output')
                ->get();


            $bearerToken = $request->bearerToken();
            $travelproposals->map(function ($proposal) use ($bearerToken) {
                if (isset($proposal->carrier->user_id)) {
                    try {
                        $client = new Client();
                        $response = $client->request('GET', 'http://54.198.88.58:82/api/user/' . $proposal->carrier->user_id, [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $bearerToken,
                                'Accept' => 'application/json',
                            ],
                        ]);
                        if ($response->getStatusCode() == 200) {
                            $proposal->carrier->user = json_decode($response->getBody(), true);
                        } else {
                            $proposal->carrier->user = ['error' => 'Usuário não encontrado'];
                        }
                    } catch (\Exception $e) {
                        $proposal->carrier->user = ['error' => 'Falha ao buscar o usuário'];
                        \Log::error("Erro ao carregar usuário: {$e->getMessage()}");
                    }
                } else {
                    $proposal->carrier->user = null;
                }

                return $proposal;
            });

            return response()->json([
                'travel_carrier' => $travelproposals,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error has occurred',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getClientProposal(Request $request, $client_travel_id)
    {
        try {
            $travelclient = Proposal::where('client_travel_id', '=', $client_travel_id)
                ->with(
                    'travel',
                    'travel.vehicle',
                    'travel.arrival',
                    'travel.output')
                ->get();

            $bearerToken = $request->bearerToken();
            $travelclient->map(function ($proposal) use ($bearerToken) {
                if (isset($proposal->carrier->user_id)) {
                    try {
                        $client = new Client();
                        $response = $client->request('GET', 'http://54.198.88.58:82/api/user/' . $proposal->carrier->user_id, [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $bearerToken,
                                'Accept' => 'application/json',
                            ],
                        ]);
                        if ($response->getStatusCode() == 200) {
                            $proposal->carrier->user = json_decode($response->getBody(), true);
                        } else {
                            $proposal->carrier->user = ['error' => 'Usuário não encontrado'];
                        }
                    } catch (\Exception $e) {
                        $proposal->carrier->user = ['error' => 'Falha ao buscar o usuário'];
                        \Log::error("Erro ao carregar usuário: {$e->getMessage()}");
                    }
                } else {
                    $proposal->carrier->user = null;
                }

                return $proposal;
            });

            return response()->json([
                'travel_client' => $travelclient,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error has occurred',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function acceptProposal($proposal_id)
    {
        try {

            DB::beginTransaction();

            $proposal = Proposal::findOrFail($proposal_id);

            $proposal->update(['accepted' => true]);

            Proposal::where('client_travel_id', $proposal->client_travel_id)
                ->where('id_proposal', '!=', $proposal_id)
                ->delete();

            DB::commit();

            return response()->json([
                'message' => 'Proposta aceita e outras propostas relacionadas removidas com sucesso.',
                'proposal' => $proposal,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error has occurred',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function deleteProposal($proposal_id)
    {
        try {
            DB::beginTransaction();

            $travelProposal = Proposal::where('id_proposal', '=', $proposal_id)->firstOrFail();
            $travelProposal->delete();

            DB::commit();

            return response()->json([
                'message' => 'Proposta Apagada!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error has occurred',
                'error' => $e->getMessage()
            ]);
        }
    }

}
