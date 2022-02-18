<?php

namespace MODX\Twilio\Snippet;

use libphonenumber\PhoneNumberUtil;

class SupportedRegions extends Snippet
{
    public function process()
    {
        $regions = PhoneNumberUtil::getSupportedRegions();
        $setFirst = $this->getOption('setFirst');
        $setFirstType = $this->getOption('setFirstType', 'csv');
        $selected = (int) $this->getOption('selected', 0);
        $output = '';
        if (!is_array($setFirst)) {
            if ($setFirstType === 'json') {
                $setFirst = json_decode($setFirst);
            } elseif ($setFirstType === 'csv') {
                $setFirst = explode(',', $setFirst);
            } else {
                $setFirst = [];
            }
        }
        $regions = array_merge($setFirst, array_diff($regions, $setFirst));
        $tpl = $this->getOption('tpl');
        if (empty($tpl)) {
            foreach ($regions as $region) {
                $selected = ($region === $selected) ? "selected='selected'" : '';
                $output .= "<option value='$region' $selected>+$region</option>";
            }
        } else {
            foreach ($regions as $region) {
                $output .= $this->modx->getChunk($tpl, ['region' => $region, 'selected' => ($region === $selected) ? 1 : 0]);
            }
        }
        return $output;
    }
}
