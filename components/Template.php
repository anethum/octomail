<?php namespace OctoDevel\OctoMail\Components;

use Mail;
use Redirect;
use Validator;
use Cms\Classes\ComponentBase;
use Cms\Classes\CmsPropertyHelper;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Request;
use October\Rain\Support\ValidationException;
use System\Models\EmailSettings;
use \System\Models\EmailTemplate;
use OctoDevel\OctoMail\Models\Template as TemplateBase;
use OctoDevel\OctoMail\Models\Recipient as RecipientEmail;
use OctoDevel\OctoMail\Models\Log as RegisterLog;

class Template extends ComponentBase
{
    public $table = 'octodevel_octomail_templates';
    public $recipients = 'octodevel_octomail_tem_rec';
    public $recipients_table = 'octodevel_octomail_recipients';

    public $aRName;
    public $aREmail;
    public $requestTemplate;
    public $langs = [
        ''=>'',
        'nl' => 'Dutch',
        'en' => 'English',
        'de' => 'German',
        'ja' => 'Japanese',
        'br' => 'Portuguese (Brazilian)',
        'pt' => 'Portuguese',
        'ru' => 'Russian',
        'sv' => 'Swedish',
        'tr' => 'Turkish'
    ];

    /*
     * Validation
     */
    public $rules = [];

    public function componentDetails()
    {
        return [
            'name'        => 'Mail Template',
            'description' => 'Displays a mail template to contact form where ever it\'s been embedded.'
        ];
    }

    public function defineProperties()
    {
        return [
            'redirectURL' => [
                'title'       => 'Redirect to',
                'description' => 'Redirect to page after send email.',
                'type'        => 'dropdown',
                'default'     => ''
            ],
            'templateName' => [
                'title'       => 'Mail template',
                'description' => 'Select the mail template.',
                'type'        => 'dropdown',
                'default'     => ''
            ],
            'responseTemplate' => [
                'title'       => 'Response template',
                'description' => 'Select the response mail template.',
                'type'        => 'dropdown',
                'default'     => 'octodevel.octomail::emails.autoresponse'
            ],
            'responseFieldName' => [
                'title'       => 'Response field name',
                'description' => 'Set here your form field name that auto-response message will use as recipient.',
                'type'        => 'string',
                'default'     => 'name'
            ],
            'responseFieldEmail' => [
                'title'       => 'Response field email',
                'description' => 'Set here your form field email that auto-response message will use as recipient.',
                'type'        => 'string',
                'default'     => 'email'
            ]
        ];
    }

    public function getRedirectURLOptions()
    {
        return array_merge([''=>'- none -'], CmsPropertyHelper::listPages());
    }

    public function getResponseTemplateOptions()
    {
        $temp = new EmailTemplate();
        $templates = $temp->listRegisteredTemplates();

        return array_merge(array('' => '- none -'), $templates);
    }

    public function getTemplateNameOptions()
    {
        $templates = DB::table($this->table)
                    ->orderBy('title','asc')
                    ->groupBy('slug')
                    ->get();

        $array_dropdown = [];

        foreach ($templates as $template)
        {
            $array_dropdown[$template->slug] = $template->title . ' [' . $this->langs[$template->lang] . ']';
        }

        return array_merge([''=>''], $array_dropdown);
    }

    public function onOctoMailSent()
    {
        // Set the requested template in a variable;
        $this->requestTemplate = $this->loadTemplate();
        if(!$this->requestTemplate)
            throw new \Exception(sprintf('A unexpected error has occurred. The template slug is invalid.'));

        // Set a second variable with request data from database
        $template = $this->requestTemplate->attributes;
        if(!$template)
            throw new \Exception(sprintf('A unexpected error has occurred. Erro while trying to get a non-object propertyOrParam.'));

        // Set a global $_POST variable
        $post = post();

        // Unset problematic variables
        if(isset($post['message']))
        {
            // change message to body variable
            $post['body'] = $post['message'];
            unset($post['message']);
        }

        // get name for auto-response message
        $this->aRName = $this->propertyOrParam('responseFieldName');

        // get email for auto-response message
        $this->aREmail = $this->propertyOrParam('responseFieldEmail');

        // Set redirect URL
        $redirectUrl = $this->controller->pageUrl($this->propertyOrParam('redirectURL'));

        // Get response email
        $responseMailTemplate = $this->propertyOrParam('responseTemplate');

        // Get request info
        $request = Request::createFromGlobals();

        // Set some request variables
        $post['ip_address'] = $request->getClientIp();
        $post['user_agent'] = $request->headers->get('User-Agent');
        $post['sender_name'] = $template['sender_name'];
        $post['sender_email'] = $template['sender_email'];
        $post['recipient_name'] = $template['recipient_name'] ? $template['recipient_name'] : EmailSettings::get('sender_name');
        $post['recipient_email'] = $template['recipient_email'] ? $template['recipient_email'] : EmailSettings::get('sender_email');
        $post['default_subject'] = $template['subject'];

        // Set some usable data
        $data = [
            'sender_name' => $template['sender_name'],
            'sender_email' => $template['sender_email'],
            'recipient_name' => $template['recipient_name'] ? $template['recipient_name'] : EmailSettings::get('sender_name'),
            'recipient_email' => $template['recipient_email'] ? $template['recipient_email'] : EmailSettings::get('sender_email'),
            'default_subject' => $template['subject']
        ];

        // Making custon validation
        $fields = explode(',', preg_replace('/\s/', '', $template['fields']));

        if($fields)
        {
            $validation_rules = [];
            foreach ($fields as $field)
            {
                $rules = explode("|", $field);
                if($rules)
                {
                    $field_name = $rules[0];
                    $validation_rules[$field_name] = [];
                    unset($rules[0]);

                    foreach ($rules as $key => $rule)
                    {
                    $validation_rules[$field_name][$key] = $rule;
                    }
                }
            }
            $this->rules = $validation_rules;

            $validation = Validator::make($post, $this->rules);
            if ($validation->fails())
                throw new ValidationException($validation);
        }

        // Get recipients
        $recipients = DB::table($this->recipients)
                    ->where('template_id', '=', $template['id'])
                    ->leftJoin($this->recipients_table, $this->recipients_table . '.id', '=', $this->recipients . '.recipient_id')
                    ->get();

        if(!$template['multiple_recipients'])
        {
            Mail::send('octodevel.octomail::emails.view-' . $template['slug'], $post, function($message) use($data)
            {
                $message->from($data['sender_email'], $data['sender_name']);
                $message->to($data['recipient_email'], $data['recipient_name'])->subject($data['default_subject']);
            });

            $log = new RegisterLog;
            $log->template_id = $template['id'];
            $log->sender_agent = $post['user_agent'];
            $log->sender_ip = $post['ip_address'];
            $log->sent_at = date('Y-m-d H:i:s');
            $log->data = $post;
            $log->save();

        }
        else
        {
            // Multiple emails
            foreach($recipients as $recipient)
            {
                $data['recipient_name'] = $recipient->name;
                $data['recipient_email'] = $recipient->email;

                Mail::send('octodevel.octomail::emails.view-' . $template['slug'], $post, function($message) use($data)
                {
                    $message->from($data['sender_email'], $data['sender_name']);
                    $message->to($data['recipient_email'], $data['recipient_name'])->subject($data['default_subject']);
                });

                $log = new RegisterLog;
                $log->template_id = $template['id'];
                $log->sender_agent = $post['user_agent'];
                $log->sender_ip = $post['ip_address'];
                $log->sent_at = date('Y-m-d H:i:s');
                $log->data = $post;
                $log->save();
            }
        }

        if( (isset($post[$this->aREmail]) and $post[$this->aREmail]) and (isset($post[$this->aRName]) and $post[$this->aRName]) and (isset($template['autoresponse']) and $template['autoresponse']) )
        {
            $response = [
                'name' => $post[$this->aRName],
                'email' => $post[$this->aREmail],
            ];

            if($responseMailTemplate)
            {
                Mail::send($responseMailTemplate, $post, function($autoresponse) use ($response)
                {
                    $autoresponse->to($response['email'], $response['name']);
                });
            }
        }

        $this->page["result"] = true;
        $this->page["confirmation_text"] = $template['confirmation_text'];

       if($redirectUrl)
               return Redirect::intended($redirectUrl);
    }

    protected function loadTemplate()
    {
        $slug = $this->propertyOrParam('templateName');
        return TemplateBase::getTemplate()->where('slug', '=', $slug)->first();
    }
}