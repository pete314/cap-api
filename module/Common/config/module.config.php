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
 * @description CLASS NAME - Short description
 */
return array(
    'service_manager' => array(
        'invokables' => array(
            'Common\Listeners\GlobalErrorListener' => 'Common\Listeners\GlobalErrorListener',
            'Common\Listeners\AuthListener'    => 'Common\Listeners\AuthListener',
        )
    )
);

