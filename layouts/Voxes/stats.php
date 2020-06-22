<?php

defined('_EXEC') or die;

$this->dependencies->add(['js', '{$path.plugins}charts/Chart.js']);
$this->dependencies->add(['js', '{$vkye_base}Voxes/charts']);
$this->dependencies->add(['js', '{$path.js}Voxes/stats.js']);
$this->dependencies->add(['other', '<script>menu_focus("voxes");</script>']);

?>

%{header}%
<main class="dashboard">
    <section class="workspace">
        <div class="charts">
            <!-- <form name="get_v_chart_data">
                <div class="row">
                    <div class="span4">
                        <div class="label">
                            <label>
                                <p>{$lang.started_date}</p>
                                <input type="date" name="started_date" value="<?php echo Functions::get_past_date(Functions::get_current_date(), '7', 'days'); ?>">
                            </label>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="label">
                            <label>
                                <p>{$lang.end_date}</p>
                                <input type="date" name="date_end" value="<?php echo Functions::get_current_date(); ?>">
                            </label>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="label">
                            <label>
                                <p>{$lang.vox_type}</p>
                                <select name="type">
                                    <option value="all">{$lang.view_all}</option>
                                    <option value="request">{$lang.view_only_requests}</option>
                                    <option value="incident">{$lang.view_only_incidents}</option>
                                    <option value="workorder">{$lang.view_only_workorders}</option>
                                </select>
                            </label>
                        </div>
                    </div>
                </div>
            </form> -->
            <div class="charts small">
                <canvas id="v_oa_chart" height="300"></canvas>
            </div>
            <div class="charts small">
                <canvas id="v_r_chart" height="300"></canvas>
            </div>
            <div class="charts small">
                <canvas id="v_l_chart" height="300"></canvas>
            </div>
            <!-- <form name="get_ar_chart_data">
                <div class="row">
                    <div class="span4">
                        <div class="label">
                            <label>
                                <p>{$lang.started_date}</p>
                                <input type="date" name="started_date" value="<?php echo Functions::get_past_date(Functions::get_current_date(), '7', 'days'); ?>">
                            </label>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="label">
                            <label>
                                <p>{$lang.end_date}</p>
                                <input type="date" name="date_end" value="<?php echo Functions::get_current_date(); ?>">
                            </label>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="label">
                            <label>
                                <p>{$lang.vox_type}</p>
                                <select name="type">
                                    <option value="all">{$lang.view_all}</option>
                                    <option value="request">{$lang.view_only_requests}</option>
                                    <option value="incident">{$lang.view_only_incidents}</option>
                                    <option value="workorder">{$lang.view_only_workorders}</option>
                                </select>
                            </label>
                        </div>
                    </div>
                </div>
            </form> -->
            <div class="charts small">
                <canvas id="ar_oa_chart" height="300"></canvas>
            </div>
            <div class="charts small">
                <canvas id="ar_r_chart" height="300"></canvas>
            </div>
            <div class="charts small">
                <canvas id="ar_l_chart" height="300"></canvas>
            </div>
            <!-- <form name="get_c_chart_data">
                <div class="row">
                    <div class="span6">
                        <div class="label">
                            <label>
                                <p>{$lang.started_date}</p>
                                <input type="date" name="started_date" value="<?php echo Functions::get_past_date(Functions::get_current_date(), '7', 'days'); ?>">
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label>
                                <p>{$lang.end_date}</p>
                                <input type="date" name="date_end" value="<?php echo Functions::get_current_date(); ?>">
                            </label>
                        </div>
                    </div>
                </div>
            </form> -->
            <div class="charts small">
                <canvas id="c_oa_chart" height="300"></canvas>
            </div>
            <div class="charts small">
                <canvas id="c_r_chart" height="300"></canvas>
            </div>
            <div class="charts small">
                <canvas id="c_l_chart" height="300"></canvas>
            </div>
        </div>
    </section>
    <section class="buttons">
        <div>
            <a data-button-modal="search"><i class="fas fa-search"></i></a>
            <a href="/voxes"><i class="fas fa-atom"></i></a>
            <?php if (Functions::check_user_access(['{voxes_stats_view}']) == true) : ?>
            <a href="/voxes/stats" class="active"><i class="fas fa-chart-pie"></i></a>
            <?php endif; ?>
            <?php if (Functions::check_user_access(['{voxes_reports_print}']) == true) : ?>
            <a href="/voxes/reports/print"><i class="fas fa-bug"></i></a>
            <?php elseif (Functions::check_user_access(['{voxes_reports_create}','{voxes_reports_update}','{voxes_reports_deactivate}','{voxes_reports_activate}','{voxes_reports_delete}']) == true) : ?>
            <a href="/voxes/reports"><i class="fas fa-bug"></i></a>
            <?php endif; ?>
        </div>
    </section>
</main>

<!-- <main class="voxes-stats">
    <article>
        <main>
            <div class="counters">
                <h2>{$general_average_resolution}<span>{$lang.average_resolution}</span></h2>
                <h2>{$count_created_today}<span>{$lang.created_today}</span></h2>
                <h2>{$count_created_week}<span>{$lang.created_week}</span></h2>
                <h2>{$count_created_month}<span>{$lang.created_month}</span></h2>
                <h2>{$count_created_year}<span>{$lang.created_year}</span></h2>
                <h2>{$count_created_total}<span>{$lang.created_total}</span></h2>
            </div>

        </main>
    </article>
</main> -->
