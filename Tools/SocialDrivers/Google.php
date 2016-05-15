<?php

namespace Lightning\Tools\SocialDrivers;

use Google_Client;
use Google_Service_Plus;
use Lightning\Tools\Configuration;
use Lightning\Tools\Session;
use Lightning\View\JS;

class Google extends SocialMediaApi {

    const EMAIL_SUFFIX = 'google.com';

    /**
     * @var Google_Service_Plus
     */
    public $service;

    protected static $isApp = false;

    public static function setApp($app) {
        self::$isApp = $app;
    }

    public static function createInstance($token = null, $authorize = false) {
        include HOME_PATH . '/Lightning/Vendor/googleapiclient/src/Google/autoload.php';

        $google = new static();
        if (!empty($token)) {
            $google->setToken($token, $authorize);
        } else {
            $session = Session::getInstance(true, false);
            if ($session && $token = $session->getSetting('google.token')) {
                $google->setToken($token, $authorize);
            }
        }

        return $google;
    }

    public function setToken($token, $authorize = false) {
        $this->token = $token;
        $this->authorize = $authorize;

        if (empty(self::$isApp)) {
            $appId = Configuration::get('social.google.client_id');
            $secret = Configuration::get('social.google.secret');
        } else {
            $appId = Configuration::get('social.google-app.client_id');
            $secret = Configuration::get('social.google-app.secret');
        }

        $client = new Google_Client();
        $client->setClientId($appId);
        $client->setClientSecret($secret);

        if ($authorize) {
            $client->setAccessToken($token);
            $this->service = new Google_Service_Plus($client);
        } else {
            $user_data = $client->verifyIdToken($token);
            $this->id_profile = $user_data->getAttributes()['payload'];
            $this->social_id = $user_data->getUserId();
        }
    }

    public function storeSessionData() {
        $session = Session::getInstance();
        $session->setSetting('google.token', $this->token);
    }

    public function loadProfile() {
        if ($this->authorize && empty($this->profile)) {
            $this->profile = $this->service->people->get('me');
        }
    }

    public function authenticate() {
        if ($this->authorize) {
            $this->loadProfile();
            return !empty($this->me);
        } else {
            return !empty($this->id_profile);
        }
    }

    public function getLightningUserData() {
        if ($this->authorize) {
            $this->loadProfile();
            $user_settings = [
                'first' => $this->profile->getName()->getGivenName(),
                'last' => $this->profile->getName()->getFamilyName(),
            ];
            $emails = $this->profile->getEmails();
            if (!empty($emails)) {
                $user_settings['alt_email'] = $emails[0]->getValue();
            }
        } else {
            $user_settings = [
                'first' => $this->id_profile['given_name'],
                'last' => $this->id_profile['family_name'],
                'alt_email' => $this->id_profile['email'],
            ];
        }
        return $user_settings;
    }

    public function getSocialId() {
        if ($this->authorize) {
            return $this->profile->getId();
        } else {
            return $this->social_id;
        }
    }

    public function myImageURL() {
        if ($this->authorize) {
            return $this->profile->getImage()->getUrl();
        } else {
            return $this->id_profile['picture'];
        }
    }

    public function myImageData() {
        return file_get_contents($this->myImageURL());
    }

    public function getFriends() {
        return $this->service->people->listPeople('me', 'visible');
    }

    public function getFriendIDs() {
        $friends = $this->getFriends();
        $ids = [];
        foreach ($friends as $f) {
            $ids[] = $f->id;
        }

        return $ids;
    }

    public static function renderLinks() {
        $settings = Configuration::get('social.google');
        if (!empty($settings['like'])) {
            JS::add('https://apis.google.com/js/platform.js', true);
            $output = '<g:plusone size="medium" annotation="none"></g:plusone>';

            return $output;
        }
    }

    public static function loginButton($authorize = false) {
        JS::set('token', Session::getInstance()->getToken());
        JS::set('social.authorize', $authorize);
        JS::set('social.google.client_id', Configuration::get('social.google.client_id'));
        JS::startup('lightning.social.initLogin()');

        return '<span class="social-signin google"><i class="fa fa-google"></i><span> Sign in with Google</span></span>';
    }
}
