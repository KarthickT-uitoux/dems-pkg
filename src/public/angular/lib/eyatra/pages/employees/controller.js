app.component('eyatraEmployees', {
    templateUrl: eyatra_employee_list_template_url,
    controller: function(HelperService, $rootScope, $http, $scope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        var dataTable = $('#eyatra_employee_table').DataTable({
            stateSave: true,
            "dom": dom_structure,
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
                url: laravel_routes['listEYatraEmployee'],
                type: "GET",
                dataType: "json",
                data: function(d) {}
            },

            columns: [
                { data: 'action', searchable: false, class: 'action' },
                { data: 'code', name: 'e.code', searchable: true },
                { data: 'name', name: 'e.name', searchable: true },
                { data: 'outlet_code', name: 'o.code', searchable: true },
                { data: 'manager_code', name: 'm.code', searchable: true },
                { data: 'grade', name: 'grd.name', searchable: true },
                { data: 'status', name: 'c.name', searchable: false },
            ],
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            }
        });
        $('.dataTables_length select').select2();
        $('.page-header-content .display-inline-block .data-table-title').html('Employees');
        $('.add_new_button').html(
            '<a href="#!/eyatra/employee/add" type="button" class="btn btn-secondary" ng-show="$ctrl.hasPermission(\'add-trip\')">' +
            'Add New' +
            '</a>'
        );

        $scope.deleteEmployee = function(id) {
            $('#del').val(id);
        }
        $scope.confirmDeleteEmployee = function() {
            $id = $('#del').val();
            $http.get(
                employee_delete_url + '/' + $id,
            ).then(function(response) {
                if (!response.data.success) {
                    var errors = '';
                    for (var i in res.errors) {
                        errors += '<li>' + res.errors[i] + '</li>';
                    }
                    new Noty({
                        type: 'error',
                        layout: 'topRight',
                        text: errors
                    }).show();
                } else {
                    new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: 'Employee Deleted Successfully',
                    }).show();
                    $('#delete_emp').modal('hide');
                    dataTable.ajax.reload(function(json) {});
                }

            });
        }

        $rootScope.loading = false;

    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------

app.component('eyatraEmployeeForm', {
    templateUrl: employee_form_template_url,
    controller: function($http, $location, $location, HelperService, $routeParams, $rootScope, $scope) {
        $form_data_url = typeof($routeParams.employee_id) == 'undefined' ? employee_form_data_url : employee_form_data_url + '/' + $routeParams.employee_id;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        $http.get(
            $form_data_url
        ).then(function(response) {
            if (!response.data.success) {
                new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: response.data.error,
                }).show();
                $location.path('/eyatra/employees')
                $scope.$apply()
                return;
            }
            self.employee = response.data.employee;
            self.extras = response.data.extras;
            self.action = response.data.action;
            $rootScope.loading = false;

        });

        var form_id = '#employee_form';
        var v = jQuery(form_id).validate({
            errorPlacement: function(error, element) {
                error.insertAfter(element)
            },
            ignore: '',
            rules: {
                'code': {
                    required: true,
                    maxlength: 191,
                },
                'name': {
                    required: true,
                    maxlength: 80,
                },
                'outlet_id': {
                    required: true,
                },
                'reporting_to_id': {
                    required: true,
                },
                'grade_id': {
                    required: true,
                },
                'bank_name': {
                    required: true,
                    maxlength: 100,
                },
                'branch_name': {
                    required: true,
                    maxlength: 50,
                },
                'account_number': {
                    required: true,
                    maxlength: 20,
                },
                'ifsc_code': {
                    required: true,
                    maxlength: 10,
                },
            },
            messages: {
                'code': {
                    maxlength: 'Please enter maximum of 191 letters',
                },
                'name': {
                    maxlength: 'Please enter maximum of 80 letters',
                },
            },
            submitHandler: function(form) {

                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveEYatraEmployee'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        console.log(res.success);
                        if (!res.success) {
                            $('#submit').button('reset');
                            var errors = '';
                            for (var i in res.errors) {
                                errors += '<li>' + res.errors[i] + '</li>';
                            }
                            custom_noty('error', errors);
                        } else {
                            new Noty({
                                type: 'success',
                                layout: 'topRight',
                                text: 'Employee saved successfully',
                            }).show();
                            $location.path('/eyatra/employees')
                            $scope.$apply()
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        custom_noty('error', 'Something went wrong at server');
                    });
            },
        });

        //SEARCH MANAGER
        self.searchManager = function(query) {
            if (query) {
                return new Promise(function(resolve, reject) {
                    $http
                        .post(
                            search_manager_url, {
                                key: query,
                            }
                        )
                        .then(function(response) {
                            console.log(response.data);
                            resolve(response.data);
                        });
                    //reject(response);
                });
            } else {
                return [];
            }
        }
    }
});

app.component('eyatraEmployeeView', {
    templateUrl: employee_view_template_url,
    controller: function($http, $location, $routeParams, HelperService, $scope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        $http.get(
            employee_view_url + '/' + $routeParams.employee_id
        ).then(function(response) {
            self.employee = response.data.employee;
        });
    }
});


//------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------