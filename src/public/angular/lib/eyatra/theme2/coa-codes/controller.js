app.component('eyatraCoaCode', {
    templateUrl: eyatra_coa_code_list_template_url,
    controller: function(HelperService, $rootScope, $scope, $http) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        var dataTable = $('#eyatra_coa_code_table').DataTable({
            stateSave: true,
            "dom": dom_structure_separate,
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
                url: laravel_routes['listEYatraCoaCode'],
                type: "GET",
                dataType: "json",
                data: function(d) {}
            },
            columns: [
                { data: 'action', searchable: false, class: 'action', class: 'text-left' },
                { data: 'number', name: 'coa_codes.number', searchable: true },
                { data: 'account_description', name: 'coa_codes.account_description', searchable: true },
                { data: 'account_type', name: 'e.name', searchable: true },
                { data: 'normal_balance', name: 'e1.name', searchable: true },
                { data: 'description', name: 'coa_codes.description', searchable: true },
                { data: 'final_statement', name: 'e2.name', searchable: true },
                { data: 'group', name: 'e3.name', searchable: true },
                { data: 'sub_group', name: 'e4.name', searchable: true },
                { data: 'status', name: 'coa_codes.deleted_at', searchable: false },
            ],
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            }
        });
        $('.dataTables_length select').select2();
        $('.separate-page-header-content .data-table-title').html('<p class="breadcrumb">Masters / Coa Codes</p><h3 class="title">Coa Codes</h3>');
        // $('.page-header-content .display-inline-block .data-table-title').html('City');
        $('.add_new_button').html(
            '<a href="#!/eyatra/coa-code/add" type="button" class="btn btn-secondary" ng-show="$ctrl.hasPermission(\'add-coa-code\')">' +
            'Add New' +
            '</a>'
        );
        $scope.deleteCoaCodeConfirm = function($coa_code_id) {
            $("#del").val($coa_code_id);
        }

        $scope.deleteCoaCode = function() {
            $coa_code_id = $('#del').val();
            $http.get(
                coa_code_delete_url + '/' + $coa_code_id,
            ).then(function(response) {
                console.log(response.data);
                if (response.data.success) {
                    new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: 'Coa Code Deleted Successfully',
                    }).show();
                    dataTable.ajax.reload(function(json) {});

                } else {
                    new Noty({
                        type: 'error',
                        layout: 'topRight',
                        text: 'Coa Code not Deleted',
                    }).show();
                }
            });
        }
        $rootScope.loading = false;

    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------

app.component('eyatraCoaCodeForm', {
    templateUrl: coa_code_form_template_url,
    controller: function($http, $location, $location, HelperService, $routeParams, $rootScope, $scope) {
        $form_data_url = typeof($routeParams.coa_code_id) == 'undefined' ? coa_code_form_data_url : coa_code_form_data_url + '/' + $routeParams.coa_code_id;
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
                $location.path('/eyatra/coa-codes')
                $scope.$apply()
                return;
            }

            self.coacode = response.data.coacode;
            // self.state_list = response.data.state_list;
            self.status = response.data.status;
            self.extras = response.data.extras;
            self.action = response.data.action;

        });



        $('.btn-nxt').on("click", function() {
            $('.editDetails-tabs li.active').next().children('a').trigger("click");
        });
        $('.btn-prev').on("click", function() {
            $('.editDetails-tabs li.active').prev().children('a').trigger("click");
        });
        $('.btn-pills').on("click", function() {});
        $scope.btnNxt = function() {}
        $scope.prev = function() {}

        var form_id = '#coa-code-form';
        var v = jQuery(form_id).validate({
            errorPlacement: function(error, element) {
                if (element.hasClass("number")) {
                    error.appendTo($('.number_error'));
                } else {
                    error.insertAfter(element)
                }
            },
            invalidHandler: function(event, validator) {
                new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: 'You have errors,Please check all tabs'
                }).show();
            },
            ignore: '',
            rules: {

                'number': {
                    required: true,
                    number: true,
                    min: 1,
                },
                'account_description': {
                    required: true,
                    minlength: 3,
                    maxlength: 191,
                },
                'account_types': {
                    required: true,
                },
                'normal_balance': {
                    required: true,
                },
                'description': {
                    required: true,
                    minlength: 3,
                    maxlength: 191,
                },
                'final_statement': {
                    required: true,
                },
                'group': {
                    required: true,
                },

                'sub_group': {
                    required: true,
                },
            },
            messages: {
                'number': {
                    required: 'Coa Code Number Required',
                    number: 'Enter numbers only',
                },
                'account_description': {
                    required: 'Account Description Required',
                    minlength: 'Please enter minimum of 3 letters',
                    maxlength: 'Please enter maximum of 191 letters',
                },
                'account_types': {
                    required: 'Account Type Required',
                },
                'normal_balance': {
                    required: 'Normal Balance Required',
                },
                'description': {
                    required: 'Description required',
                    minlength: 'Please enter minimum of 3 letters',
                    maxlength: 'Please enter maximum of 191 letters',
                },
                'final_statement': {
                    required: 'Final Statement Required',
                },
                'group': {
                    required: 'Group Required',
                },
                'sub_group': {
                    required: 'Sub Group required',
                }
            },
            submitHandler: function(form) {

                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveEYatraCoaCode'],
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
                                text: 'Coa Code saved successfully',
                                text: res.message,
                            }).show();
                            $location.path('/eyatra/coa-codes')
                            $scope.$apply()
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        custom_noty('error', 'Something went wrong at server');
                    });
            },
        });
    }
});

app.component('eyatraCoaCodeView', {
    templateUrl: coa_code_view_template_url,

    controller: function($http, $location, $routeParams, HelperService, $scope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        $http.get(
            coa_code_view_url + '/' + $routeParams.coa_code_id
        ).then(function(response) {
            self.coacode = response.data.coacode;
            self.action = response.data.action;
        });
    }
});


//------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------