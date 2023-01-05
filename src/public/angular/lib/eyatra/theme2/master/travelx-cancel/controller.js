app.component('travelxAutoCancel', {
    templateUrl: 'public/angular/lib/eyatra/theme2/master/travelx-cancel/list.html',
    controller: function($http, HelperService, $rootScope, $scope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;

        var dataTable = $('#travelx_auto_cancel_table').DataTable({
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
                url: laravel_routes['listEYatraTravelxCancel'],
                type: "GET",
                dataType: "json",
                data: function(d) { }
            },
            columns: [
                { data: 'name', name: 'travelx_auto_cancel_details.name', searchable: true },
                { data: 'normal_days', name: 'travelx_auto_cancel_details.normal_days', searchable: true },
                { data: 'warning_days', name: 'travelx_auto_cancel_details.warning_days', searchable: true },
                { data: 'approve_cancel_days', name: 'travelx_auto_cancel_details.approve_cancel_days', searchable: true },
                { data: 'status', searchable: false },
            ],
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            }
        });
        $('.dataTables_length select').select2();
        setTimeout(function() {
            var x = $('.separate-page-header-inner.search .custom-filter').position();
            var d = document.getElementById('travelx_auto_cancel_table_filter');
            x.left = x.left + 15;
            d.style.left = x.left + 'px';
        }, 500);

        $rootScope.loading = false;
    }
});