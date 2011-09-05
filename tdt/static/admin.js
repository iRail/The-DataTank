var App = function($, _, Backbone) {
    var Router = Backbone.Router.extend({
        routes: {
            '': 'index',
        },
        index: function() {},
    });

    Backbone.history.start();
};

$(document).ready(function() {
    var resourcePage = $('a[href="#admin-resources"');
    resourcePage.click(function() {
        alert('hello');
        $('#admin-resource').removeClass('hidden');
    });

    var app = App(jQuery, _, Backbone);
});
