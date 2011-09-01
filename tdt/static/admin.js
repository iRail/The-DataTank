App = (function($) {
    Workspace = Backbone.Router.extend({
        routes: {
            '': 'admin',
            'module': 'module',
            'module/:id': 'module_detail',
            'module/:id/edit': 'module_edit',
            'resource': 'resource',
            'resource/:id': 'resource_detail',
            'resource/:id/edit': 'resource_edit',
            'statistics': 'statistics',
            'profile': 'profile',
            'profile/edit': 'profile_edit'
        },

        admin: function {},
        module: function {},
        module_detail: function {},
        module_edit: function {},
        resource: function {},
        resource_detail: function {},
        resource_edit: function {},
        statistics: function {},
        profile: function {},
        profile_edit: function {},
    });

    Module = Backbone.Model.extend({
        
    });
    
    Resource = Backbone.Model.extend({
        
    });

    var AdminAddResource = Backbone.Model.extend({

    });
});

$(document).ready(function() {
    var upload = $('#admin-upload');
    var file = $('#admin-file');
    var uploadComputer = $('#admin-upload-computer');
    var uploadLink = $('#admin-upload-link');
    var removeUpload = $('#admin-remove-upload')

    uploadComputer.click(function() {
        upload.addClass('hidden');
        file.removeClass('hidden');
    });

    uploadLink.click(function() {
        upload.addClass('hidden');
        file.removeClass('hidden');
    });

    removeUpload.click(function() {
        file.addClass('hidden');
        upload.removeClass('hidden');
    });

    $('#admin-modules').tablesorter({ sortList: [[1,0]] });
});
