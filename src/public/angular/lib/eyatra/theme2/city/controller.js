app.component('eyatraCity', {
    templateUrl: eyatra_city_list_template_url,
    controller: function(HelperService, $rootScope, $scope, $http) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.permissionadd = self.hasPermission('eyatra-city-add');
        self.permissionimport = self.hasPermission('eyatra-import-city');
        var dataTable = $('#eyatra_city_table').DataTable({
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
                url: laravel_routes['listEYatraCity'],
                type: "GET",
                dataType: "json",
                data: function(d) {
                    d.country = $('#country_id').val();
                    d.state_id = $('#state_id').val();
                    d.status = $('#status').val();
                }
            },
            columns: [
                { data: 'action', searchable: false, class: 'action', class: 'text-left' },
                { data: 'city_name', name: 'ncities.name', searchable: true },
                { data: 'name', name: 'entities.name', searchable: true },
                { data: 'state_name', name: 'nstates.name', searchable: true },
                { data: 'country_name', name: 'countries.name', searchable: true },
                { data: 'status', name: 'ncities.deleted_at', searchable: false },
            ],
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            }
        });
        $('#eyatra_city_table_filter').find('input').addClass("on_focus");
        $('.on_focus').focus();
        $('.dataTables_length select').select2();

        setTimeout(function() {
            var x = $('.separate-page-header-inner.search .custom-filter').position();
            var d = document.getElementById('eyatra_city_table_filter');
            x.left = x.left + 15;
            d.style.left = x.left + 'px';
        }, 500);

        $scope.loadState = function(country_id) {
            if (country_id != null) {
                $.ajax({
                        url: get_state_by_country,
                        method: "POST",
                        data: { country_id: country_id },
                    })
                    .done(function(res) {
                        self.state_list = [];
                        $(res).each(function(i, v) {
                            self.state_list.push({
                                id: v['id'],
                                name: v['name'],
                            });
                        });
                    })
                    .fail(function(xhr) {
                        console.log(xhr);
                    });
            } else {
                self.state_list = [];
            }

        }
        $http.get(
            city_filter_data_url
        ).then(function(response) {
            // console.log(response.data);
            self.country_list = response.data.country_list;
            // self.state_list = response.data.state_list;
            self.status_list = response.data.status_list;
            $rootScope.loading = false;
        });
        var dataTableFilter = $('#eyatra_city_table').dataTable();
        $scope.get_country_data = function(country_id) {
            // alert(country_id);
            $('#country_id').val(country_id);
            dataTableFilter.fnFilter();
        }
        $scope.get_state_data = function(id) {
            $('#state_id').val(id);
            dataTableFilter.fnFilter();
        }
        $scope.get_status_data = function(id) {
            $('#status').val(id);
            dataTableFilter.fnFilter();
        }
        $scope.reset_filter = function() {
            $('#country_id').val(null);
            $('#state_id').val(null);
            $('#status').val(null);
            dataTableFilter.fnFilter();
        }


        $scope.deleteCityConfirm = function($city_id) {
            $("#delete_city_id").val($city_id);
        }

        $scope.deleteCity = function() {
            $city_id = $('#delete_city_id').val();
            $http.get(
                city_delete_url + '/' + $city_id,
            ).then(function(response) {
                // console.log(response.data);
                if (response.data.success) {
                    $noty = new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: 'City Deleted Successfully',
                        animation: {
                            speed: 500 // unavailable - no need
                        },
                    }).show();
                    setTimeout(function() {
                        $noty.close();
                    }, 5000);
                    dataTable.ajax.reload(function(json) {});

                } else {
                    $noty = new Noty({
                        type: 'error',
                        layout: 'topRight',
                        text: 'City not Deleted',
                        animation: {
                            speed: 500 // unavailable - no need
                        },
                    }).show();
                    setTimeout(function() {
                        $noty.close();
                    }, 5000);
                }
            });
        }
        $rootScope.loading = false;

    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------

app.component('eyatraCityForm', {
    templateUrl: city_form_template_url,
    controller: function($http, $location, $location, HelperService, $routeParams, $rootScope, $scope) {
        $form_data_url = typeof($routeParams.city_id) == 'undefined' ? city_form_data_url : city_form_data_url + '/' + $routeParams.city_id;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        $http.get(
            $form_data_url
        ).then(function(response) {
            if (!response.data.success) {
                $noty = new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: response.data.error,
                    animation: {
                        speed: 500 // unavailable - no need
                    },
                }).show();
                setTimeout(function() {
                    $noty.close();
                }, 5000);
                $location.path('/cities')
                $scope.$apply()
                return;
            }

            self.city = response.data.city;
            self.state_list = response.data.state_list;
            self.status = response.data.status;
            self.guest_house_status = response.data.guest_house_status;
            self.extras = response.data.extras;
            self.action = response.data.action;

        });
        $('#on_focus').focus();

        $('#travel_mode').on('click', function() {
            if (event.target.checked == true) {
                $('.travelmodecheckbox').prop('checked', true);

                $.each($('.travelmodecheckbox:checked'), function() {
                    $scope.getTravelMode($(this).val());
                });

            } else {
                $('.travelmodecheckbox').prop('checked', false);
                $.each($('.travelmodecheckbox'), function() {
                    $scope.getTravelMode($(this).val());
                });
                //     if ($('.travelmodecheckbox').is(":checked")) {
                //         $('#sc_' + id).show();
                //         $('.agent_select').show();
                //     } else {
                //         $('#sc_' + id).hide();
                //         $('.agent_select').hide();
                //     }
            }
        });

        $scope.loadState = function(country_id) {

            if (country_id != null) {

                $.ajax({
                        url: get_state_by_country,
                        method: "POST",
                        data: { country_id: country_id },

                    })
                    .done(function(res) {
                        self.extras.state_list = [];
                        $(res).each(function(i, v) {
                            self.extras.state_list.push({
                                id: v['id'],
                                name: v['name'],
                            });
                        });
                    })
                    .fail(function(xhr) {
                        console.log(xhr);
                    });
            } else {

                self.extras.state_list = [];
            }
        }

        $('.btn-nxt').on("click", function() {
            $('.editDetails-tabs li.active').next().children('a').trigger("click");
        });
        $('.btn-prev').on("click", function() {
            $('.editDetails-tabs li.active').prev().children('a').trigger("click");
        });
        $('.btn-pills').on("click", function() {});
        $scope.btnNxt = function() {}
        $scope.prev = function() {}

        var form_id = '#city-form';
        var v = jQuery(form_id).validate({
            errorPlacement: function(error, element) {
                if (element.hasClass("name")) {
                    error.appendTo($('.name_error'));
                } else {
                    error.insertAfter(element)
                }
            },

            ignore: '',
            rules: {

                'name': {
                    required: true,
                    minlength: 1,
                    maxlength: 191,
                },
                'country_id': {
                    required: true,
                },
                'state_id': {
                    required: true,
                },
                'category_id': {
                    required: true,
                },
                // 'travel_modes[{{travel_mode.id}}][agent_id]': {
                //     required: true,
                // },
                // 'travel_modes[{{travel_mode.id}}][service_charge]': {
                //     required: true,
                //     number: true,
                //     min: 1,
                // },

            },
            messages: {
                // 'code': {
                //     minlength: 'Please enter minimum of 2 letters',
                //     maxlength: 'Please enter maximum of 2 letters',
                // },
                'name': {
                    minlength: 'Please enter minimum of 1 letters',
                    maxlength: 'Please enter maximum of 191 letters',
                },
                // 'travel_modes[]': {
                //     required: 'Travel mode required',
                // }
            },
            /*invalidHandler: function(event, validator) {
                $noty = new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: 'You have errors,Please check',
                    animation: {
                        speed: 500 // unavailable - no need
                    },
                }).show();
                setTimeout(function() {
                    $noty.close();
                }, 5000);
            },*/
            submitHandler: function(form) {

                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveEYatraCity'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        // console.log(res.success);
                        if (!res.success) {
                            $('#submit').button('reset');
                            var errors = '';
                            for (var i in res.errors) {
                                errors += '<li>' + res.errors[i] + '</li>';
                            }
                            custom_noty('error', errors);
                        } else {
                            $noty = new Noty({
                                type: 'success',
                                layout: 'topRight',
                                text: 'City saved successfully',
                                text: res.message,
                                animation: {
                                    speed: 500 // unavailable - no need
                                },
                            }).show();
                            setTimeout(function() {
                                $noty.close();
                            }, 5000);
                            $location.path('/cities')
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

app.component('eyatraCityView', {
    templateUrl: city_view_template_url,

    controller: function($http, $location, $routeParams, HelperService, $scope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        $http.get(
            city_view_url + '/' + $routeParams.city_id
        ).then(function(response) {
            self.city = response.data.city;
            self.action = response.data.action;
        });
    }
});


//------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------