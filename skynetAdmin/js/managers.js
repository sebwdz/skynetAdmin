/**
 * Created by sebastien on 10/28/16.
 */

$("#manager-table").bootgrid({
    formatters: {
        link : function (col, row) {
            return (
                '<a href="/index.php/managers/'+ row.type.trim() + '/' + row.id.trim() + '">edit</a>'
            );
        }
    }
});