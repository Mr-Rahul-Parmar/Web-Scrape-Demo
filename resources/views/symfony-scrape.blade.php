<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css"/>
    <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('/assets/css/jquery.fancybox.min.css') }}">
    <title>RoomeScrapeDemo</title>
    <style>
        .form-error {
            color: red;
        }
    </style>
</head>
<body>

{{--<div class="card">--}}
{{--    <div class="card-body">--}}
{{--        <form method="POST" action="{{url('right-move')}}" id="ScrapeForm">--}}
{{--            @csrf--}}
{{--            <div class="form-error">--}}
{{--                <div class="input-group mb-3">--}}
{{--                    <input type="url" name="url" id="url" class="form-control" placeholder="Enter Url...">--}}
{{--                    <button type="submit" value="submit" name="submit" class="btn btn-primary">Submit</button>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </form>--}}
{{--    </div>--}}
{{--</div>--}}

<div class="card-body">
    <form action="{{ url('import') }}" id="importScrapeForm" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-error">
            <input type="file" id="file" name="file" class="form-control">
            <br>
        </div>
        <button class="btn btn-success">Import Scrape Data</button>
    </form>
    <hr>
</div>

@if (\Session::has('success'))
    <div class="alert alert-success">
        <ul>
            <li>{!! \Session::get('success') !!}</li>
        </ul>
    </div>
@endif
@if (\Session::has('error'))
    <div class="alert alert-danger">
        <ul>
            <li>{!! \Session::get('error') !!}</li>
        </ul>
    </div>
@endif
<div class="col-lg-12 margin-tb" id="SuceessMsg">
</div>
<table class="table table-bordered data-table">
    <thead>
    <tr>
        <th>no</th>
        <th>Image</th>
        <th>Title</th>
        <th>Location</th>
        <th>Property Type</th>
        <th>Bedroom</th>
        <th>Bathroom</th>
        <th>Deposit</th>
        <th>Price Per Month</th>
        <th>Price Per Week</th>
        <th>Key Feature</th>
        <th>Description</th>
        <th>Agent Name</th>
        <th>Agent Address</th>
        <th>Agent Contact</th>
        <th>Agent Description</th>
        <th>Created At</th>
        <th width="100px">Action</th>
    </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<!-- Optional JavaScript; choose one of the two! -->

<!-- Option 1: Bootstrap Bundle with Popper -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<script src="{{ asset('/assets/js/jquery.fancybox.min.js') }}"></script>

<!-- Option 2: Separate Popper and Bootstrap JS -->
<!--
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
-->
<script type="text/javascript">
    $(function () {

        var table = $('.data-table').DataTable({
            responsive: true,
            "order": [[16, "desc"]],
            "bAutoWidth": false, // Disable the auto width calculation
            serverSide: 'true',
            ajax: {
                url: "{{ url('/') }}",
                dataSrc: function (json) {
                    setInterval(function () {
                        $('.data-table').DataTable().ajax.reload()
                    }, 10000);
                    return json.data;
                }
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'image', name: 'image'},
                {data: 'p_title', name: 'p_title'},
                {data: 'location', name: 'location'},
                {data: 'property_type', name: 'property_type'},
                {data: 'bedroom', name: 'bedroom'},
                {data: 'bathroom', name: 'bathroom'},
                {data: 'deposit', name: 'deposit'},
                {data: 'price_p_month', name: 'price_p_month'},
                {data: 'price_p_week', name: 'price_p_week'},
                {data: 'key_feature', name: 'key_feature'},
                {data: 'description', name: 'description'},
                {data: 'agent_name', name: 'agent_name'},
                {data: 'agent_address', name: 'agent_address'},
                {data: 'agent_contact_no', name: 'agent_contact_no'},
                {data: 'agent_description', name: 'agent_description'},
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action', searchable: false, sortable: false},
            ],
            "drawCallback": function (settings) {
                // $('#totalRequests').html("(" + settings.json.recordsTotal + ")");
                $($.fn.dataTable.tables(true)).DataTable()
                    .columns.adjust();
            },
            columnDefs: [
                {
                    targets: [10, 11, 15],
                    createdCell: function (cell) {
                        var $cell = $(cell);

                        if ($cell.text().length > 25) {
                            $(cell).contents().wrapAll("<div class='content'></div>");
                            var $content = $cell.find(".content");

                            $(cell).append($("<a href='javascript:;'>Read more</a>"));
                            $btn = $(cell).find("a");

                            $content.css({
                                "height": "50px",
                                "overflow": "hidden"
                            })
                            $cell.data("isLess", true);

                            $btn.click(function () {
                                var isLess = $cell.data("isLess");
                                $content.css("height", isLess ? "auto" : "50px")
                                $(this).text(isLess ? "Read less" : "Read more")
                                $cell.data("isLess", !isLess)
                            })
                        }
                    }
                }
            ],
        });
    });

    function deleteFunc(id) {
        if (confirm("Delete Record?") == true) {
            var id = id;
            // ajax
            $.ajax({
                type: "GET",
                url: "{{ url('delete-rec') }}",
                data: {id: id},
                dataType: 'json',
                success: function (res) {
                    // var oTable = $('.data-table').dataTable();
                    // oTable.fnDraw(false);
                    // $('#SuceessMsg').html("<div id='message-form' class='alert alert-danger'>" + res.delMessage + "</div>").fadeIn(5000);
                    window.location.reload();
                    // $('.data-table').DataTable().ajax.reload();
                }
            });
        }
    }

    $('[data-fancybox="group_"]').fancybox({
        buttons: [
            "slideShow",
            "thumbs",
            "zoom",
            "fullScreen",
            "share",
            "close"
        ],
        loop: false,
        protect: true
    });

    //jQuery Validation for Form
    $(document).ready(function () {
        $('#ScrapeForm').validate({
            rules: {
                url: {required: true},
            },
            messages: {
                url: {required: "Please enter url..."},
            },
            errorPlacement: function (error, element) {
                $(element).parents('.form-error').append(error);
            }
        });
    });

    jQuery.validator.addMethod("extension", function(value, element, param) {
        param = typeof param === "string" ? param.replace(/,/g, '|') : "xls|xlsx|ods";
        return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
    });
    //jQuery Validation for Import
    $(document).ready(function () {
        $('#importScrapeForm').validate({
            rules: {
                file: {
                    required: true,
                    extension: "xls|xlsx|ods"
                },
            },
            messages: {
                file: {
                    required: "Please select file for upload.",
                    extension: "You may not upload this type of file."
                },
            },
            errorPlacement: function (error, element) {
                $(element).parents('.form-error').append(error);
            }
        });
    });

</script>
</body>
</html>
