<?php
/*
Shortcode: variable
*/

  echo isset($arguments['user']) ? $current_user->$arguments['user'] : null;