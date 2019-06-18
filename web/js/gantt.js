function stopEvent(event) {
    event.preventDefault();
    event.stopPropagation();
}

function initGantt(tasks)
{
    var gantt = new Gantt("#gantt", tasks,{
        on_click: function (task) {
            //открываем всплывающее окно
            $('#graph_list_modal').modal('show');
            $("#removecourseid").val(task.id);
        },
        on_view_change: function() {
            var bars = document.querySelectorAll('#gantt' + " .bar-group");
            for (var i = 0; i < bars.length; i++) {
                bars[i].addEventListener("mousedown", stopEvent, true);
            }
            var handles = document.querySelectorAll('#gantt' + " .handle-group");
            for (var i = 0; i < handles.length; i++) {
                handles[i].remove();
            }
        },
        custom_popup_html: function(task) {
            return ``;
        },
        language: 'ru'
    });
}

function openEditGraphItem(id) {
    $('#edit_graph_item_modal').modal('show');
    $("#graph-graph_id").val(id);
}