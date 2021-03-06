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
        <li><a href="ShowCoursesLists.php"><i class="fa fa-arrow-circle-right"></i> Ver Estudiantes</a></li>
        <li><a href="#"><i class="fa fa-arrow-circle-right"></i> Estudiantes</a></li>
    </ol>
</section>
<br>

<?php
if (isset($course) && is_int($course) && isset($professor) && is_int($professor) && isset($year) && is_int($year) && isset($year) && is_int($year) && isset($group) && is_int($group)) {
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
                            <a type="button" class="btn btn-primary pull-right" title="Exportar como archivo de excel."
                            href="../actions/ExportStudentsListByCourseAndProfessorAction.php?course=<?php echo $course; ?>&period=<?php echo $period; ?>&year=<?php echo $year; ?>&group=<?php echo $group; ?>&professor=<?php echo $_SESSION['id']; ?>">Exportar</a>
                            <a type="button" class="btn btn-primary pull-right" title="Archivos para los estudiantes del grupo." style="margin-left:5px;margin-right:5px;"
                            href="FileShowByCourseAndProfessor.php?course=<?php echo $course; ?>&period=<?php echo $period; ?>&year=<?php echo $year; ?>&group=<?php echo $group; ?>&professor=<?php echo $_SESSION['id']; ?>">Archivos</a>
                            <a type="button" class="btn btn-primary pull-right" title="Exportar como archivo de excel."
                            href="ShowStudentsGradesByCourse.php?course=<?php echo $course; ?>&period=<?php echo $period; ?>&year=<?php echo $year; ?>&group=<?php echo $group; ?>&professor=<?php echo $_SESSION['id']; ?>">Calificaciones</a>
                            <?php
                            break;
                        }
                        ?>
                    </div>
                    <div class="table-responsive">
                        <div class="box-body">
                            <table id="studentsList" class="table table-bordered table-striped" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Cédula</th>
                                        <th>Teléfono</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody">
                                    <?php
                                    $students = $business->getStudentsListByCourseAndProfessor($course, $professor, $period, $year, $group);
                                    foreach ($students as $person) {
                                        ?>
                                        <tr>
                                            <td><?php echo $person[0]; ?></td>
                                            <td><?php echo $person[1]; ?></td>
                                            <td><?php echo $person[2]; ?></td>
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
    $(document).ready(function () {
        $("#studentsList").dataTable();
    });

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
    var action = $.get("action");
    var msg = $.get("msg");
    if (action === "1") {
        msg = msg.replace(/_/g, " ");
        alertify.success(msg);
    }
    if (action === "0") {
        msg = msg.replace(/_/g, " ");
        alertify.error(msg);
    }

    var data = [];
    function createInfo() {
        var isCorrect = true;
        var infoPerson;

        $('#tbody tr').each(function (index, element) {
            var name = $(element).find("td").eq(0).html();
            var present = $(element).find("td").eq(3).find("input");
            var absence = $(element).find("td").eq(4).find("input");
            var justification = $(element).find("td").eq(5).find("textarea").val();

            if ((present.is(':checked') && absence.is(':checked')) || (!present.is(':checked') && !absence.is(':checked'))) {
                isCorrect = false;
                if (name === "No se encuentran registros"){
                    alertify.error(name);
                }else{
                    alertify.error("Seleccione si el estudiante " + name + " esta ausente o presente");
                }
                
            } else {
                infoPerson = new Object();
                infoPerson.name = name;

                if (present.is(':checked')) {
                    infoPerson.present = 1;
                    infoPerson.absence = 0;
                }

                if (absence.is(':checked')) {
                    infoPerson.present = 0;
                    infoPerson.absence = 1;
                }

                infoPerson.justification = justification;
                data.push(infoPerson);
            }
        });
        return isCorrect;
    }

    function genarateAbsence() {
        if (createInfo()) {
            open("../reporter/Attendance.php?id=" + <?php echo $_SESSION['id']; ?> + "&idCourse=" + <?php echo $courseID; ?> + "&idGroup=" + <?php echo $groupID; ?> + "&data=" + JSON.stringify(data));
            data = [];
        } else {
            data = [];
        }
    }
</script>

