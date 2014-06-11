<?php namespace OctoDevel\OctoMail\Models;

use Model;

class Log extends Model
{
    public $table = 'octodevel_octomail_logs';
    /*
     * Relations
     */
    public $belongsTo = [
        'template' => ['OctoDevel\OctoMail\Models\Template', 'foreignKey' => 'template_id']
    ];

    protected $jsonable = ['data'];
    /**
     * The attributes that should be mutated to dates.
     * @var array
     */
    protected $dates = ['sent_at'];

    public function scopeGetLogs($query)
    {
        return $query;
    }
}