<?php include('sidebar.php'); ?>

<section class="home-section">

    <div class="home-content">
        <i class='bx bx-menu'></i>
        <?php
        echo date('l' . ' ' . 'd' . ' ' . 'M' . ' ' . 'Y');
        ?>
    </div>
    <div class="table-content mt-4">
        <div class="card">
            <div class="card-head pr-2">
                <p>จัดการสินค้ารออนุมัติ</p>
            </div>
            <div class="card-body">
                <div class="box-tale">
                    <table class="table" id="dataal">
                        <thead>
                            <tr>
                                <th>หมายเลขสั่งซื้อ</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>วันที่</th>
                                <th>เวลา</th>
                                <th>สถานะ</th>
                                <th>action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = $conn->prepare("SELECT * FROM payment order by pay_date ASC");
                            $sql->execute();
                            $row = $sql->fetchAll(PDO::FETCH_OBJ);
                            if ($sql->rowCount() > 0) {
                                foreach ($row as $res) {
                            ?>
                                    <tr>
                                        <td><?php echo $res->bill_trx; ?></td>
                                        <td><?php echo $res->pay_name; ?></td>
                                        <td><?php echo $res->pay_date; ?></td>
                                        <td><?php echo $res->pay_time; ?></td>
                                        <?php if ($res->pay_status == 'รอการยืนยันการสั่งซื้อ') {
                                        ?>
                                            <td class="text-warning"><?php echo $res->pay_status; ?></td>
                                        <?php } else { ?>
                                            <td class="text-success"><?php echo $res->pay_status; ?></td>
                                        <?php } ?>
                                        <td>
                                            <div class="dropdown open">
                                                <button class="btn btn-warning dropdown-toggle btn-sm" type="button" id="triggerId" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    เพิ่มเติม
                                                </button>
                                                <div class="dropdown-menu" aria-labelledby="triggerId">
                                                    <a target="_blank" class="dropdown-item" href="seeallpending.php?id=<?php echo $res->bill_trx; ?>">ดูรายละเอียด</a>
                                                    <button class="dropdown-item" onclick="editpayment('<?php echo $res->pay_id; ?>')">แก้ไข</button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                            <?php     }
                            } ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modaledit" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">แก้ไขสถานะ ( ID = <span id="editid"></span> )</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <form id="formeditpayment">
                            <input type="hidden" name="action" value="editpayment">
                            <input type="hidden" name="id" id="inputeditid">
                            <div class="form-group">
                                <label for="status">แก้ไขสถานะ</label>
                                <select name="status" id="editstatus" class="form-control">
                                    <option value="รอการยืนยันการสั่งซื้อ">รอการยืนยันการสั่งซื้อ</option>
                                    <option value="ยืนยันสินค้าเรียบร้อย">ยืนยันสินค้าเรียบร้อย</option>
                                </select>
                            </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                    <button class="btn btn-success">บันทึก</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</section>
<br>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
<script>
    $(function() {
        $(".modal").removeAttr("tabindex");
    });
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    })

    function getFormData($form) {
        var unindexed_array = $form.serializeArray();
        var indexed_array = {};

        $.map(unindexed_array, function(n, i) {
            indexed_array[n['name']] = n['value'];
        });

        return JSON.stringify(indexed_array);
    }

    function wait() {
        swal.fire({
            html: '<h5>กรุณารอซักครู่...</h5>',
            showConfirmButton: false,
        });
    }
</script>

<script>
    function editpayment(id) {
        $("#editid").html(id);
        $.ajax({
            type: "POST",
            url: "ajax/payment.php",
            data: {
                action: "getpayment",
                id: id,
            },
            success: function(response) {
                // console.log(response);
                let res = JSON.parse(response);
                $('#editstatus option[value="' + res.msg.status + '"]').prop('selected', true);
                $("#inputeditid").val(id);
                $("#modaledit").modal("show");
            }
        });
    }

    $("#formeditpayment").submit(function(e) {
        e.preventDefault();
        var data = $(this).serialize();
        var datavalid = JSON.parse(getFormData($(this)));
        // console.log(data);
        // console.log(datavalid.sorting.length);

        $.ajax({
            type: "POST",
            url: "ajax/payment.php",
            data: data,
            beforeSend: (e) => {
                wait();
            },
            success: (resp) => {
                let res = JSON.parse(resp);
                if (res.status == "success") {
                    Toast.fire({
                        icon: 'success',
                        title: 'สำเร็จ!'
                    })
                    setTimeout((e) => {
                        window.location = '?success'
                    }, 1500);
                } else {
                    if (res.msg == "wrong") {
                        Toast.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด!'
                        })
                    } else if (res.msg == "nopermission") {
                        Toast.fire({
                            icon: 'error',
                            title: 'ไม่ได้รับอนุญาต!',
                        })
                    } else if (res.msg == "notnum") {
                        Toast.fire({
                            icon: 'error',
                            title: 'ข้อมูลไม่ถูกต้อง!',
                        })
                    } else if (res.msg == "empty") {
                        Toast.fire({
                            icon: 'error',
                            title: 'ห้ามปล่อยว่าง!',
                        })
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด!'
                        })
                    }

                    setTimeout((e) => {
                        window.location = '?error'
                    }, 1500);
                }
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#dataal').DataTable({
            "oLanguage": {
                "sLengthMenu": "แสดงรายการ _MENU_ รายการ ต่อหน้า",
                "sZeroRecords": "ไม่เจอข้อมูลที่ค้นหา",
                "sInfo": "จำนวน _START_ ถึง _END_ ใน _TOTAL_ รายการทั้งหมด",
                "sInfoEmpty": "แสดง 0 ถึง 0 ของ 0 รายการทั้งหมด",
                "sInfoFiltered": "(จากเร็คคอร์ดทั้งหมด _MAX_ เร็คคอร์ด)",
                "sSearch": "ค้นหา :",
                "aaSorting": [
                    [0, 'desc']
                ],
                "oPaginate": {
                    "sFirst": "หน้าแรก",
                    "sPrevious": "ก่อนหน้า",
                    "sNext": "ถัดไป",
                    "sLast": "หน้าสุดท้าย"
                },
            },
            "order": [
                [0, "desc"]
            ]
        });
    });
</script>

<script>
    let arrow = document.querySelectorAll(".arrow");

    for (var i = 0; i < arrow.length; i++) {
        arrow[i].addEventListener("click", (e) => {

            let arrowParent = e.target.parentElement.parentElement;
            arrowParent.classList.toggle("showMenu");
        });
    }

    let sidebar = document.querySelector(".sidebar");
    let sidebarbtn = document.querySelector(".bx-menu");
    console.log(sidebarbtn);


    sidebarbtn.addEventListener("click", () => {
        sidebar.classList.toggle("close");

    });
</script>



</body>

</html>