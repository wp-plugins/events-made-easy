    jQuery.fn.dataTableExt.oApi.clearSearch = function ( oSettings )
    {
        var table = this;
        var clearSearch = jQuery('<img src="data:image/png;data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAD2SURBVHjaxFM7DoMwDH2pOESHHgDPcB223gKpAxK34EAMMIe1FCQOgFQxuflARVBSVepQS5Ht2PHn2RHMjF/ohB8p2gSZpprtyxEHX8dGTeMG0A5UlsD5rCSGvF55F4SpqpSm1GmCzPO3LXJy1LXllwvodoMsCpNVy2hbYBjCLRiaZ8u7Dng+QXlu9b4H7ncvBmKbwoYBWR4kaXv3YmAMyoEpjv2PdWUHcP1j1ECqFpyj777YA6Yss9KyuEeDaW0cCsCUJMDjYUE8kr5TNuOzC+JiMI5uz2rmJvNWvidwcJXXx8IAuwb6uMqrY2iVgzbx99/4EmAAarFu0IJle5oAAAAASUVORK5CYII=" style="vertical-align:text-bottom;cursor:pointer;" />');
        jQuery(clearSearch).click( function () {
              table.fnFilter('');
              jQuery('input[type=search]').val('');
        });
       	jQuery(oSettings.nTableWrapper).find('div.dataTables_filter').append(clearSearch);
       	jQuery(oSettings.nTableWrapper).find('div.dataTables_filter label').css('margin-right', '-16px');//16px the image width
       	jQuery(oSettings.nTableWrapper).find('div.dataTables_filter input').css('padding-right', '16px');
    }
 
    //auto-execute, no code needs to be added
    jQuery.fn.dataTable.models.oSettings['aoInitComplete'].push({
        "fn": jQuery.fn.dataTableExt.oApi.clearSearch,
        "sName": 'whatever'
    });
