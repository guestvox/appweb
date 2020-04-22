<?php

defined('_EXEC') or die;

$this->dependencies->add(['css', '{$path.css}Signup/activate.css']);
$this->dependencies->add(['js', '{$path.js}Signup/activate.js']);

?>

<main class="signup-activate">
    <figure>
        <img src="{$path.images}logotype-color.png" alt="GuestVox">
    </figure>
    <p>{$txt}</p>
    <figure>
        <img src="{$path.images}signup/load.gif" alt="Icon">
    </figure>
</main>