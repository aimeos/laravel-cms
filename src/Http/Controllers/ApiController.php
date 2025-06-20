<?php

namespace Aimeos\Cms\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;


class ApiController extends Controller
{
    use ValidatesRequests;

    /**
     * Sends the e-mail for the contact form.
     *
     * @return \Illuminate\View\View
     */
    public function contact( Request $request ): JsonResponse
    {
        $data = $request->validate( [
            'name' => 'required|string',
            'email' => 'required|email',
            'message' => 'required|string',
        ] );

        return response()->json( [
            'status' => true,
            'message' => 'Success'
        ] );
    }
}
