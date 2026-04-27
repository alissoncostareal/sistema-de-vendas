<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdutoController extends Controller
{
    public function index()
    {
        try {
            $produtos = Produto::select('id', 'nome', 'custo_medio', 'preco_venda', 'estoque')->get();

            return response()->json($produtos);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar produtos: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {

            $validated = $request->validate([
                'nome' => 'required|string|min:3',
                'preco_venda' => 'required|numeric|min:0.01',
                'custo_medio' => 'nullable|numeric',
                'estoque' => 'nullable|integer',
            ]);

            DB::beginTransaction();
            $produto = Produto::create($validated);
            DB::commit();
            return response()->json($produto, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro ao criar produto: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $produto = Produto::findOrFail($id);
            $validated = $request->validate([
                'nome' => 'sometimes|required|string',
                'preco_venda' => 'sometimes|required|numeric',
                'custo' => 'sometimes|required|numeric',
                'estoque' => 'sometimes|required|integer',
            ]);

            $produto->update($validated);
            DB::commit();
            return response()->json($produto);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro ao atualizar produto: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            Produto::destroy($id);
            DB::commit();
            return response()->json(['message' => 'Produto deletado com sucesso']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao deletar produto: ' . $e->getMessage()], 500);
        }
    }
}
