<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $service = new Service;

        $service->name = trim(strval($request->string('name')));
        $service->price = $request->float('price');

        try {

            $service->save();

            return response('product created !', 201);

        } catch (QueryException $exception) {

            Log::info($exception->getMessage());

            return response('product not created', 403);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show(int $id)
    {
        $service = Service::find($id);

        if ($service instanceof Model) {

            return response([
                'id' => $service->id,
                'name' => $service->name,
                'price' => $service->price
            ]);
        }

        return response('service not found!', 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, int $id)
    {
        $service = Service::find($id);

        if ($service instanceof Model) {

            $service->name = trim(strval($request->input('name')));
            $service->price = $request->float('price');

            try {

                $service->save();

                return response('product updated !');

            } catch (QueryException $exception) {

                Log::info($exception->getMessage());

                return response('product not updated', 403);
            }
        }

        return response('service not found!', 404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
