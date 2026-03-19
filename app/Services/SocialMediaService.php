<?php

namespace App\Services;

class SocialMediaService
{
    public function getIcon(object $request): string
    {
        if ($request['name'] == 'google-plus') {
            $icon = 'fa fa-google-plus-square';
        } elseif ($request['name'] == 'facebook') {
            $icon = 'fa fa-facebook';
        } elseif ($request['name'] == 'twitter') {
            $icon = 'fa fa-twitter';
        } elseif ($request['name'] == 'pinterest') {
            $icon = 'fa fa-pinterest';
        } elseif ($request['name'] == 'instagram') {
            $icon = 'fa fa-instagram';
        } elseif ($request['name'] == 'linkedin') {
            $icon = 'fa fa-linkedin';
        } else {
            $icon = '';
        }

        return $icon;
    }
}
