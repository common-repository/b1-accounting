jQuery(document).ready(function ($) {

    "use strict";

    var b1_error_alert = $("#b1-error-alert");
    var b1_success_alert = $("#b1-success-alert");

    function b1_show_success_alert(message) {
        b1_error_alert.addClass('hidden');
        b1_success_alert.find('p').text(message);
        b1_success_alert.removeClass('hidden');
    }

    function b1_show_error_alert(message) {
        b1_success_alert.addClass('hidden');
        b1_error_alert.find('p').text(message);
        b1_error_alert.removeClass('hidden');
    }

    /**
     * Display table data
     */
    function b1_load_logs() {
        $.ajax({
            url: b1.base_url,
            type: 'post',
            data: {
                _ajax_nonce: b1.security,
                action: 'b1_load_logs',
            },
            success: function (response) {
                $('#b1TableLogs').html(response)
            }
        }).fail(function () {
            b1_show_error_alert(b1.internal_error);
        });
    }

    function b1_load_validation_logs() {
        $.ajax({
            url: b1.base_url,
            type: 'post',
            data: {
                _ajax_nonce: b1.security,
                action: 'b1_load_validation_logs',
            },
            success: function (response) {
                $('#b1ValidationTableLogs').html(response)
            }
        }).fail(function () {
            b1_show_error_alert(b1.internal_error);
        });
    }

    /**
     * View separate item
     * @param id
     */
    function b1_view_detail_log(id) {
        $.ajax({
            url: b1.base_url,
            type: 'post',
            data: {
                _ajax_nonce: b1.security,
                action: 'b1_view_detail_log',
                id: id
            },
            success: function (response) {
                $('#b1LogDetailView').html(response)
            }
        }).fail(function () {
            b1_show_error_alert(b1.internal_error);
        });
    }

    function b1_view_detail_validation_log(id) {
        $.ajax({
            url: b1.base_url,
            type: 'post',
            data: {
                _ajax_nonce: b1.security,
                action: 'b1_view_detail_validation_log',
                id: id
            },
            success: function (response) {
                $('#b1LogDetailView').html(response)
            }
        }).fail(function () {
            b1_show_error_alert(b1.internal_error);
        });
    }

    /**
     * Download file from php stream
     * @param selected_ids
     */
    function b1_export_logs(selected_ids) {
        $.ajax({
            url: b1.base_url,
            type: 'post',
            dataType: 'binary',
            xhrFields: {
                'responseType': 'blob'
            },
            data: {
                _ajax_nonce: b1.security,
                action: 'b1_export_logs',
                selected: selected_ids
            },
            success: function (data, status, xhr) {
                var link = document.createElement('a'),
                    filename = 'b1-woocommerce-logs.json';
                if (xhr.getResponseHeader('Content-Disposition')) { // filename from php
                    filename = xhr.getResponseHeader('Content-Disposition');
                    filename = filename.match(/filename="(.*?)"/)[1];
                    filename = decodeURIComponent(escape(filename));
                }
                link.href = URL.createObjectURL(data);
                link.download = filename;
                var text = document.createTextNode(filename);
                link.appendChild(text);
                $('#b1DownloadLink').append(link);
            }
        }).fail(function () {
            b1_show_error_alert(b1.internal_error);
        });
    }


    $('#resetB1ReferenceId').click(function (event) {
        event.preventDefault();
        var data = $(this).data();
        data.resetAll = true;
        $.ajax({
            url: b1.base_url,
            type: 'post',
            data: data
        }).done(function (response) {
            if (response.success) {
                b1_show_success_alert(response.message);
            } else {
                b1_show_error_alert(response.message);
            }
        }).fail(function () {
            b1_show_error_alert(b1.internal_error);
        });
    });

    $('#getImportDropdownItems').one("click", function (event) {
        $("#attribute_id").empty();
        $("#measurement_unit_id").empty();
        event.preventDefault();
        $('#getImportDropdownItems')
            .hide()
        var data = $(this).data();
        $.ajax({
            url: b1.base_url,
            type: 'post',
            data: data
        }).done(function (response) {
            if (response.success) {
                $.each(response.message.attributeId.data, function (index, value) {
                    $('#attribute_id')
                        .prepend("<option value = " + value.id + ">" + value.name + "</option>")
                        .find('listItem')
                });
                $.each(response.message.measurementId.data, function (index, value) {
                    $('#measurement_unit_id')
                        .prepend("<option value = " + value.id + ">" + value.name + "</option>")
                        .find('listItem')
                })
            } else {
                b1_show_error_alert(response.message);
            }
        }).fail(function (response) {
            b1_show_error_alert(response.statusText);
        });
    });

    $('#importItemsToB1').submit(function (event) {
        event.preventDefault();
        $.ajax({
            url: b1.base_url,
            type: 'post',
            data: $(this).serialize()
        }).done(function (response) {
            if (response.success) {
                b1_show_success_alert(response.message);
            } else {
                b1_show_error_alert(response.message);
            }
        }).fail(function () {
            b1_show_error_alert(b1.internal_error);
        });
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        if (target === '#mapping') {
            //   $('#table-b1-unlinked-items').DataTable().ajax.reload();
        } else if (target === '#settings') {
            //    $('#table-linked-items').DataTable().ajax.reload();
        } else if (target === '#logs') {
            b1_load_logs();
        }else if (target === '#validations') {
            b1_load_validation_logs();
        }
    });

    $('#form-settings').submit(function (event) {
        event.preventDefault();
        $.ajax({
            url: b1.base_url,
            type: 'post',
            data: $(this).serialize()
        }).done(function (response) {
            if (response.success) {
                b1_show_success_alert(response.message);
            } else {
                b1_show_error_alert(response.message);
            }
        }).fail(function (response) {
            b1_show_error_alert(response);
        });
    });

    $('#form-mapping').submit(function (event) {
        event.preventDefault();
        $.ajax({
            url: b1.base_url,
            type: 'post',
            data: $(this).serialize()
        }).done(function (response) {
            if (response.success) {
                b1_show_success_alert(response.message);
            } else {
                b1_show_error_alert(response.message);
            }
        }).fail(function () {
            b1_show_error_alert(response);
        });
    });

    /*$(document).on("click", "tr", function (e) {
        $(':checkbox', this).trigger('click');
        $(':radio', this).prop("checked", true);
    });*/

    $.extend(true, $.fn.dataTable.defaults, {
        processing: true,
        serverSide: true,
        ordering: false,
        pageLength: 50,
        initComplete: function () {
            var api = this.api();
            api.columns().every(function () {
                var that = this;
                $('select', this.header()).on('change', function () {
                    if (that.search() !== this.value) {
                        that
                            .search(this.value)
                            .draw();
                    }
                });
                $('input', this.header()).on('keyup change', function () {
                    if (that.search() !== this.value) {
                        that
                            .search(this.value)
                            .draw();
                    }
                });
            });
        },
        language: {
            sProcessing: b1.texts.processing,
            sLengthMenu: b1.texts.length_menu,
            sZeroRecords: b1.texts.zero_records,
            Info: b1.texts.showing_records,
            sInfoEmpty: b1.texts.showing_zero_records,
            oPaginate: {
                sFirst: '<<',
                sPrevious: '<',
                sNext: '>',
                sLast: '>>'
            }
        }
    });
    $.fn.dataTableExt.oStdClasses.sWrapper = 'dataTables_wrapper dt-bootstrap no-footer';

    $(document).on("click", ".B1-logs tr", function (e) {
        var id = $(this).attr('data-id');
        b1_view_detail_log(id);
    });

    $(document).on("click", ".B1-validation-logs tr", function (e) {
        var id = $(this).attr('data-id');
        b1_view_detail_validation_log(id);
    });

    $('#b1DownloadBtn').click(function (event) {
        event.preventDefault();
        $('#b1DownloadLink').html('');
        var selected_ids = [];
        $.each($("input[name='b1LogItemChkBox[]']:checked"), function () {
            selected_ids.push($(this).val());
        });
        b1_export_logs(selected_ids);
    });

    $(document).on("click", "a.page-numbers", function (event) {
        event.preventDefault();
        var link = $(this).attr('href');
        $('#b1TableLogs').empty().load(b1.base_url+'?action=b1_load_logs&path='+encodeURIComponent(link));
    });

    $('#b1ResetMappingBtn').click(function (event) {
        event.preventDefault();
        $.ajax({
            url: b1.base_url,
            type: 'post',
            data: {
                _ajax_nonce: b1.security,
                action: 'b1_reset_mappings',
            },
            success: function (data, status, xhr) {
                location.reload(true);
            }
        }).fail(function () {
            b1_show_error_alert(b1.internal_error);
        });

    });

    $('#b1ResetSettingsBtn').click(function (event) {
        event.preventDefault();
        $.ajax({
            url: b1.base_url,
            type: 'post',
            data: {
                _ajax_nonce: b1.security,
                action: 'b1_reset_settings',
            },
            success: function (data, status, xhr) {
                location.reload(true);
            }
        }).fail(function () {
            b1_show_error_alert(b1.internal_error);
        });

    });

});
