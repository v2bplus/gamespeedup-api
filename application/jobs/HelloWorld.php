<?php
namespace Jobs;

class HelloWorld
{
    public function perform($args)
    {
        $text = <<<TEXT
	    __  __     ____         _       __           __    __   __
	   / / / /__  / / /___     | |     / /___  _____/ /___/ /  / /
	  / /_/ / _ \/ / / __ \    | | /| / / __ \/ ___/ / __  /  / /
	 / __  /  __/ / / /_/ /    | |/ |/ / /_/ / /  / / /_/ /  /_/
	/_/ /_/\___/_/_/\____/     |__/|__/\____/_/  /_/\__,_/  (_)


TEXT;
        echo $text;
    }
}

