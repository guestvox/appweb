<?php

defined('_EXEC') or die;

$this->dependencies->add(['js', '{$path.js}Voxes/history.js']);
$this->dependencies->add(['other', '<script>menu_focus("voxes");</script>']);

?>

%{header}%
<main>
    <nav>
        <h2><i class="fas fa-history"></i>{$lang.history}</h2>
        <ul>
            <li><a href="/voxes/view/details/{$id}"><i class="fas fa-info-circle"></i></a></li>
            <li><a href="/voxes/view/history/{$id}" class="view"><i class="fas fa-history"></i></a></li>
        </ul>
    </nav>
    <article>
        <main>
            <div class="vox-history">
                {$div_changes}
            </div>
        </main>
    </article>
</main>
