app.component('masterSms', {
    templateUrl: 'public/angular/lib/eyatra/theme2/master/sms/list.html',
    controller: function($http, HelperService, $rootScope, $scope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;

        var dataTable = $('#sms_table').DataTable({
            stateSave: true,
            "dom": dom_structure_separate_2,
            "language": {
                "search": "",
                "searchPlaceholder": "Search",
                "lengthMenu": "Rows Per Page _MENU_",
                "paginate": {
                    "next": '<i class="icon ion-ios-arrow-forward"></i>',
                    "previous": '<i class="icon ion-ios-arrow-back"></i>'
                },
            },
            pageLength: 10,
            processing: true,
            serverSide: true,
            paging: true,
            ordering: false,
            ajax: {
                url: laravel_routes['listEYatraMasterSms'],
                type: "GET",
                dataType: "json",
                data: function(d) { }
            },
            columns: [
                { data: 'type', name: 'type', searchable: true },
                { data: 'content', searchable: false },
            ],
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            }
        });
        $('.dataTables_length select').select2();
        setTimeout(function() {
            var x = $('.separate-page-header-inner.search .custom-filter').position();
            var d = document.getElementById('sms_table_filter');
            x.left = x.left + 15;
            d.style.left = x.left + 'px';
        }, 500);

        $rootScope.loading = false;
    }
});