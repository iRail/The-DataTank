App = (function($) {
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
