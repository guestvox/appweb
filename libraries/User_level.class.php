<?php

defined('_EXEC') or die;

class User_level
{
    public function access($path)
    {
        $paths = [];

        array_push($paths, '/Dashboard/index');
        array_push($paths, '/Dashboard/logout');
        array_push($paths, '/Voxes/index');
        array_push($paths, '/Voxes/create');
        array_push($paths, '/Voxes/details');
        array_push($paths, '/Voxes/history');
        array_push($paths, '/Profile/index');

        foreach (Session::get_value('user')['user_permissions'] as $key => $value)
        {
            switch ($value)
            {
                case '{voxes_update}' :
                    array_push($paths, '/Voxes/edit');
                break;

                case '{vox_reports_view}' :
                    array_push($paths, '/Voxes/generate');
                break;

                case '{vox_reports_create}' :
                    array_push($paths, '/Voxes/reports');
                break;

                case '{vox_reports_update}' :
                    array_push($paths, '/Voxes/reports');
                break;

                case '{vox_reports_delete}' :
                    array_push($paths, '/Voxes/reports');
                break;

                case '{vox_stats_view}' :
                    array_push($paths, '/Voxes/stats');
                    array_push($paths, '/Voxes/charts');
                break;

                case '{survey_questions_create}' :
                    array_push($paths, '/Surveys/questions');
                break;

                case '{survey_questions_update}' :
                    array_push($paths, '/Surveys/questions');
                break;

                case '{survey_questions_deactivate}' :
                    array_push($paths, '/Surveys/questions');
                break;

                case '{survey_questions_activate}' :
                    array_push($paths, '/Surveys/questions');
                break;

                case '{survey_questions_delete}' :
                    array_push($paths, '/Surveys/questions');
                break;

                case '{survey_answers_view}' :
                    array_push($paths, '/Surveys/answers');
                    array_push($paths, '/Surveys/comments');
                    array_push($paths, '/Surveys/contact');
                break;

                case '{survey_stats_view}' :
                    array_push($paths, '/Surveys/stats');
                    array_push($paths, '/Surveys/charts');
                break;

                case '{rooms_create}' :
                    array_push($paths, '/Rooms/index');
                break;

                case '{rooms_update}' :
                    array_push($paths, '/Rooms/index');
                break;

                case '{rooms_delete}' :
                    array_push($paths, '/Rooms/index');

                case '{tables_create}' :
                    array_push($paths, '/Tables/index');
                break;

                case '{tables_update}' :
                    array_push($paths, '/Tables/index');
                break;

                case '{tables_delete}' :
                    array_push($paths, '/Tables/index');
                break;

                case '{clients_create}' :
                    array_push($paths, '/Clients/index');
                break;

                case '{clients_update}' :
                    array_push($paths, '/Clients/index');
                break;

                case '{clients_delete}' :
                    array_push($paths, '/Clients/index');
                break;

                case '{opportunity_areas_create}' :
                    array_push($paths, '/Opportunityareas/index');
                break;

                case '{opportunity_areas_update}' :
                    array_push($paths, '/Opportunityareas/index');
                break;

                case '{opportunity_areas_delete}' :
                    array_push($paths, '/Opportunityareas/index');
                break;

                case '{opportunity_types_create}' :
                    array_push($paths, '/Opportunitytypes/index');
                break;

                case '{opportunity_types_update}' :
                    array_push($paths, '/Opportunitytypes/index');
                break;

                case '{opportunity_types_delete}' :
                    array_push($paths, '/Opportunitytypes/index');
                break;

                case '{locations_create}' :
                    array_push($paths, '/Locations/index');
                break;

                case '{locations_update}' :
                    array_push($paths, '/Locations/index');
                break;

                case '{locations_delete}' :
                    array_push($paths, '/Locations/index');
                break;

                case '{reservation_statuses_create}' :
                    array_push($paths, '/Reservationstatuses/index');
                break;

                case '{reservation_statuses_update}' :
                    array_push($paths, '/Reservationstatuses/index');
                break;

                case '{reservation_statuses_delete}' :
                    array_push($paths, '/Reservationstatuses/index');
                break;

                case '{guest_treatments_create}' :
                    array_push($paths, '/Guesttreatments/index');
                break;

                case '{guest_treatments_update}' :
                    array_push($paths, '/Guesttreatments/index');
                break;

                case '{guest_treatments_delete}' :
                    array_push($paths, '/Guesttreatments/index');
                break;

                case '{guest_types_create}' :
                    array_push($paths, '/Guesttypes/index');
                break;

                case '{guest_types_update}' :
                    array_push($paths, '/Guesttypes/index');
                break;

                case '{guest_types_delete}' :
                    array_push($paths, '/Guesttypes/index');
                break;

                case '{users_create}' :
                    array_push($paths, '/Users/index');
                break;

                case '{users_update}' :
                    array_push($paths, '/Users/index');
                break;

                case '{users_restore_password}' :
                    array_push($paths, '/Users/index');
                break;

                case '{users_deactivate}' :
                    array_push($paths, '/Users/index');
                break;

                case '{users_activate}' :
                    array_push($paths, '/Users/index');
                break;

                case '{users_delete}' :
                    array_push($paths, '/Users/index');
                break;

                case '{user_levels_create}' :
                    array_push($paths, '/Userlevels/index');
                break;

                case '{user_levels_update}' :
                    array_push($paths, '/Userlevels/index');
                break;

                case '{user_levels_delete}' :
                    array_push($paths, '/Userlevels/index');
                break;

                case '{account_update}' :
                    array_push($paths, '/Account/index');
                break;

                default: break;
            }
        }

        $paths = array_unique($paths);
        $paths = array_values($paths);

        return in_array($path, $paths) ? true : false;
    }
}
