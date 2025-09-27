
// $(document).ready(function(){
//     var table = $("#table").DataTable({
//         ajax: {
//             url: 'select.php',
//             type: 'POST',
//             dataSrc: ''
//         },
//         columns: [
//             { data: 'id' },
//             { data: 'date' },
//             { data: 'task' },
//             { data: 'status' },
//             {
//                 data: 'check',
//                 render: function(data, type, row) {
//                     return `
//                     <div class="form-check form-switch">
//                         <input class="form-check-input check" type="checkbox" data-id="${row.id}" ${data == 1 ? 'checked':''}>
//                         <label class="form-check-label">${data == 1 ? 'Active':'Deactive'}</label>
//                     </div>`;
//                 }
//             },
//             {
//                 data: null,
//                 render: function(fetch) {
//                     return `
//                         <button class="btn btn-primary btn-sm mt-2 p-2 btn-outline-dark text-white" data-id="${fetch.id}" data-date="${fetch.date}" data-task="${fetch.task}" data-status="${fetch.status}" data-check="${fetch.check}" id="edit"><i class="fa fa-edit"></i> Edit</button>
//                         <button class="btn btn-danger btn-sm mt-2 p-2" data-id="${fetch.id}" id="delete"><i class="fa fa-trash-o"></i> Delete</button>
//                     `;
//                 }
//             }
//         ],
//         order: [
//             [0, 'asc']
//         ],
//     });
//     $('#taskForm').on('submit', function(e) {
//         e.preventDefault();
//         const taskId = $('#taskId').val();
//         const date = $('#taskDate').val();
//         const task = $('#taskDescription').val();
//         const url = taskId ? 'update.php':'insert.php';
//         alert(taskId);
//         $.ajax({
//             url: url,
//             type: 'POST',
//             data: {
//                 id: taskId,
//                 date: date,
//                 task: task
//             },
//             success: function(result) {
//                 $('#taskModal').modal('hide');
//                 $('.modal-backdrop.show').remove();
//                 table.ajax.reload(null, false);
//             }
//         });
//     });
//     $(document).on('click', '#edit', function() {
//         const id = $(this).data('id');
//         const date = $(this).data('date');
//         const task = $(this).data('task');
//         const status = $(this).data('status');
//         const check = $(this).data('check');

//         $("#updateId").val(id);
//         $("#editDate").val(date);
//         $("#editTask").val(task);
//         $("#editStatus").val(status);
//         $("#editCheck").prop('checked', check == 1);
//         $("#editCheckLabel").text(check == 1 ? 'Active':'Deactive');
//         $("#updateModal").modal('show');
//     });
//     $('#updateForm').on('submit', function(e) {
//         e.preventDefault();
//         const id = $('#updateId').val();
//         const date = $('#editDate').val();
//         const task = $('#editTask').val();
//         const status = $('#editStatus').val();
//         const check = $('#editCheck').is(':checked') ? 1:0;

//         $.ajax({
//             url: 'update.php',
//             type: 'POST',
//             data: {
//                 id: id,
//                 date: date,
//                 task: task,
//                 status: status,
//                 check: check
//             },
//             success: function(result) {
//                 $('#updateModal').modal('hide');
//                 $('.modal-backdrop.show').remove();
//                 table.ajax.reload(null, false);
//             }
//         });
//     });
//     $(document).on('click','#delete', function(e) {
//         e.preventDefault();
//         const id = $(this).data('id');
//         if(confirm('Are you sure delete this data?')) {
//             $.ajax({
//                 url: 'delete.php',
//                 type: 'POST',
//                 data: { id: id },
//                 success: function(result) {
//                     table.ajax.reload(null, false);
//                 }
//             });
//         }
//     });
//     $(document).on('change', '.check', function() {
//         const id = $(this).data('id');
//         const check = $(this).is(':checked') ? 1:0;
//         const label = $(this).next('label');
//         label.text(check == 1 ? 'Active':'Deactive');

//         $.ajax({
//             url: 'check.php',
//             type: 'POST',
//             data: {
//                 id: id,
//                 check: check
//             },
//             success: function(result) {
//                 table.ajax.reload(null, false);
//             }
//         });
//     });
//     $('#taskModal').on('shown.bs.modal', function () {
//         $('#taskId').val('');
//         $('#taskDate').val('');
//         $('#taskDescription').val('');
//     });
// });










