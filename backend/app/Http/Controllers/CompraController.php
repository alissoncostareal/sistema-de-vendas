<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Compra;
use App\Models\CompraItem;
use App\Models\Produto;

class CompraController extends Controller
{

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $compra = Compra::create([
                'fornecedor' => $request->fornecedor
            ]);

            foreach ($request->produtos as $item) {

                $produto = Produto::findOrFail($item['id']);

                $novoCusto = round((
                    ($produto->estoque * $produto->custo_medio) +
                    ($item['quantidade'] * $item['preco_unitario'])
                ) / ($produto->estoque + $item['quantidade']), 2);

                $produto->update([
                    'estoque' => $produto->estoque + $item['quantidade'],
                    'custo_medio' => $novoCusto
                ]);

                CompraItem::create([
                    'compra_id' => $compra->id,
                    'produto_id' => $produto->id,
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco_unitario']
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Compra registrada']);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        try {
            $compras = Compra::with('produtos')->orderBy('created_at', 'desc')->get();
            return response()->json($compras);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar compras: ' . $e->getMessage()], 500);
        }
    }
}
