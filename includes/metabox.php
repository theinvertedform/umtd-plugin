<?php
/**
 * Agent–role meta box for umtd_works.
 *
 * Replaces the ACF agents/agents_artists/agents_authors relationship fields
 * with a custom UI that writes directly to umtd_work_agents. Each row is an
 * agent (autocomplete search against umtd_agents posts) paired with a role
 * select (from wp_umtd_roles).
 *
 * Reads existing rows from umtd_work_agents on load. Saves on post save via
 * save_post at priority 20 — after ACF save at priority 10.
 *
 * @package umtd-plugin
 * @see includes/db.php — umtd_get_agent_id(), umtd_get_work_id()
 * @see includes/save.php — scalar field intercepts
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the meta box.
 */
add_action( 'add_meta_boxes', function() {
    add_meta_box(
        'umtd_work_agents',
        __( 'Agents', 'umtd' ),
        'umtd_render_work_agents_metabox',
        'umtd_works',
        'normal',
        'high'
    );
} );

/**
 * Render the agent–role meta box.
 *
 * Outputs existing rows from umtd_work_agents plus one empty row for new
 * entries. JavaScript handles adding additional rows client-side.
 *
 * @param WP_Post $post
 */
function umtd_render_work_agents_metabox( $post ) {
    global $wpdb;

    wp_nonce_field( 'umtd_work_agents_save', 'umtd_work_agents_nonce' );

    // Existing rows.
    $rows = $wpdb->get_results( $wpdb->prepare(
        "SELECT wa.sort_order, wa.role_id, a.post_id AS agent_post_id
         FROM {$wpdb->prefix}umtd_work_agents wa
         JOIN {$wpdb->prefix}umtd_works w  ON w.id  = wa.work_id
         JOIN {$wpdb->prefix}umtd_agents a ON a.id  = wa.agent_id
         WHERE w.post_id = %d
         ORDER BY wa.sort_order ASC",
        $post->ID
    ) );

    // All roles for the select.
    $roles = $wpdb->get_results(
        "SELECT id, slug, label_en FROM {$wpdb->prefix}umtd_roles ORDER BY label_en ASC"
    );

    ?>
    <div id="umtd-agents-wrap">
        <table id="umtd-agents-table" class="widefat">
            <thead>
                <tr>
                    <th><?php _e( 'Agent', 'umtd' ); ?></th>
                    <th><?php _e( 'Role', 'umtd' ); ?></th>
                    <th><?php _e( 'Order', 'umtd' ); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="umtd-agents-rows">
                <?php
                $existing = ! empty( $rows ) ? $rows : array();
                // Always append one empty row.
                $existing[] = (object) array(
                    'agent_post_id' => '',
                    'role_id'       => '',
                    'sort_order'    => count( $existing ),
                );
                foreach ( $existing as $i => $row ) :
                    $agent_title = $row->agent_post_id
                        ? get_field( 'name_display', (int) $row->agent_post_id )
                        : '';
                ?>
                <tr class="umtd-agent-row">
                    <td>
                        <input type="hidden"
                               name="umtd_agents[<?php echo $i; ?>][post_id]"
                               class="umtd-agent-post-id"
                               value="<?php echo esc_attr( $row->agent_post_id ); ?>" />
                        <input type="text"
                               class="umtd-agent-search"
                               placeholder="<?php esc_attr_e( 'Search agents…', 'umtd' ); ?>"
                               value="<?php echo esc_attr( $agent_title ); ?>"
                               autocomplete="off" />
                        <ul class="umtd-agent-suggestions" style="display:none;"></ul>
                    </td>
                    <td>
                        <select name="umtd_agents[<?php echo $i; ?>][role_id]">
                            <option value=""><?php _e( '— select role —', 'umtd' ); ?></option>
                            <?php foreach ( $roles as $role ) : ?>
                                <option value="<?php echo esc_attr( $role->id ); ?>"
                                    <?php selected( $role->id, $row->role_id ); ?>>
                                    <?php echo esc_html( $role->label_en ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input type="number"
                               name="umtd_agents[<?php echo $i; ?>][sort_order]"
                               value="<?php echo esc_attr( $row->sort_order ); ?>"
                               style="width:50px;" />
                    </td>
                    <td>
                        <button type="button" class="button umtd-remove-agent">
                            <?php _e( 'Remove', 'umtd' ); ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p>
            <button type="button" class="button" id="umtd-add-agent">
                <?php _e( '+ Add Agent', 'umtd' ); ?>
            </button>
        </p>
    </div>

    <script>
    (function($) {
        var rowIndex = <?php echo count( $existing ); ?>;
        var roles    = <?php echo wp_json_encode( $roles ); ?>;

        // Build role options HTML once.
        var roleOptions = '<option value="">— select role —</option>';
        $.each(roles, function(i, r) {
            roleOptions += '<option value="' + r.id + '">' + r.label_en + '</option>';
        });

        // Add row.
        $('#umtd-add-agent').on('click', function() {
            var row = '<tr class="umtd-agent-row">'
                + '<td>'
                + '<input type="hidden" name="umtd_agents[' + rowIndex + '][post_id]" class="umtd-agent-post-id" value="" />'
                + '<input type="text" class="umtd-agent-search" placeholder="Search agents…" autocomplete="off" />'
                + '<ul class="umtd-agent-suggestions" style="display:none;"></ul>'
                + '</td>'
                + '<td><select name="umtd_agents[' + rowIndex + '][role_id]">' + roleOptions + '</select></td>'
                + '<td><input type="number" name="umtd_agents[' + rowIndex + '][sort_order]" value="' + rowIndex + '" style="width:50px;" /></td>'
                + '<td><button type="button" class="button umtd-remove-agent">Remove</button></td>'
                + '</tr>';
            $('#umtd-agents-rows').append(row);
            rowIndex++;
        });

        // Remove row.
        $('#umtd-agents-rows').on('click', '.umtd-remove-agent', function() {
            $(this).closest('tr').remove();
        });

        // Agent search autocomplete.
        $('#umtd-agents-rows').on('input', '.umtd-agent-search', function() {
            var $input      = $(this);
            var $hidden     = $input.siblings('.umtd-agent-post-id');
            var $suggestions = $input.siblings('.umtd-agent-suggestions');
            var term        = $input.val();

            if ( term.length < 2 ) {
                $suggestions.hide().empty();
                return;
            }

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action:   'umtd_search_agents',
                    term:     term,
                    nonce:    '<?php echo wp_create_nonce( "umtd_search_agents" ); ?>',
                },
                success: function(response) {
                    $suggestions.empty();
                    if ( ! response.success || ! response.data.length ) {
                        $suggestions.hide();
                        return;
                    }
                    $.each(response.data, function(i, agent) {
                        $suggestions.append(
                            $('<li>')
                                .text(agent.label)
                                .attr('data-id', agent.id)
                        );
                    });
                    $suggestions.show();
                }
            });
        });

        // Select suggestion.
        $('#umtd-agents-rows').on('click', '.umtd-agent-suggestions li', function() {
            var $li          = $(this);
            var $suggestions = $li.parent();
            var $row         = $suggestions.closest('tr');
            $row.find('.umtd-agent-post-id').val( $li.data('id') );
            $row.find('.umtd-agent-search').val( $li.text() );
            $suggestions.hide().empty();
        });

        // Hide suggestions on outside click.
        $(document).on('click', function(e) {
            if ( ! $(e.target).closest('.umtd-agent-row').length ) {
                $('.umtd-agent-suggestions').hide().empty();
            }
        });

    })(jQuery);
    </script>

    <style>
        #umtd-agents-table { border-collapse: collapse; margin-bottom: 8px; }
        #umtd-agents-table th,
        #umtd-agents-table td { padding: 6px 8px; vertical-align: top; }
        .umtd-agent-search { width: 100%; min-width: 200px; }
        .umtd-agent-suggestions {
            position: absolute;
            background: #fff;
            border: 1px solid #ccc;
            list-style: none;
            margin: 0;
            padding: 0;
            min-width: 200px;
            z-index: 9999;
        }
        .umtd-agent-suggestions li {
            padding: 6px 10px;
            cursor: pointer;
        }
        .umtd-agent-suggestions li:hover { background: #f0f0f1; }
    </style>
    <?php
}

/**
 * AJAX handler — search agents by name_display.
 *
 * Returns up to 20 agents matching the search term. Searches against
 * post_title (the sort key) and the name_display postmeta field.
 *
 * @return void JSON response.
 */
add_action( 'wp_ajax_umtd_search_agents', function() {
    check_ajax_referer( 'umtd_search_agents', 'nonce' );

    $term = sanitize_text_field( $_POST['term'] ?? '' );
    if ( strlen( $term ) < 2 ) {
        wp_send_json_error();
    }

    $query = new WP_Query( array(
        'post_type'      => 'umtd_agents',
        'post_status'    => 'publish',
        'posts_per_page' => 20,
        's'              => $term,
    ) );

    $results = array();
    foreach ( $query->posts as $post ) {
        $display = get_field( 'name_display', $post->ID ) ?: $post->post_title;
        $results[] = array(
            'id'    => $post->ID,
            'label' => $display,
        );
    }

    wp_send_json_success( $results );
} );

/**
 * Save agent–role rows to umtd_work_agents.
 *
 * Deletes all existing rows for this work then reinserts from POST data.
 * Skips rows with no agent post_id or no role_id. Resolves agent post_id
 * to umtd_agents.id and work post_id to umtd_works.id before insert.
 *
 * Runs at priority 20 — after ACF save and after the Works scalar intercept
 * in save.php, which ensures the umtd_works row exists before we FK to it.
 *
 * @param int $post_id
 */
add_action( 'acf/save_post', function( $post_id ) {
    if ( get_post_type( $post_id ) !== 'umtd_works' ) {
        return;
    }
    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
        return;
    }
    if ( ! isset( $_POST['umtd_work_agents_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['umtd_work_agents_nonce'], 'umtd_work_agents_save' ) ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    global $wpdb;
    $table    = $wpdb->prefix . 'umtd_work_agents';
    $work_id  = umtd_get_work_id( $post_id );

    if ( ! $work_id ) {
        // umtd_works row not yet created — scalar intercept in save.php runs
        // on acf/save_post which fires before save_post. Should not happen
        // in normal flow but guard anyway.
        return;
    }

    // Delete existing rows for this work.
    $wpdb->delete( $table, array( 'work_id' => $work_id ), array( '%d' ) );

    $rows = $_POST['umtd_agents'] ?? array();
    if ( empty( $rows ) ) {
        return;
    }

    foreach ( $rows as $row ) {
        $agent_post_id = (int) ( $row['post_id']    ?? 0 );
        $role_id       = (int) ( $row['role_id']    ?? 0 );
        $sort_order    = (int) ( $row['sort_order'] ?? 0 );

        if ( ! $agent_post_id || ! $role_id ) {
            continue;
        }

        $agent_id = umtd_get_agent_id( $agent_post_id );
        if ( ! $agent_id ) {
            continue;
        }

        $wpdb->insert( $table, array(
            'work_id'    => $work_id,
            'agent_id'   => $agent_id,
            'role_id'    => $role_id,
            'sort_order' => $sort_order,
        ), array( '%d', '%d', '%d', '%d' ) );
    }
}, 30 );
