<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CV extends Model
{
    protected $table = 'cvs';

    protected $fillable = [
        'name',
        'path',
        'summary',
        'experience_years',
        'skill_score',
        'soft_skills',
        'education_score',
        'relevant_experience',
        'fit_score'
    ];

    public function calculateFitScore(): void
    {
        $this->fit_score = round(
            ($this->skill_score + 
            $this->soft_skills + 
            $this->education_score + 
            $this->relevant_experience) / 4
        );
        $this->save();
    }
}