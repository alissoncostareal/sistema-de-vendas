<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $fillable = [
        'nome',
        'preco_venda',
        'custo_medio',
        'estoque'
    ];

    protected $appends = ['margem_lucro'];

    public function getMargemLucroAttribute()
    {
        if ($this->custo_medio <= 0) return 0;

        return (($this->preco_venda / $this->custo_medio) - 1) * 100;
    }
}
