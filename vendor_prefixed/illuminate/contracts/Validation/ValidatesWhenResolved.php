<?php
/**
 * @license MIT
 *
 * Modified by gravityview on 08-November-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityView\Foundation\ThirdParty\Illuminate\Contracts\Validation;

interface ValidatesWhenResolved
{
    /**
     * Validate the given class instance.
     *
     * @return void
     */
    public function validate();
}
