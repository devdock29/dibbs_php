<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace nazharFramework\twig;

class TwigExtensions extends \Twig_Extension {

    public function getFunctions() {
        return array(
            'localeMsg' => new \Twig_Function_Method($this, 'localeMsg'),
        );
    }

    public function localeMsg($key) {
        return \nasFramework\lang\Lang::get($key);
    }

    public function getName() {
        return 'my_extension';
    }

}
