
<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) 
	{
		exit;
	}

	/** 
		* @package bytescrafter-usocketnet-restapi
		* Name: USocketNet RestAPI
		* Description: Self-Host Realtime Multiplayer Server 
		*       for your Game or Chat Application.
		* Package-Website: https://usocketnet.bytescrafter.net
		* 
		* Author: Bytes Crafter
		* Author-Website:: https://www.bytescrafter.net/about-us
		* License: Copyright (C) Bytes Crafter - All rights Reserved. 
	*/
?>

<script type="text/javascript">
    jQuery(document).ready( function ( $ ) 
    {
        var usnprojects = undefined;

        //GET THE REFERENCE OF THE CURRENT PAGE DATTABLES.
        var clusterDatatables = $('#project-datatables');

        //SHOW NOTIFICATION THAT WE ARE CURRENTLY LOADING Cluster.

        //SET INTERVAL DRAW UPDATE.
        loadingClusterList();
        // setInterval( function()
        // { 
        //     loadingClusterList( clusterDatatables);
        // }, 10000);
        $('#ReloadClusterList').click(function() {
            loadingClusterList();
        });

        function loadingClusterList()
        {
            if( clusterDatatables.length != 0 )
            {
                if( $('#project-notification').hasClass('usn-display-hide') )
                {
                    $('#project-notification').removeClass('usn-display-hide');
                }
                
                var clusterListAction = { action: 'ReloadAgentList' };
                $.ajax({
                    dataType: 'json',
                    type: 'POST', 
                    data: clusterListAction,
                    url: 'wp-admin/admin-ajax.php',
                    success : function( data )
                    {
                        displayingLoadedProjects( data.message );
                        if( !$('#project-notification').hasClass('usn-display-hide') )
                        {
                            $('#project-notification').addClass('usn-display-hide');
                        }
                    },
                    error : function(jqXHR, textStatus, errorThrown) 
                    {
                        //$('#project-notification').text = "";
                        console.log("" + JSON.stringify(jqXHR) + " :: " + textStatus + " :: " + errorThrown);
                    }
                });
            }
        }

        //DISPLAY DATA INTO THE TARGET DATATABLES.
        function displayingLoadedProjects( data )
        {
            //Set table column header.
            let columns = [
                { "sTitle": "FIRST NAME",   "mData": "cluster_name" },
                { "sTitle": "LAST NAME",   "mData": "cluster_info" },
                { "sTitle": "DEPARTMENT",   "mData": "cluster_hostname" },
                { "sTitle": "DEVICE ID",   "mData": "cluster_capacity" },
                { "sTitle": "MCAFEE READY",   "mData": "mcafee_ready" },

                {"sTitle": "Action", "mRender": function(data, type, item)
                    {
                        return '' + 

                            '<div class="btn-group" role="group" aria-label="Basic example">' +

                                '<button type="button" class="btn btn-edit btn-primary btn-sm"' +
                                    ' data-toggle="modal" data-target="#EditClusterOption"' +
                                    ' title="Click this to modify or delete the cluster."' +
                                    ' data-cluster_id="' + item.ID + '"' +  
                                    ' data-cluster_name="' + item.cluster_name + '"' +  
                                    ' data-cluster_info="' + item.cluster_info + '"' +  
                                    ' data-cluster_owner="' + item.cluster_owner + '"' +  
                                    ' data-cluster_hostname="' + item.cluster_hostname + '"' +  
                                    ' data-cluster_capacity="' + item.cluster_capacity + '"' +
                                    ' data-mcafee_ready="' + item.mcafee_ready + '"' +
                                    ' >MODIFY</button>' +
                                    
                            '</div>'; 
                    }
                }

            ];

            //Displaying data on datatables.
            usnprojects = $('#project-datatables').DataTable({
                destroy: true,
                searching: true,
                buttons: ['copy', 'excel', 'print'],
                responsive: true,
                "aaData": data,
                "aoColumns": columns,
                "columnDefs": [
                    {
                        "className": "dt-center", 
                        "targets": "_all",
                        "targets": [0, 1, 2, 3, 4],
                        "createdCell": function (td, cellData, rowData, row, col) {
                            if ( rowData.cluster_hostname.toLowerCase().match('aps') || rowData.cluster_hostname.toLowerCase().match('audit') || rowData.cluster_hostname.toLowerCase().includes('datafied')   ) {
                                rowData.cluster_hostname += "aaaa";
                                $(td).css('background-color', 'rgb(197, 255, 197)')
                            }
                            console.log(rowData);
                        },
                        "createdRow": function( row, data, dataIndex ) {
                            // if ( data[4] == "A" ) {
                            //     $(row).addClass( 'important' );
                            // }
                            console.log(row+'::'+data+'::'+dataIndex);
                        }
                    }
                ],
            })
        }

        //IMPLEMENT DATATABLES RESPONSIVENESS.
        if(typeof usnprojects !== 'undefined' && typeof usnprojects.on === 'function')
        {
            usnprojects.on( 'responsive-resize', function ( e, datatable, columns ) {
                var count = columns.reduce( function (a,b) {
                    return b === false ? a+1 : a;
                }, 0 );
            
                console.log( count +' column(s) are hidden' );
            } );
        }

        //CREATE NEW ENTRY ON MODAL.
        $('#add-cluster-form').submit( function(event) {
            event.preventDefault();

            $( "#dialog-confirm-create" ).dialog({
                title: 'Confirmation',
                resizable: false,
                height: "auto",
                width: 320,
                modal: false,
                open: function() {
                    $('#jquery-overlay').removeClass('usn-display-hide');
                    $('#confirm-content-create').html(
                        '<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>' +
                        'Please confirm to complete the process, else just press cancel.'
                    );
                },
                buttons: {
                    "Confirm": function() 
                    {
                        confirmAddProcess();
                        $('#jquery-overlay').addClass('usn-display-hide');
                        $( this ).dialog( "close" );
                    },
                    Cancel: function() 
                    {
                        $('#jquery-overlay').addClass('usn-display-hide');
                        $( this ).dialog( "close" );
                    }
                }
            });
        });

        function confirmAddProcess()
        {
            $('#add-cluster-btn').addClass('disabled');

            //From native form object to json object.
            var unindexed_array = $('#add-cluster-form').serializeArray();
            var indexed_array = {};

            $.map(unindexed_array, function(n, i){
                indexed_array[n['name']] = n['value'];
            });
            indexed_array.action = 'AddNewAgents';

            // This will be handled by create.php.
            $.ajax({
                dataType: 'json',
                type: 'POST', 
                data: indexed_array,
                url: 'wp-admin/admin-ajax.php',
                success : function( data )
                {
                    if( data.status == 'success' ) {
                        $('#cluster_name').val('');
                        $('#cluster_info').val('');
                        $('#cluster_hostname').val('');
                        $('#cluster_capacity').val('');
                        $('#mcafee_ready').val('');
                    }
                    $('#CNAMessage').addClass('alert-'+data.status);
                    $('#CNAMessage').removeClass('usn-display-hide');
                    $('#CNAMcontent').text( data.message );

                    loadingClusterList();
                   $('#add-cluster-btn').removeClass('disabled');
                    activeTimeout = setTimeout( function() {
                        $('#CNAMessage').removeClass('alert-'+data.status);
                        $('#CNAMessage').addClass('usn-display-hide');
                        activeTimeout = 'undefined';
                    }, 4000);
                },
                error : function(jqXHR, textStatus, errorThrown) {
                    $('#CNAMessage').addClass('alert-danger');
                    $('#CNAMessage').removeClass('usn-display-hide');
                    $('#CNAMcontent').text( textStatus + ': Kindly consult to your administrator for this issue.' );

                    $('#add-cluster-btn').removeClass('disabled');
                    activeTimeout = setTimeout( function() {
                        $('#CNAMessage').removeClass('alert-danger');
                        $('#CNAMessage').addClass('usn-display-hide');
                        activeTimeout = 'undefined';
                    }, 7000);
                    console.log("" + JSON.stringify(jqXHR) + " :: " + textStatus + " :: " + errorThrown);
                }
            });
        }

        // LISTEN FOR MODAL SHOW AND ATTACHED ID.
        $('#AddNewAgents').on('show.bs.modal', function(e) {
            var data = e.relatedTarget.dataset;
            $('#add-cluster-btn').removeClass('disabled');
        });

        // MAKE SURE THAT TIMEOUT IS CANCELLED.
        $('#AddNewAgents').on('hide.bs.modal', function(e) {
            if( typeof activeTimeout !== 'undefined' )
            {
                clearTimeout( activeTimeout );
            }

            if( !$('#CNAMessage').hasClass('usn-display-hide') )
            {
                $('#CNAMessage').addClass('usn-display-hide');
            }
        });

        //DELETE OR UPDATE FOCUSED ON MODAL.
            $('#edit-cluster-form').submit( function(event) {
                event.preventDefault();
                var clickedBtnId = $(this).find("button[type=submit]:focus").attr('id');
                $( "#dialog-confirm-edit" ).dialog({
                    title: 'Confirmation',
                    resizable: false,
                    height: "auto",
                    width: 320,
                    modal: false,
                    open: function() {
                        $('#jquery-overlay').removeClass('usn-display-hide');
                        $('#confirm-content-edit').html(
                            '<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>' +
                            'Please confirm to complete the process, else just press cancel.'
                        );
                    },
                    buttons: {
                    "Confirm": function() 
                    {
                        confirmEditProcess( clickedBtnId );
                        $('#jquery-overlay').addClass('usn-display-hide');
                        $( this ).dialog( "close" );
                    },
                    Cancel: function() 
                    {
                        $('#jquery-overlay').addClass('usn-display-hide');
                        $( this ).dialog( "close" );
                    }
                    }
                });
                
            });

            function confirmEditProcess( clickedBtnId )
            {
                $('#delete-cluster-btn').addClass('disabled');
                $('#update-cluster-btn').addClass('disabled');

                //From native form object to json object.
                var postParam = {};

                if( clickedBtnId == 'delete-cluster-btn' )
                {
                    postParam.action = 'DeleteThisAgent';
                    postParam.cluster_id = $('#cluster_id_edit').val();
                }

                else
                {
                    postParam.action = 'UpdateThisAgent';
                    postParam.cluster_id = $('#cluster_id_edit').val();
                    postParam.cluster_name = $('#cluster_name_edit').val();
                    postParam.cluster_info = $('#cluster_info_edit').val();
                    postParam.cluster_hostname = $('#cluster_hostname_edit').val();
                    postParam.cluster_capacity = $('#cluster_capacity_edit').val();
                    postParam.mcafee_ready = $('#mcafee_ready_edit').val();
                }

                // This will be handled by create.php.
                $.ajax({
                    dataType: 'json',
                    type: 'POST', 
                    data: postParam,
                    url: 'wp-admin/admin-ajax.php',
                    success : function( data )
                    {
                        if( clickedBtnId == 'delete-cluster-btn' ) {
                            $('#cluster_name_edit').val('');
                            $('#cluster_info_edit').val('');
                            $('#cluster_hostname_edit').val('');
                            $('#cluster_capacity_edit').val('');
                            $('#mcafee_ready_edit').val('');
                        } else {
                            $('#delete-cluster-btn').removeClass('disabled');
                            $('#update-cluster-btn').removeClass('disabled');
                        }
                        
                        $('#DFAMessage').addClass('alert-'+data.status);
                        $('#DFAMessage').removeClass('usn-display-hide');
                        $('#DFAMcontent').text( data.message );

                        loadingClusterList();
                        activeTimeout = setTimeout( function() {
                            $('#DFAMessage').removeClass('alert-'+data.status);
                            $('#DFAMessage').addClass('usn-display-hide');
                            if( clickedBtnId == 'delete-cluster-btn' ) {
                                $('#EditClusterOption').modal('hide');
                            }
                            activeTimeout = 'undefined';
                        }, 4000);
                    },
                    error : function(jqXHR, textStatus, errorThrown) {
                        $('#DFAMessage').addClass('alert-danger');
                        $('#DFAMessage').removeClass('usn-display-hide');
                        $('#DFAMcontent').text( textStatus + ': Kindly consult to your administrator for this issue.' );

                        $('#delete-cluster-btn').removeClass('disabled');
                        $('#update-cluster-btn').removeClass('disabled');
                        activeTimeout = setTimeout( function() {
                            $('#DFAMessage').removeClass('alert-danger');
                            $('#DFAMessage').addClass('usn-display-hide');
                            activeTimeout = 'undefined';
                        }, 7000);
                        console.log("" + JSON.stringify(jqXHR) + " :: " + textStatus + " :: " + errorThrown);
                    }
                });
            }

            // LISTEN FOR MODAL SHOW AND ATTACHED ID.
            $('#EditClusterOption').on('show.bs.modal', function(e) {
                var data = e.relatedTarget.dataset;
                console.log('aaaaaaaa' + data)
                $('#cluster_id_edit').val( data.cluster_id );
                $('#cluster_name_edit').val( data.cluster_name );
                $('#cluster_info_edit').val( data.cluster_info );
                $('#cluster_hostname_edit').val( data.cluster_hostname );
                $('#cluster_capacity_edit').val( data.cluster_capacity );
                $('#mcafee_ready_edit').val( data.mcafee_ready );

                $('#delete-cluster-btn').removeClass('disabled');
                $('#update-cluster-btn').removeClass('disabled');
            });

            // MAKE SURE THAT TIMEOUT IS CANCELLED.
            $('#EditClusterOption').on('hide.bs.modal', function(e) {
                if( typeof activeTimeout !== 'undefined' ) {
                    clearTimeout( activeTimeout );
                }

                if( !$('#DFAMessage').hasClass('usn-display-hide') ){
                    $('#DFAMessage').addClass('usn-display-hide');
                }
            });

    });
</script>