
<?php
function deleted_users_page_content()
{
    global $wpdb;

    $per_page = 10; 
    $current_page = isset($_GET['paged']) ? absint($_GET['paged']) : 1; 

    $offset = ($current_page - 1) * $per_page;

    $deleted_users = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}merge_details LIMIT $per_page OFFSET $offset");

    echo '<div class="wrap">';
    echo '<h2>Deleted Users Details</h2>';
    if (!empty($deleted_users)) {
        echo '<table class="widefat">';
        echo '<thead><tr><th>Previous Username</th><th>Current Username</th><th>Merged Date</th><th>More Details</th></tr></thead>';
        echo '<tbody>';
        foreach ($deleted_users as $user) {
            echo '<tr>';
            echo '<td class="prev-username">' . $user->prev_username . '</td>';
            echo '<td>' . $user->current_username . '</td>';
            echo '<td>' . $user->merge_date . '</td>';
            echo '<td><button class="btn btn-primary view-more-btn">View More</button></td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';

        $total_users = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}merge_details");
        $total_pages = ceil($total_users / $per_page);

        if ($total_pages > 1) {
            $pagination_args = array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'total' => $total_pages,
                'current' => $current_page,
                'prev_text' => '&laquo; Prev',
                'next_text' => 'Next &raquo;'
            );
            echo '<div class="pagination">';
            echo paginate_links($pagination_args);
            echo '</div>';
        }
    } else {
        echo '<p>No deleted users found</p>';
    }
    echo '</div>';
?>

    <script>
        jQuery(document).ready(function($) {
            $('.view-more-btn').on('click', function() {
                var prev_username = $(this).closest('tr').find('.prev-username').text();

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'get_deleted_user_details',
                        prev_username: prev_username
                    },
                    success: function(response) {
                        if (response) {
                            var detailsHtml = '<table class="table">' +
                                '<thead><tr><th>Field</th><th>Value</th></tr></thead>' +
                                '<tbody>' +
                                '<tr><td>Previous Username:</</td><td>' + response.prev_username + '</td></tr>' +
                                //'<tr><td>Merged Date:</td><td>' + response.merge_date + '</td></tr>' +
                                '<tr><td>Previous Course Count:</td><td>' + response.prev_course_count + '</td></tr>' +
                                '<tr><td>Previous Certificate Count:</td><td>' + response.prev_certificate_count + '</td></tr>' +
                                '<tr><td>Previous Group Count:</td><td>' + response.prev_group_count + '</td></tr>' +
                                '</tbody></table>';

                            Swal.fire({
                                html: detailsHtml,
                                title: 'Deleted User Details',
                                position: 'center',
                                showCancelButton: false,
                                confirmButtonText: 'Close',
                                customClass: 'preview-box'
                            });
                        } else {
                            Swal.fire({
                                text: 'No details found for the selected user.',
                                icon: 'error',
                                position: 'center'
                            });
                        }
                    }
                });
            });
        });
    </script>
<?php
}

add_action('wp_ajax_get_deleted_user_details', 'get_deleted_user_details');

function get_deleted_user_details()
{
    global $wpdb;

    $prev_username = $_POST['prev_username'];

    $meta_table_name = $wpdb->prefix . 'merge_details_meta';
    $deleted_user_details = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $meta_table_name WHERE prev_username = %s",
            $prev_username
        )
    );

    wp_send_json($deleted_user_details);
}

?>