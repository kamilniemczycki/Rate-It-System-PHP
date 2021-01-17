<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DegustationStoreRequest;
use App\Models\Degustation;
use App\Models\Degustationfeature;
use App\Models\Produktevaluations;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function userReady(Degustation $degustation)
    {
        $user = Auth::user();
        $user->degustation_id = $degustation->id;
        $user->save();
        return response()->json($user->currentDegustation);
    }

    public function isStarted()
    {
        $degustation = Auth::user()->currentDegustation;
        return response()->json(['started' => ($degustation->status === 'in progress')]);
    }

    public function currentProduct()
    {
        return response()->json(Auth::user()->currentDegustation->currentProduct);
    }

    public function rateProduct(Degustationfeature $degustationfeature, $rate)
    {
        $user = Auth::user();
        $product = $user->currentDegustation->currentProduct;
        $produktevaluations = Produktevaluations::createOrUpdate($user, $product, $degustationfeature);

        $produktevaluations->rating = $rate;

        $produktevaluations->save();

        return response()->json($this->getProgressProduct());
    }

    private function getProgressProduct()
    {
        $degustation = Auth::user()->currentDegustation;
        $product = $degustation->currentProduct;
        return [
            'total' => $degustation->members->count(),
            'progress' => Produktevaluations::where('product_id', $product->id)->count()
        ];
    }

}