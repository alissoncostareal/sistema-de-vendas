<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraItem extends Model
{
    protected $fillable = ['compra_id', 'produto_id', 'quantidade', 'preco_unitario'];

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
