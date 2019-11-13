<?php

defined('_EXEC') or die;

$this->dependencies->add(['css', '{$path.plugins}data-tables/jquery.dataTables.min.css']);
$this->dependencies->add(['js', '{$path.plugins}data-tables/jquery.dataTables.min.js']);
$this->dependencies->add(['js', '{$path.js}Settings/opportunitytypes.js']);
$this->dependencies->add(['other', '<script>menu_focus("settings");</script>']);

?>

%{header}%
<main>
    <div class="multi-tabs" data-tab-active="tab3">
        <ul>
            <?php if (Functions::check_access(['{opportunityareas_create}','{opportunityareas_update}','{opportunityareas_delete}']) == true) : ?>
            <li data-tab-target="tab1" class="active"><a href="/settings/opportunityareas">{$lang.opportunity_areas}</a></li>
            <?php endif; ?>
            <?php if (Functions::check_access(['{opportunitytypes_create}','{opportunitytypes_update}','{opportunitytypes_delete}']) == true) : ?>
            <li data-tab-target="tab3" class="active"><a href="/settings/opportunitytypes">{$lang.opportunity_types}</a></li>
            <?php endif; ?>
            <?php if (Functions::check_access(['{locations_create}','{locations_update}','{locations_delete}']) == true) : ?>
            <li data-tab-target="tab4" class="active"><a href="/settings/locations">{$lang.locations}</a></li>
            <?php endif; ?>
            <?php if (Functions::check_access(['{rooms_create}','{rooms_update}','{rooms_delete}']) == true) : ?>
            <li data-tab-target="tab5" class="active"><a href="/settings/rooms">{$lang.rooms}</a></li>
            <?php endif; ?>
            <?php if (Functions::check_access(['{guesttreatments_create}','{guesttreatments_update}','{guesttreatments_delete}']) == true) : ?>
            <li data-tab-target="tab6" class="active"><a href="/settings/guesttreatments">{$lang.guest_treatments}</a></li>
            <?php endif; ?>
            <?php if (Functions::check_access(['{guesttypes_create}','{guesttypes_update}','{guesttypes_delete}']) == true) : ?>
            <li data-tab-target="tab7" class="active"><a href="/settings/guesttypes">{$lang.guest_types}</a></li>
            <?php endif; ?>
            <?php if (Functions::check_access(['{reservationstatus_create}','{reservationstatus_update}','{reservationstatus_delete}']) == true) : ?>
            <li data-tab-target="tab8" class="active"><a href="/settings/reservationstatus">{$lang.reservation_status}</a></li>
            <?php endif; ?>
        </ul>
        <?php if (Functions::check_access(['{opportunitytypes_create}','{opportunitytypes_update}','{opportunitytypes_delete}']) == true) : ?>
        <div class="tab" data-target="tab3">
            <section class="box-container complete">
                <div class="main">
                    <?php if (Functions::check_access(['{opportunitytypes_create}']) == true) : ?>
                    <article>
                        <main class="tables">
                            <form name="new_opportunity_type">
                                <div class="row">
                                    <div class="span2">
                                        <div class="label">
                                            <label>
                                                <p>{$lang.opportunity_area}</p>
                                                <select name="opportunity_area">
                                                    <option value="" selected hidden>{$lang.choose}</option>
                                                    {$opt_opportunity_areas}
                                                </select>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="span2">
                                        <div class="label">
                                            <label>
                                                <p>(ES) {$lang.opportunity_type}</p>
                                                <input type="text" name="name_es" />
                                            </label>
                                        </div>
                                    </div>
                                    <div class="span2">
                                        <div class="label">
                                            <label>
                                                <p>(EN) {$lang.opportunity_type}</p>
                                                <input type="text" name="name_en" />
                                            </label>
                                        </div>
                                    </div>
                                    <div class="span2">
                                        <div class="label">
                                            <label>
                                                <p>{$lang.to_request}</p>
                                                <div class="switch">
                                                    <input id="ot-request" type="checkbox" name="request" class="switch-checkbox" checked>
                                                    <label class="switch-label" for="ot-request"></label>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="span2">
                                        <div class="label">
                                            <label>
                                                <p>{$lang.to_incident}</p>
                                                <div class="switch">
                                                    <input id="ot-incident" type="checkbox" name="incident" class="switch-checkbox" checked>
                                                    <label class="switch-label" for="ot-incident"></label>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="span2">
                                        <div class="label">
                                            <label>
                                                <p>{$lang.public}</p>
                                                <div class="switch">
                                                    <input id="ot-public" type="checkbox" name="public" class="switch-checkbox" checked>
                                                    <label class="switch-label" for="ot-public"></label>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </main>
                        <footer>
                            <div class="buttons text-center">
                                <a class="btn" data-action="new_opportunity_type">{$lang.add}</a>
                            </div>
                        </footer>
                    </article>
                    <?php endif; ?>
                    <article>
                        <main class="tables">
                            <div class="table-container">
                                <table id="opportunity_types" class="table">
                                    <thead>
                                        <tr>
                                            <th align="left" width="200px">{$lang.opportunity_area}</th>
                                            <th align="left">{$lang.opportunity_type}</th>
                                            <th align="left" width="100px">{$lang.to_request}</th>
                                            <th align="left" width="100px">{$lang.to_incident}</th>
                                            <th align="left" width="100px">{$lang.public}</th>
                                            <?php if (Functions::check_access(['{opportunitytypes_delete}']) == true) : ?>
                                            <th align="right" class="icon"></th>
                                            <?php endif; ?>
                                            <?php if (Functions::check_access(['{opportunitytypes_update}']) == true) : ?>
                                            <th align="right" class="icon"></th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {$tbl_opportunity_types}
                                    </tbody>
                                </table>
                            </div>
                        </main>
                    </article>
                </div>
            </section>
        </div>
        <?php endif; ?>
    </div>
</main>
<?php if (Functions::check_access(['{opportunitytypes_update}']) == true) : ?>
<section class="modal" data-modal="edit_opportunity_type">
    <div class="content">
        <header>
            <h3>{$lang.edit}</h3>
        </header>
        <main>
            <form name="edit_opportunity_type">
                <div class="row">
                    <div class="span12">
                        <div class="label">
                            <label>
                                <p>{$lang.opportunity_area}</p>
                                <select name="opportunity_area">
                                    <option value="" selected hidden>{$lang.choose}</option>
                                    {$opt_opportunity_areas}
                                </select>
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label>
                                <p>(ES) {$lang.opportunity_type}</p>
                                <input type="text" name="name_es" />
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label>
                                <p>(EN) {$lang.opportunity_type}</p>
                                <input type="text" name="name_en" />
                            </label>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="label">
                            <label>
                                <p>{$lang.to_request}</p>
                                <div class="switch">
                                    <input id="ot-e-request" type="checkbox" name="request" class="switch-checkbox">
                                    <label class="switch-label" for="ot-e-request"></label>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="label">
                            <label>
                                <p>{$lang.to_incident}</p>
                                <div class="switch">
                                    <input id="ot-e-incident" type="checkbox" name="incident" class="switch-checkbox">
                                    <label class="switch-label" for="ot-e-incident"></label>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="label">
                            <label>
                                <p>{$lang.public}</p>
                                <div class="switch">
                                    <input id="ot-e-public" type="checkbox" name="public" class="switch-checkbox">
                                    <label class="switch-label" for="ot-e-public"></label>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </main>
        <footer>
            <div class="action-buttons">
                <button class="btn btn-flat" button-cancel>{$lang.cancel}</button>
                <button class="btn" button-success>{$lang.accept}</button>
            </div>
        </footer>
    </div>
</section>
<?php endif; ?>
<?php if (Functions::check_access(['{opportunitytypes_delete}']) == true) : ?>
<section class="modal" data-modal="delete_opportunity_type">
    <div class="content">
        <header>
            <h3>{$lang.delete}</h3>
        </header>
        <footer>
            <div class="action-buttons">
                <button class="btn btn-flat" button-close>{$lang.cancel}</button>
                <button class="btn" button-success>{$lang.accept}</button>
            </div>
        </footer>
    </div>
</section>
<?php endif; ?>
