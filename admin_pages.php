<h1>AwardBox UPCP Extender</h1>
<table id="UPCP_extended1" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Catalogue</th>
            <th>Category</th>
            <th>Product</th>
            <th>Image</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th>Catalogue</th>
            <th>Category</th>
            <th>Product</th>
            <th>Image</th>
        </tr>
    </tfoot>
</table>
<script>
(function($) {
    $(function() {
        $('#UPCP_extended1').DataTable( {
            processing  : true,
            _serverSide  : true,
            ajax        : {
                url  : ajaxurl,
                type : 'POST',
                data : function(d) {
                    d.action = "get_eUPCP_query1";
            }}
        } );
    })
})(jQuery);
</script>