<?php

/*  * 
 * ===============================================================
 * Copyright (C) 2016 - Peter Nagy.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * ===============================================================
 * @author      Peter Nagy
 * @since       Jan 2016
 * @version     0.1
 * @description User module config
 */
return array(
    'service_manager' => array(
        'invokables' => array(
            'Common\Listeners\ApiErrorListener' => 'Common\Listeners\ApiErrorListener',
            'Common\Listeners\OAuthListener'    => 'Common\Listeners\OAuthListener',
        )
    )
);

