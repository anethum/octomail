<?php namespace OctoDevel\OctoMail\Models;

use \DB;
use Model;
use OctoDevel\OctoMail\Models\Files;
use OctoDevel\OctoMail\Models\Recipient;

class Template extends Model
{
    public $table = 'octodevel_octomail_templates';

    public $belongsToMany = [
        'recipents' => ['OctoDevel\OctoMail\Models\Recipient','table' => 'octodevel_octomail_tem_rec']
    ];

    /*
     * Validation
     */
    public $rules = [
        'title' => 'required',
        'slug' => ['required', 'regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i'],
        'lang' => 'required',
        'content_html' => 'required',
        'fields' => '',
        'subject' => 'required',
        'sender_name' => 'required',
        'sender_email' => ['required', 'email'],
        'multiple_recipients' => '',
        'recipient_name' => '',
        'recipient_email' => 'email',
        'confirmation_text' => '',
        'autoresponse' => '',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */

    protected $dates = ['created_at'];

    public function beforeSave()
    {
        $title = explode(' ', $this->title);
        $last = array_pop($title);

        if(is_numeric($last))
        {
            $count = count($title);
        }

        if(DB::table($this->table)->where('title', '=', $this->title)->count())
        {
            throw new \Exception(sprintf('Slug ja existe: ' . $last . ' - ' . $all));
        }

        $this->filename = 'view-' . $this->slug . '.htm';
        $this->fields = preg_replace('/\s/', '', $this->fields);
        $this->content = strip_tags(preg_replace("/{{\s*message\s*}}/i", "{{ body }}", $this->content_html));
        $this->content_html = preg_replace("/{{\s*message\s*}}/i", "{{ body }}", $this->content_html);
    }

    public function afterSave()
    {
        Files::write_view($this);
    }

    public function beforeUpdate()
    {
        Files::delete_view($this->original);
    }

    public function afterDelete()
    {
        Files::delete_view($this);
    }

    public function scopeGetTemplate($query)
    {
        return $query;
    }
}