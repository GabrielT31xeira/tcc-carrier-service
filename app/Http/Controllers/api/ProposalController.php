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
        $proposals = DB::table('proposal')
            ->join('travel_proposal', 'proposal.id_proposal', '=', 'travel_proposal.proposal_id')
            ->join('travel', 'travel_proposal.travel_id', '=', 'travel.id_travel')
            ->where('travel.user_id', $user_id)
            ->select('proposal.*')
            ->get();

        return response()->json($proposals);
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

}
