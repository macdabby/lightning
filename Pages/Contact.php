<?php
/**
 * @file
 * Contains Lightning\Pages\Page
 */

namespace Lightning\Pages;

use Lightning\Model\URL;
use Lightning\Tools\Configuration;
use Lightning\Tools\Form;
use Lightning\Tools\Language;
use Lightning\Tools\Mailer;
use Lightning\Tools\Messenger;
use Lightning\Tools\Navigation;
use Lightning\Tools\Output;
use Lightning\Tools\ReCaptcha;
use Lightning\Tools\Request;
use Lightning\Tools\Tracker;
use Lightning\View\Page as PageView;
use Lightning\Model\User as UserModel;
use Lightning\Model\Message;

/**
 * A contact page handler.
 *
 * @package Lightning\Pages
 */
class Contact extends PageView {

    protected $page = 'contact';
    protected $menuContext = 'contact';

    /**
     * @var UserModel
     */
    protected $user;

    /**
     * @var array
     */
    protected $settings;

    public function __construct() {
        parent::__construct();
        Form::requiresToken();
    }

    protected function hasAccess() {
        return true;
    }

    /**
     * Send a posted contact request to the site admin.
     */
    public function post() {
        $this->settings = Configuration::get('contact');

        // Check captcha if required.
        $request_contact = Request::post('contact', 'boolean');
        if (
            !empty($this->settings['require_captcha'])
            && (
                $this->settings['require_captcha'] === true
                || (
                    $this->settings['require_captcha'] == 'contact_only'
                    && $request_contact
                )
            )
            && !ReCaptcha::verify()
        ) {
            Messenger::error('You did not correctly enter the captcha code.');
            return $this->get();
        }

        // Make sure the sender's email address is valid.
        if (!$this->getSender()) {
            Messenger::error('Please enter a valid email address.');
            return $this->get();
        }

        // Optin the user.
        if ($list = Request::get('list', 'int')) {
            if (!Message::validateListID($list)) {
                $list = Message::getDefaultListID();
            }
        }
        if (empty($list) && Request::get('optin', 'boolean')) {
            $list = Message::getDefaultListID();
        }
        if (!empty($list)) {
            $this->user->subscribe($list);
        }

        // Send a message to the user who just opted in.
        if ($message = Request::post('message', 'int')) {
            $mailer = new Mailer();
            $mailer->sendOne($message, $this->user);
        }

        // Send a message to the site contact.
        if (!empty($this->settings['always_notify']) || ($request_contact && $this->settings['contact'])) {
            $sent = $this->sendMessage();
            Tracker::trackEvent('Contact Sent', URL::getCurrentUrlId(), $this->user->id);
            if (!$sent) {
                Output::error('Your message could not be sent. Please try again later');
            } else {
                // Send an email to to have them test for spam.
                if (!empty($this->settings['auto_responder'])) {
                    $auto_responder_mailer = new Mailer();
                    $result = $auto_responder_mailer->sendOne($this->settings['auto_responder'], UserModel::loadByEmail($this->getSender()) ?: new UserModel(array('email' => $this->getSender())));
                    if ($result && $this->settings['spam_test']) {
                        // Set the notice.
                        $this->setSuccessMessage(Language::translate('spam_test'));
                        $this->redirect();
                    }
                }
                $this->setSuccessMessage(Language::translate('contact_sent'));
                $this->redirect();
            }
        } else {
            $this->setSuccessMessage(Language::translate('optin.success'));
            $this->redirect();
        }
    }

    public function redirect($params = []) {
        if ($redirect = Request::post('redirect', 'url')) {
            Navigation::redirect($redirect, $params);
        } else {
            Navigation::redirect('/message', $params);
        }
    }

    /**
     * Add a custom message from the form input.
     */
    protected function setSuccessMessage($default) {
        if ($this->settings['custom_message'] && $message = Request::post('success')) {
            Messenger::message($message);
        } elseif (isset($_POST['success'])) {
            return;
        } else {
            Messenger::message($default);
        }
    }

    /**
     * Load the user data and save it into a user entry.
     *
     * @return UserModel|boolean
     */
    protected function getSender() {
        if ($name = Request::post('name', '', '', '')) {
            $name_parts = explode(' ', $name, 2);
            $name = array('first' => $name_parts[0]);
            if (!empty($name_parts[1])) {
                $name['last'] = $name_parts[1];
            }
        } else {
            $name = array(
                'first' => Request::post('first', '', '', ''),
                'last' => Request::post('last', '', '', ''),
            );
        }

        // Add the user to the database.
        $email = Request::post('email', 'email');
        if (empty($email)) {
            return false;
        }
        $this->user = UserModel::addUser($email, $name);
        return true;
    }

    /**
     * Send a message to the site contact.
     *
     * @return boolean
     *   Whether the email was successfully sent.
     */
    public function sendMessage() {
        $mailer = new Mailer();
        foreach ($this->settings['to'] as $to) {
            $mailer->to($to);
        }
        return $mailer
            ->replyTo($this->user->email)
            ->subject($this->settings['subject'])
            ->message($this->getMessageBody())
            ->send();
    }

    /**
     * Create the message body to the site contact.
     *
     * @return string
     */
    protected function getMessageBody() {
        $fields = array_combine(array_keys($_POST), array_keys($_POST));
        $values = [
            'Name' => Request::post('name'),
            'Email' => $this->user->email,
            'IP' => Request::server(Request::IP),
        ];
        $message = Request::post('message');

        unset($fields['token']);
        unset($fields['name']);
        unset($fields['email']);
        unset($fields['message']);
        unset($fields['contact']);
        unset($fields['success']);
        unset($fields['list']);
        unset($fields['g-recaptcha-response']);
        unset($fields['captcha_abide']);

        foreach ($fields as $field) {
            if (is_array($_POST[$field])) {
                $input = json_encode(Request::post($field, 'array'));
            } else {
                $input = Request::post($field);
            }
            $values[ucfirst(preg_replace('/_/', ' ', $field))] = $input;
        }

        $output = '';
        foreach ($values as $key => $value) {
            $output .= $key . ': ' . $value . "<br>\n";
        }
        $output .= "Message: <br>\n" . $message;
        return $output;
    }
}
