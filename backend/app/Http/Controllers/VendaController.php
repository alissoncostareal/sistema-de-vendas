<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Venda;
use App\Models\VendaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendaController extends Controller
{
    public function store(Request $request)
    {

        try {
            $request->validate([
                'cliente' => 'required|string',
                'produtos' => 'required|array|min:1',
                'produtos.*.id' => 'required|exists:produtos,id',
                'produtos.*.quantidade' => 'required|integer|min:1',
                'produtos.*.preco_unitario' => 'required|numeric|min:0',
            ]);

            DB::beginTransaction();

            $totalVenda = 0;
            $totalLucro = 0;

            $venda = Venda::create([
                'cliente' => $request->cliente,
                'total' => 0,
                'lucro' => 0
            ]);

            foreach ($request->produtos as $item) {
            $produto = Produto::findOrFail($item['id']);

            if ($produto->estoque < $item['quantidade']) {
                throw new \Exception("Estoque insuficiente para o produto: {$produto->nome}");
            }

            $valorTotalItem = $item['preco_unitario'] * $item['quantidade'];
            $lucroUnitario = $item['preco_unitario'] - $produto->custo_medio;
            $lucroTotalItem = $lucroUnitario * $item['quantidade'];

            $totalVenda += $valorTotalItem;
            $totalLucro += $lucroTotalItem;

            VendaItem::create([
                'venda_id' => $venda->id,
                'produto_id' => $produto->id,
                'quantidade' => $item['quantidade'],
                'preco_unitario' => $item['preco_unitario'],
                'lucro' => $lucroTotalItem,
            ]);

            $produto->decrement('estoque', $item['quantidade']);
        }

        $venda->update([
            'total' => $totalVenda,
            'lucro' => $totalLucro
        ]);


            $venda->update([
                'total' => $totalVenda,
                'lucro' => $totalLucro
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Venda realizada com sucesso',
                'total' => $totalVenda,
                'lucro' => $totalLucro
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

public function index()
    {
        try {
            $vendas = Venda::with('produtos')
            ->orderBy('created_at', 'desc')
            ->get();
            return response()->json($vendas);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar vendas: ' . $e->getMessage()], 500);
        }
    }
}
