<?php namespace OctoDevel\OctoMail;

use \Lang;
use Backend;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name' => 'Octo Mail',
            'description' => 'Create front-end contact forms that allow to send email messages based on custom templates.',
            'author' => 'Octo Devel - Trumbowyg by Alex D - http://alex-d.fr/',
            'icon' => 'icon-envelope'
        ];
    }

    public function registerComponents()
    {
        return [
            'OctoDevel\OctoMail\Components\Template' => 'mailTemplate',
        ];
    }

    public function registerNavigation()
    {
        return [
            'octomail' => [
                'label'       => Lang::get('octodevel.octomail::lang.octomail.name'),
                'url'         => Backend::url('octodevel/octomail/templates'),
                'icon'        => 'icon-envelope',
                'permissions' => ['octodevel.octomail.*'],
                'order'       => 500,

                'sideMenu' => [
                    'templates' => [
                        'label'       => Lang::get('octodevel.octomail::lang.templates.title'),
                        'icon'        => 'icon-list-alt',
                        'url'         => Backend::url('octodevel/octomail/templates'),
                        'permissions' => ['octodevel.octomail.access_templates'],
                    ],
                    'logs' => [
                        'label'       => Lang::get('octodevel.octomail::lang.contact_log.title'),
                        'icon'        => 'icon-file-text-o',
                        'url'         => Backend::url('octodevel/octomail/logs'),
                        'permissions' => ['octodevel.octomail.access_logs'],
                    ]
                ]
            ]
        ];
    }

    public function registerFormWidgets()
    {
        return [
            'OctoDevel\OctoMail\FormWidgets\Trumbowyg' => [
                'label' => 'Trumbowyg',
                'alias' => 'trumbowyg'
            ],
            'OctoDevel\OctoMail\FormWidgets\JsonRender' => [
                'label' => 'JsonRender',
                'alias' => 'jsonrender'
            ],
            'OctoDevel\OctoMail\FormWidgets\TemplateData' => [
                'label' => 'TemplateData',
                'alias' => 'templatedata'
            ],
            'OctoDevel\OctoMail\FormWidgets\UserAgent' => [
                'label' => 'UserAgent',
                'alias' => 'useragent'
            ]
        ];
    }

    public function registerPermissions()
    {
        return [
            'octodevel.octomail.access_templates' => ['label' => Lang::get('octodevel.octomail::lang.templates.permission'), 'tab' => 'OctoDevel'],
            'octodevel.octomail.access_logs' => ['label' => Lang::get('octodevel.octomail::lang.contact_log.permission'), 'tab' => 'OctoDevel']
        ];
    }
}