<?php
function merge_menu_page()
{
    $merge_message = '';

    if (isset($_POST['merge_users_submit'])) {
        $prev_user = intval($_POST['prev_user']);
        $current_user = intval($_POST['current_user']);

        if ($prev_user === $current_user) {
            $merge_message = 'Please select different users for merging.';
        } else {
            merge_users($prev_user, $current_user);
            $merge_message = 'Confirm merge';
        }
    }

?>
    <div class="merge-users-form container mt-5">
        <h2>Merge Users</h2>

        <?php if (!empty($merge_message)) : ?>
            <div class="<?php echo ($prev_user === $current_user) ? 'alert alert-danger' : 'alert alert-success'; ?>" role="alert">
                <?php echo esc_html($merge_message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="prev_user">Select Previous User:</label></th>
                    <td>
                        <select name="prev_user" id="prev_user" class="form-control search-user from-user" data-ajax-url="<?php echo admin_url('admin-ajax.php'); ?>">
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="current_user">Select Current User:</label></th>
                    <td>
                        <select name="current_user" id="current_user" class="form-control search-user to-user" data-ajax-url="<?php echo admin_url('admin-ajax.php'); ?>">
                        </select>
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td>
                        <button type="button" id="preview_users_button" class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#previewUsersModal">Preview Users</button>
                        <button type="button" id="merge_users_button" class="btn btn-primary mt-3">Merge Users</button>
                    </td>
                </tr>
            </table>
        </form>
        <div id="prev_user_table">
        </div>
        <!-- <div id="current_user_table">
        </div> -->
    </div>
    <link rel="stylesheet" href="/wp-content/plugins/user-merger/mergestyle.css" />

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <script>
        jQuery(document).ready(function($) {
            $('.search-user').select2({
                width: '30%',
                placeholder: 'Search for a user',
                allowClear: true,
                ajax: {
                    url: function() {
                        return $(this).data('ajax-url');
                    },
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            action: 'search_users_ajax'
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            });

            function showConfirmation() {
                Swal.fire({
                    text: 'Are you sure you want to merge the selected users?',
                    icon: 'warning',
                    position: 'center',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        mergeUsers();
                    }
                });
            }

            function mergeUsers() {

                var prev_user = $('#prev_user').val();
                var current_user = $('#current_user').val();

                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'merge_users_ajax',
                        prev_user: prev_user,
                        current_user: current_user
                    },

                    success: function(response) {
                        Swal.fire({
                            text: response.message,
                            icon: 'success',
                            position: 'center'
                        });
                        console.log(response);

                        var mergeUserTableHTML = '<table class="table">' +
                            '<thead><tr><th>Field</th><th>Previous User</th><th>Current User</th></tr></thead>' +
                            '<tbody>' +
                            '<tr><td>User ID</td><td>' + response.prev_user_data.ID + '</td><td>' + response.current_user_data.ID + '</td></tr>' +
                            '<tr><td>User Login</td><td>' + response.prev_user_data.user_login + '</td><td>' + response.current_user_data.user_login + '</td></tr>' +
                            '<tr><td>User Email</td><td>' + response.prev_user_data.user_email + '</td><td>' + response.current_user_data.user_email + '</td></tr>' +
                            '<tr><td>Course Count</td><td>' + response.prev_user_data.course_count + '</td><td>' + response.current_user_data.course_count + '</td></tr>' +
                            '<tr><td>Certificate</td><td>' + response.prev_user_data.certificate + '</td><td>' + response.current_user_data.certificate + '</td></tr>' +
                            '</tbody></table>';

                        $('#prev_user_table').html(mergeUserTableHTML);
                        // $('#current_user_table').html(currentUserTableHTML);

                        $('#prev_user').val('').trigger('change');
                        $('#current_user').val('').trigger('change');
                    }
                });
            }

            $('#merge_users_button').on('click', function() {
                var prev_user = $('#prev_user').val();
                var current_user = $('#current_user').val();

                if (prev_user && current_user && prev_user !== current_user) {
                    showConfirmation();
                } else {
                    Swal.fire({
                        text: 'Please select users or different users for merging.',
                        icon: 'error',
                        position: 'center'
                    });
                }
            });
            $('#preview_users_button').on('click', function() {
                var prev_user = $('#prev_user').val();
                var current_user = $('#current_user').val();

                if (prev_user && current_user && prev_user !== current_user) {
                    previewUsers(prev_user, current_user);
                } else {
                    Swal.fire({
                        text: 'Please select users or different users for merging.',
                        icon: 'error',
                        position: 'center'
                    });
                }
            });

            function previewUsers(prev_user, current_user) {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'preview_users_ajax',
                        prev_user: prev_user,
                        current_user: current_user
                    },
                    success: function(response) {
                        if (response.success) {
                            var prevUserTableHTML = '<table class="table">' +
                                '<thead><tr><th>Field</th><th>Previous User</th><th>Current User</th></tr></thead>' +
                                '<tbody>' +
                                '<tr><td>User ID</td><td>' + response.prev_user_data.ID + '</td><td>' + response.current_user_data.ID + '</td></tr>' +
                                '<tr><td>User Login</td><td>' + response.prev_user_data.user_login + '</td><td>' + response.current_user_data.user_login + '</td></tr>' +
                                '<tr><td>User Email</td><td>' + response.prev_user_data.user_email + '</td><td>' + response.current_user_data.user_email + '</td></tr>' +
                                '<tr><td>Course Count</td><td>' + response.prev_user_data.course_count + '</td><td>' + response.current_user_data.course_count + '</td></tr>' +
                                '<tr><td>Certificates</td><td>' + response.prev_user_data.certifications + '</td><td>' + response.current_user_data.certifications + '</td></tr>' +
                                '</tbody></table>';
                            Swal.fire({
                                html: prevUserTableHTML,
                                title: 'Users Details',
                                position: 'center',
                                showCancelButton: true,
                                confirmButtonText: 'Merge Users',
                                cancelButtonText: 'Close',
                                customClass: 'preview-box'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    mergeUsers(prev_user, current_user);
                                }
                            });
                        } else {
                            Swal.fire({
                                text: response.message,
                                icon: 'error',
                                position: 'center'
                            });
                        }
                    }
                });
            }
            // $("#swap_users_button").on('click', function() {
            //     var prev_user = $('#prev_user').val();
            //     var current_user = $('#current_user').val();
            //     $('#prev_user').val(current_user).trigger('change');
            //     $('#current_user').val(prev_user).trigger('change');
            // });
        });
    </script>

<?php
}

add_action('wp_ajax_merge_users_ajax', 'merge_users_ajax');
function merge_users_ajax()
{
    $prev_user_id = intval($_POST['prev_user']);
    $current_user_id = intval($_POST['current_user']);

    merge_users($prev_user_id, $current_user_id);

    $prev_user_data = get_userdata($prev_user_id);
    $current_user_data = get_userdata($current_user_id);

    $prev_user_certificate_count = learndash_get_certificate_count($prev_user_data->ID);
    $current_certificate_count =  learndash_get_certificate_count($current_user_data->ID);


    $prev_user_enrolled_courses = learndash_user_get_enrolled_courses($prev_user_data->ID);
    $current_user_enrolled_courses = learndash_user_get_enrolled_courses($current_user_data->ID);

    $prev_user_active_course_count = count(array_filter($prev_user_enrolled_courses, function ($course_id) {
        $course_status = get_post_status($course_id);
        return $course_status === 'publish';
    }));

    $current_user_active_course_count = count(array_filter($current_user_enrolled_courses, function ($course_id) {
        $course_status = get_post_status($course_id);
        return $course_status === 'publish';
    }));

    $response = array(
        'success' => true,
        'message' => 'Users merged successfully.',
        'prev_user_data' => array(
            'ID' => $prev_user_data->ID,
            'user_login' => $prev_user_data->user_login,
            'user_email' => $prev_user_data->user_email,
            'course_count' => $prev_user_active_course_count,
            'certificate' => $prev_user_certificate_count
        ),
        'current_user_data' => array(
            'ID' => $current_user_data->ID,
            'user_login' => $current_user_data->user_login,
            'user_email' => $current_user_data->user_email,
            'course_count' => $current_user_active_course_count,
            'certificate' => $current_certificate_count
        )
    );

    wp_send_json($response);
}

add_action('wp_ajax_search_users_ajax', 'search_users_ajax');
function search_users_ajax()
{
    $search_term = $_GET['q'];
    $users = get_users(array(
        'number' => 10,
        'search' => '*' . $search_term . '*',
    ));

    $results = array();
    foreach ($users as $user) {
        $results[] = array(
            'id' => $user->ID,
            'text' => esc_html($user->user_email),
        );
    }
    wp_send_json($results);
}
add_action('wp_ajax_get_user_details_ajax', 'get_user_details_ajax');

function merge_users($prev_user_id, $current_user_id)
{
    global $wpdb;
    $prev_user_data = get_userdata($prev_user_id);
    $current_user_data = get_userdata($current_user_id);
    update_user_meta($prev_user_id, '_billing_email', $current_user_data->user_email);
    update_user_meta($prev_user_id, '_billing_first_name', $current_user_data->first_name);
    update_user_meta($prev_user_id, '_billing_last_name', $current_user_data->last_name);
    $wpdb->update(
        $wpdb->users,
        array(
            'user_login' => $prev_user_data->user_login,
            'user_email' => $prev_user_data->user_email,
            'user_nicename' => $prev_user_data->user_nicename,
            'display_name' => $prev_user_data->display_name,
            'user_url' => $prev_user_data->user_url,
            'user_registered' => $prev_user_data->user_registered,
        ),
        array('ID' => $prev_user_id)
    );
    merge_specific_user_meta($prev_user_id, $current_user_id, '_sfwd-course_progress');
    merge_learndash_user_activity($prev_user_id, $current_user_id);
    merge_course_access_fields($prev_user_id, $current_user_id);
    merge_quiz_details($prev_user_id, $current_user_id);
    merge_user_posts($prev_user_id, $current_user_id);
    merge_learndash_group_users_meta($prev_user_id, $current_user_id);
    merge_course_progress_field($prev_user_id, $current_user_id);


    $prev_course_count = count(learndash_user_get_enrolled_courses($prev_user_data->ID));
    $current_course_count = count(learndash_user_get_enrolled_courses($current_user_data->ID));

    $prev_certificate_count = learndash_get_certificate_count($prev_user_data->ID);
    $current_certificate_count = learndash_get_certificate_count($current_user_data->ID);

    $prev_group_count = count(learndash_get_groups_user_ids($prev_user_data->ID));
    $current_group_count = count(learndash_get_groups_user_ids($current_user_data->ID));

    $table_name = $wpdb->prefix . 'merge_details';
    $wpdb->insert(
        $table_name,
        array(
            'prev_username' => $prev_user_data->user_email,
            'current_username' => $current_user_data->user_email,
            'merge_date' => current_time('mysql'),
        )
    );


    $meta_table_name = $wpdb->prefix . 'merge_details_meta';
    $wpdb->insert(
        $meta_table_name,
        array(
            'prev_username' => $prev_user_data->user_email,
            'current_username' => $current_user_data->user_email,
            'prev_course_count' => $prev_course_count,
            'prev_certificate_count' => $prev_certificate_count,
            'prev_group_count' => $prev_group_count,
            'current_course_count' => $current_course_count,
            'current_certificate_count' => $current_certificate_count,
            'current_group_count' => $current_group_count,
        )
    );


    // wp_delete_user($prev_user_id);
}


function merge_course_progress_field($user1_id, $user2_id)
{
    $user1_course_progress = get_user_meta($user1_id, '_sfwd-course_progress', true);
    $user2_course_progress = get_user_meta($user2_id, '_sfwd-course_progress', true);

    foreach ($user1_course_progress as $course_id => $progress) {
        if (isset($user2_course_progress[$course_id]) && $user2_course_progress[$course_id] > $progress) {
            continue;
        }

        if (!isset($user2_course_progress[$course_id]) || $user2_course_progress[$course_id] < $progress) {
            $user2_course_progress[$course_id] = $progress;
        }
    }

    update_user_meta($user2_id, '_sfwd-course_progress', $user2_course_progress);
}
function merge_specific_user_meta($source_user_id, $target_user_id, $meta_key)
{
    $meta_value = get_user_meta($source_user_id, $meta_key, true);

    if (!empty($meta_value)) {
        update_user_meta($target_user_id, $meta_key, $meta_value);
    }
}

function merge_learndash_user_activity($source_user_id, $target_user_id)
{
    global $wpdb;
    $user_activity_table = $wpdb->prefix . 'learndash_user_activity';
    $user_activity_meta_table = $wpdb->prefix . 'learndash_user_activity_meta';
    $source_user_activity_data = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $user_activity_table WHERE user_id = %d",
            $source_user_id
        )
    );

    foreach ($source_user_activity_data as $activity) {
        $activity->user_id = $target_user_id;

        $wpdb->insert($user_activity_table, (array) $activity);
    }


    foreach ($source_user_activity_data as $activity) {
        $source_activity_meta_data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $user_activity_meta_table WHERE activity_id = %d",
                $activity->activity_id
            )
        );

        foreach ($source_activity_meta_data as $meta_data) {
            $meta_data->activity_id = $activity->activity_id;
            $wpdb->insert($user_activity_meta_table, (array) $meta_data);
        }
    }
}

function merge_learndash_group_users_meta($source_user_id, $target_user_id)
{
    $prev_user_meta_keys = get_user_meta($source_user_id);

    foreach ($prev_user_meta_keys as $meta_key => $meta_values) {
        if (preg_match('/learndash_group_users_(\d+)/', $meta_key, $matches)) {
            // $post_id = isset($matches[1]) ? intval($matches[1]) : 0;

            $meta_value = get_user_meta($source_user_id, $meta_key, true);

            update_user_meta($target_user_id, $meta_key, $meta_value);
        }
    }
}

function merge_quiz_details($source_user_id, $target_user_id)
{
    $meta_prefix = '_sfwd-quizzes';

    $prev_user_meta_keys = get_user_meta($source_user_id);

    foreach ($prev_user_meta_keys as $meta_key => $meta_values) {
        if (strpos($meta_key, $meta_prefix) === 0) {
            $quiz_details = get_user_meta($source_user_id, $meta_key, true);

            update_user_meta($target_user_id, $meta_key, $quiz_details);
        }
    }
}

function merge_user_posts($source_user_id, $target_user_id)
{
    global $wpdb;

    $prefix = $wpdb->prefix;

    $source_post_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT ID FROM {$prefix}posts WHERE post_author = %d",
            $source_user_id
        )
    );

    foreach ($source_post_ids as $source_post_id) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$prefix}posts (post_author, post_title, post_content, post_type) 
                 SELECT %d, post_title, post_content, post_type FROM {$prefix}posts WHERE ID = %d",
                $target_user_id,
                $source_post_id
            )
        );

        $new_post_id = $wpdb->insert_id;
        if ($new_post_id) {
            $source_post_meta = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT meta_key, meta_value FROM {$prefix}postmeta WHERE post_id = %d",
                    $source_post_id
                )
            );
            foreach ($source_post_meta as $meta) {
                $wpdb->query(
                    $wpdb->prepare(
                        "INSERT INTO {$prefix}postmeta (post_id, meta_key, meta_value) 
                        VALUES (%d, %s, %s)",
                        $new_post_id,
                        $meta->meta_key,
                        $meta->meta_value
                    )
                );
            }
        }
    }
}

function merge_course_access_fields($source_user_id, $target_user_id)
{
    $prev_user_meta_keys = get_user_meta($source_user_id);

    foreach ($prev_user_meta_keys as $meta_key => $meta_values) {
        if (strpos($meta_key, 'course_') !== false && strpos($meta_key, '_access_from') !== false) {
            $course_id = intval(str_replace(array('course_', '_access_from'), '', $meta_key));

            $access_from_value = get_user_meta($source_user_id, $meta_key, true);

            update_user_meta($target_user_id, $meta_key, $access_from_value);
        }
    }
}



add_action('wp_ajax_preview_users_ajax', 'preview_users_ajax');

function preview_users_ajax()
{
    $prev_user_id = intval($_POST['prev_user']);
    $current_user_id = intval($_POST['current_user']);

    $prev_user_data = get_userdata($prev_user_id);
    $current_user_data = get_userdata($current_user_id);

    $prev_user_courses_count = count(learndash_user_get_enrolled_courses($prev_user_data->ID));
    $current_user_courses_count = count(learndash_user_get_enrolled_courses($current_user_data->ID));

    $prev_user_groups_count = count(learndash_get_groups_user_ids($prev_user_data->ID));
    $current_user_groups_count = count(learndash_get_groups_user_ids($current_user_data->ID));
    $prev_certificate_count = learndash_get_certificate_count($prev_user_data->ID);
    $current_certificate_count = learndash_get_certificate_count($current_user_data->ID);

    $prev_user_enrolled_courses = learndash_user_get_enrolled_courses($prev_user_data->ID);
    $current_user_enrolled_courses = learndash_user_get_enrolled_courses($current_user_data->ID);

    $prev_user_active_course_count = count(array_filter($prev_user_enrolled_courses, function ($course_id) {
        $course_status = get_post_status($course_id);
        return $course_status === 'publish';
    }));

    $current_user_active_course_count = count(array_filter($current_user_enrolled_courses, function ($course_id) {
        $course_status = get_post_status($course_id);
        return $course_status === 'publish';
    }));

    $response = array(
        'success' => true,
        'prev_user_data' => array(
            'ID' => $prev_user_data->ID,
            'user_login' => $prev_user_data->user_login,
            'user_email' => $prev_user_data->user_email,
            'course_count' =>  $prev_user_active_course_count,
            'group_count' => $prev_user_groups_count,
            'certifications' => $prev_certificate_count
        ),
        'current_user_data' => array(
            'ID' => $current_user_data->ID,
            'user_login' => $current_user_data->user_login,
            'user_email' => $current_user_data->user_email,
            'course_count' => $current_user_active_course_count,
            'group_count' => $current_user_groups_count,
            'certifications' => $current_certificate_count

        )
    );

    wp_send_json($response);
}
