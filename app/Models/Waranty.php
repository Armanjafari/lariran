<?php

namespace App\Models;

use App\Exceptions\FileHasExistsException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waranty extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
    ];
    public function fulls()
    {
        return $this->hasMany(Full::class);
    }
    public function delete()
    {
        $this->load('fulls');
        $fulls = $this->fulls;
        if ($fulls->isEmpty()) 
        {
            return parent::delete();
        } else {
            throw new FileHasExistsException('a relation exists');
        }
    }
}
