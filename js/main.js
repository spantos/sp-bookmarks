$(document).ready(function () {
    $('#modal-group').click(function () {
        var group_message = $('#group-message');
        var modal_group = $('#modalGroup');
        group_message.text(' ');
        var group_name = $('#group-name').val();
        group_name = group_name.trim();
        if (group_name.length == false) {
            group_message.text('Enter the name of the group.');
            return false;
        }
        var group_action = modal_group.find('#modal-group').attr('value');
        switch (group_action) {
            case 'Add Group':
                $.ajax({
                    cache: false,
                    url: 'add.php',
                    type: 'POST',
                    dataType: 'text',
                    data: {
                        gr_name: group_name
                    },
                    success: function (data) {
                        group_message.text(data);
                    },
                    error: function (textStatus) {
                        group_message.text('Sorry, there was a problem!');
                    }
                });
                break;
            case 'Save':
                var id_group = modal_group.find('[name=id_group]').val();
                $.ajax({
                    cache: false,
                    url: 'edit.php',
                    type: 'POST',
                    dataType: 'text',
                    data: {
                        edit_id_group: id_group,
                        new_group_name: group_name
                    },
                    success: function (data) {
                        modal_group.find('#group-name').attr('disabled', true);
                        $('#modal-group').attr('disabled', true);
                        modal_group.find('#group-message').text(data);
                    },
                    error: function () {
                        modal_group.find('#group-message').text('Sorry, an error occurred on the server!');
                    }
                });
                break;
            case 'Delete group':
                id_group = modal_group.find('[name=id_group]').val();
                $.ajax({
                    cache: false,
                    url: 'edit.php',
                    type: 'POST',
                    dataType: 'text',
                    data: {
                        del_id_group: id_group
                    },
                    success: function (data) {
                        modal_group.find('#group-name').attr('disabled', true);
                        $('#modal-group').attr('disabled', true);
                        modal_group.find('#group-message').text(data);
                    },
                    error: function () {
                        modal_group.find('#group-message').text('Sorry, an error occurred on the server!');
                    }
                });
                break;
        }
    });
    //####################################################################################################
    $('#modal-bookmark').click(function () {
        var bookmark_message = $('#bookmark-message');
        bookmark_message.text(' ');
        var bookmark_name = $('#bookmark-name').val();
        bookmark_name.trim();
        var bookmark_url = $('#bookmark-url').val();
        bookmark_url.trim();
        var bookmark_description = $('#bookmark-description').val();
        bookmark_description.trim();
        var bookmark_group = $('#bookmark-group').val();
        var bookmark_modal = $('#modalBookmark');
        var bookmark_id = bookmark_modal.find('#bookmark_id').val();
        if (bookmark_name.length == false) {
            bookmark_message.text('Enter the bookmark name.');
            return;
        } else if (bookmark_url.length == false) {
            bookmark_message.text('Enter the bookmark URL.');
            return;
        } else if (bookmark_group == null) {
            bookmark_message.text('Select the group.');
            return;
        }
        var bookmark_action = bookmark_modal.find('#modal-bookmark').attr('value');
        switch (bookmark_action) {
            case 'Add Bookmark':
                $.ajax({
                    cache: false,
                    url: 'add.php',
                    type: 'POST',
                    dataType: 'text',
                    data: {
                        bookmark_name: bookmark_name,
                        bookmark_url: bookmark_url,
                        bookmark_description: bookmark_description,
                        bookmark_group: bookmark_group
                    },
                    success: function (response) {
                        bookmark_message.text(response);
                    },
                    error: function () {
                        bookmark_message.text('Sorry, there was a problem!');
                    }
                });
                break;
            case 'Delete Bookmark':
                $.ajax({
                    cache: false,
                    url: 'edit.php',
                    type: 'POST',
                    dataType: 'text',
                    data: {
                        delete_bookmark_id: bookmark_id
                    },
                    success: function (response) {
                        bookmark_message.text(response);
                        bookmark_modal.find('#modal-bookmark').attr('disabled', true);
                    },
                    error: function () {
                        $('#bookmark-message').text('Sorry, there was a problem!');
                    }
                });
                break;
            case 'Edit Bookmark':
                $.ajax({
                    cache: false,
                    url: 'edit.php',
                    type: 'POST',
                    dataType: 'text',
                    data: {
                        edit_bookmark_id: bookmark_id,
                        bookmark_name: bookmark_name,
                        bookmark_url: bookmark_url,
                        bookmark_description: bookmark_description,
                        bookmark_id_group: bookmark_group
                    },
                    success: function (response) {
                        bookmark_message.text(response);
                        bookmark_modal.find('#modal-bookmark').attr('disabled', true);
                    },
                    error: function () {
                        bookmark_message.text('Sorry, there was a problem!');
                    }
                });
                break;
        }
    });
});
//#####################################################################################################

function editGroup(id_group, group_name) {
    var modal_group = $('#modalGroup');
    modal_group.find('#modalGroupLabel').text('Edit the group name');
    modal_group.find('label[for=group-name]').text('Enter the new name');
    modal_group.find('#modal-group').attr('value', 'Save');
    modal_group.find('.modal-footer').append('<input type=hidden name=id_group />');
    modal_group.find('[name=id_group]').val(id_group);
    modal_group.find('#group-name').attr('placeholder', group_name);
    modal_group.modal('show');
}

function deleteGroup(id_group, group_name) {
    var modal_group = $('#modalGroup');
    modal_group.find('#modalGroupLabel').text('Delete group');
    modal_group.find('label[for=group-name]').text('');
    modal_group.find('#group-name').attr('value', group_name);
    modal_group.find('#group-name').attr('disabled', true);
    modal_group.find('#modal-group').attr('value', 'Delete group');
    modal_group.find('.modal-footer').append('<input type=hidden name=id_group />');
    modal_group.find('[name=id_group]').val(id_group);
    modal_group.modal('show');
}

function deleteBookmark(bookmark_id) {
    $.ajax({
        cache: false,
        url: 'edit.php',
        type: 'POST',
        dataType: 'json',
        data: {
            get_bookmark: bookmark_id
        },
        success: function (bookmark_data) {
            var modal_bookmark = $('#modalBookmark');
            modal_bookmark.find('#modalBookmarkLabel').text('Delete Bookmark');
            modal_bookmark.find('#bookmark-name').val(bookmark_data.bookmark_name);
            modal_bookmark.find('#bookmark-name').attr('disabled', true);
            modal_bookmark.find('#bookmark-url').val(bookmark_data.bookmark_url);
            modal_bookmark.find('#bookmark-url').attr('disabled', true);
            modal_bookmark.find('#bookmark-description').val(bookmark_data.bookmark_description);
            modal_bookmark.find('#bookmark-description').attr('disabled', true);
            var opt = modal_bookmark.find('#bookmark-group');
            opt.find('option[value="' + bookmark_data.id_group + '"]').attr('selected', true);
            opt.attr('disabled', true);
            modal_bookmark.find('.modal-footer').append('<input type=hidden id=bookmark_id value=' + bookmark_data.id_bookmark + ' />');
            modal_bookmark.find('#modal-bookmark').attr('value', 'Delete Bookmark');
            modal_bookmark.modal('show');
        },
        error: function () {

        }
    });
}

function editBookmark(bookmark_id) {
    $.ajax({
        cache: false,
        url: 'edit.php',
        type: 'POST',
        dataType: 'json',
        data: {
            get_bookmark: bookmark_id
        },
        success: function (bookmark_data) {
            $('#modalBookmarkLabel').text('Edit Bookmark');
            $('#bookmark-name').val(bookmark_data.bookmark_name);
            $('#bookmark-url').val(bookmark_data.bookmark_url);
            $('#bookmark-description').val(bookmark_data.bookmark_description);
            $('#bookmark-group option[value="' + bookmark_data.id_group + '"]').attr('selected', true);
            $('#modalBookmark .modal-footer').append('<input type=hidden id=bookmark_id value=' + bookmark_data.id_bookmark + ' />');
            $('#modal-bookmark').attr('value', 'Edit Bookmark');
            $('#modalBookmark').modal('show');
        },
        error: function () {

        }
    });
}