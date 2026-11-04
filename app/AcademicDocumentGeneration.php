<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicDocumentGeneration extends Model {
    use SoftDeletes;
    protected $table = 'academic_document_generations';
    protected $dates = array('deleted_at');

    public function scopeForCampus($query, $campusId)
    {
        return $query->where('id_campus', (int) $campusId);
    }

    public function verifiedContent()
    {
        if (!hash_equals($this->empreinte, hash('sha256', $this->contenu))) {
            throw new \RuntimeException('Le contenu archivé ne correspond plus à son empreinte.');
        }
        return $this->contenu;
    }
}
