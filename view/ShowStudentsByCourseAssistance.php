<?php
include_once './reusable/Session.php';
include_once './reusable/Header.php';

$course = (int) $_GET['course'];
$professor = (int) $_GET['professor'];
$year = (int) $_GET['year'];
$period = (int) $_GET['period'];
$group = (int) $_GET['group'];
?>

<!-- Content Header (Page header) -->
<section class="content-header" style="text-align: left">
    <ol class="breadcrumb">
        <li><a href="Home.php"><i class="fa fa-arrow-circle-right"></i> Inicio</a></li>
        <li><a href="#"><i class="fa fa-arrow-circle-right"></i> Módulos</a></li>
        <li><a href="ShowAssistance.php"><i class="fa fa-arrow-circle-right"></i> Ver Asistencia</a></li>
        <li><a href="#"><i class="fa fa-arrow-circle-right"></i> Asistencia</a></li>
    </ol>
</section>
<br>

<?php
if (isset($course) && is_int($course) &&
        isset($professor) && is_int($professor) &&
        isset($year) && is_int($year) &&
        isset($period) && is_int($period) &&
        isset($group) && is_int($group)) {
    ?>
    <!-- Main content -->
    <section class="content">
        <div class="row">

            <!--SHOW MODULES RELATED TO PROFESSOR-->
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <?php
                        include_once '../business/CourseBusiness.php';

                        $business = new CourseBusiness();

                        $courses = $business->getCourseId($course);
                        foreach ($courses as $item) {
                            ?>
                            <h3 class="box-title">
                                <b>
                                    <?php
                                    echo $item->getCourseName();
                                    ?>
                                </b>
                            </h3>
                            <?php
                            break;
                        }
                        ?>
                        <a type="button" class="btn btn-primary pull-right" id="btnInforme" onclick="genarateAbsence();">Crear Asistencia</a>
                        <br>
                        <br>
                        <h3 class="box-title"><b id="txtDate"/></h3>
                    </div>
                    <div class="table-responsive">
                        <div class="box-body">
                            <table id="studentsList" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Cédula</th>
                                        <th>Teléfono</th>
                                        <th>Presente</th>
                                        <th>Ausente</th>
                                        <th>Justificación</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody">
                                    <?php
                                    $students = $business->getStudentsListByCourseAndProfessor($course, $professor, $period, $year, $group);
                                    foreach ($students as $person) {
                                        ?>
                                        <tr>
                                            <td>
                                                <label><?php echo $person[0]; ?></label>
                                                <input type="hidden" name="id" id="id" value="<?php echo $person[3] ?>"/>

                                            </td>
                                            <td><?php echo $person[1]; ?></td>
                                            <td><?php echo $person[2]; ?></td>
                                            <td>
                                                <input type="checkbox" name="present" style="width: 20px; height: 20px; text-align: center" />
                                            </td>
                                            <td>
                                                <input type="checkbox" name="absence" style="width: 20px; height: 20px; text-align: center" />
                                            </td>
                                            <td>
                                                <textarea></textarea>
                                            </td>

                                        </tr>    
                                        <?php
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Cédula</th>
                                        <th>Teléfono</th>
                                        <th>Presente</th>
                                        <th>Ausente</th>
                                        <th>Justificación</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

            </div><!-- /.col -->

        </div><!-- /.row -->
    </section><!-- /.content -->

    <?php
}
include_once './reusable/Footer.php';
?>

<!-- page script -->
<script type="text/javascript">

    var d = new Date();
    $('#txtDate').html("Fecha: " + d.getDate() + "/" + (d.getMonth() + 1) + "/" + d.getFullYear());
    (function ($) {
        $.get = function (key) {
            key = key.replace(/[\[]/, '\\[');
            key = key.replace(/[\]]/, '\\]');
            var pattern = "[\\?&]" + key + "=([^&#]*)";
            var regex = new RegExp(pattern);
            var url = unescape(window.location.href);
            var results = regex.exec(url);
            if (results === null) {
                return null;
            } else {
                return results[1];
            }
        }
    })(jQuery);
    var typeMessage = $.get("typeMessage");
    var msg = $.get("msg");
    if (typeMessage === "1") {
        msg = msg.replace(/_/g, " ");
        alertify.success(msg);
    }
    if (typeMessage === "0") {
        msg = msg.replace(/_/g, " ");
        alertify.error(msg);
    }

    var data = [];
    function createInfo() {
        var isCorrect = true;
        var infoPerson;
        $('#tbody tr').each(function (index, element) {
            var id = $(element).find("td").eq(0).find("input").val();
            var name = $(element).find("td").eq(0).find("label").html();
            var present = $(element).find("td").eq(3).find("input");
            var absence = $(element).find("td").eq(4).find("input");
            var justification = $(element).find("td").eq(5).find("textarea").val();
            if ((present.is(':checked') && absence.is(':checked')) ||
                    (!present.is(':checked') &&
                            !absence.is(':checked'))) {
                isCorrect = false;
                if (name === "No se encuentran registros") {
                    alertify.error(name);
                } else {
                    alertify.error("Seleccione si el estudiante " + name + " esta ausente o presente");
                }

            } else {
                infoPerson = new Object();
                infoPerson.id = id;
                infoPerson.name = name;
                if (present.is(':checked')) {
                    infoPerson.isPresent = 1;
                }

                if (absence.is(':checked')) {
                    infoPerson.isPresent = 0;
                }

                infoPerson.justification = justification;
                infoPerson.professor = <?php echo $_SESSION['id']; ?>;
                infoPerson.course = <?php echo $course; ?>;
                infoPerson.group = <?php echo $group; ?>;
                infoPerson.period = <?php echo $period; ?>;
                data.push(infoPerson);
            }
        });
        return isCorrect;
    }

    function genarateAbsence() {
        if (createInfo()) {
            $.ajax({
                type: 'POST',
                url: "../actions/CreateAttendanceAction.php",
                data: {"data": JSON.stringify(data)},
                success: function (response)
                {
                    if (response == true) {
                        clearTable();
                        alertify.success("Asistencia guardada correctamente.");
                    } else {
                        alertify.error("Error al guradar asistencia...");
                    }

                },
                error: function ()
                {
                    alertify.error("Error ...");
                }
            });
            data = [];
        } else {
            data = [];
        }
    }

    function clearTable() {
        $('#tbody tr').each(function (index, element) {
            $(element).find("td").eq(3).find("input").prop("checked", "");
            $(element).find("td").eq(4).find("input").prop("checked", "");
            $(element).find("td").eq(5).find("textarea").val("");
        });
    }
</script>

