/**
 * Agent–role meta box UI for umtd_works.
 *
 * Handles:
 * - Adding/removing agent rows
 * - Autocomplete agent search via AJAX
 * - Selecting agents from search results
 *
 * Expects umtdMetaboxAgents object localized with:
 *   - roles: array of {id, slug, label_en}
 *   - nonce: AJAX nonce for agent search
 *   - ajaxurl: WordPress AJAX endpoint
 *
 * @package umtd-plugin
 */

(function($) {
    'use strict';

    if (typeof umtdMetaboxAgents === 'undefined') {
        console.error('umtdMetaboxAgents not defined');
        return;
    }

    var rowIndex = umtdMetaboxAgents.rowIndex || 0;
    var roles    = umtdMetaboxAgents.roles || [];
    var nonce    = umtdMetaboxAgents.nonce;
    var ajaxurl  = umtdMetaboxAgents.ajaxurl;

    // Build role options HTML once
    var roleOptions = '<option value="">— select role —</option>';
    $.each(roles, function(i, r) {
        roleOptions += '<option value="' + r.id + '">' + r.label_en + '</option>';
    });

    /**
     * Add a new agent row.
     */
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

    /**
     * Remove an agent row.
     */
    $('#umtd-agents-rows').on('click', '.umtd-remove-agent', function() {
        $(this).closest('tr').remove();
    });

    /**
     * Agent autocomplete search via AJAX.
     */
    $('#umtd-agents-rows').on('input', '.umtd-agent-search', function() {
        var $input       = $(this);
        var $hidden      = $input.siblings('.umtd-agent-post-id');
        var $suggestions = $input.siblings('.umtd-agent-suggestions');
        var term         = $input.val();

        if (term.length < 2) {
            $suggestions.hide().empty();
            return;
        }

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'umtd_search_agents',
                term:   term,
                nonce:  nonce
            },
            success: function(response) {
                $suggestions.empty();
                if (!response.success || !response.data.length) {
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

    /**
     * Select an agent from autocomplete suggestions.
     */
    $('#umtd-agents-rows').on('click', '.umtd-agent-suggestions li', function() {
        var $li          = $(this);
        var $suggestions = $li.parent();
        var $row         = $suggestions.closest('tr');
        $row.find('.umtd-agent-post-id').val($li.data('id'));
        $row.find('.umtd-agent-search').val($li.text());
        $suggestions.hide().empty();
    });

    /**
     * Hide suggestions on outside click.
     */
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.umtd-agent-row').length) {
            $('.umtd-agent-suggestions').hide().empty();
        }
    });

})(jQuery);
