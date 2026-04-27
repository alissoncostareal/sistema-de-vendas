<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    protected $fillable = ['cliente', 'total', 'lucro'];

    public function itens()
    {
        return $this->hasMany(VendaItem::class);
    }
    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'venda_items')
                    ->withPivot('quantidade', 'preco_unitario', 'lucro');
    }
}
