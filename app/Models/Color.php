<?php

namespace App\Models;

use App\Exceptions\FileHasExistsException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;
    protected $fillable = [
        'title' ,
        'value',
        'option_id',
    ];
    public function option()
    {
        return $this->belongsTo(Option::class);
    }
    public function fulls()
    {
        return $this->hasMany(Full::class);
    }
    public function delete()
    {
        $this->load('fulls');
        if (!is_null($this->fulls->first() ?? null)) 
        {
            return parent::delete();
        } else {
            throw new FileHasExistsException('a relation exists');
        }
    }
}
