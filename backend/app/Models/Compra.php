<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $fillable = [
        'fornecedor'
    ];
    protected $appends = ['valor_total'];

    public function produtos()
    {

        return $this->belongsToMany(Produto::class, 'compra_items', 'compra_id', 'produto_id')
                    ->withPivot('quantidade', 'preco_unitario');
    }

    public function getValorTotalAttribute()
    {
        return $this->produtos->sum(function($produto) {
            return $produto->pivot->quantidade * $produto->pivot->preco_unitario;
        });
    }
}
