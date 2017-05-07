<?php
?>
<html>
            <head>

    <link rel="stylesheet" type="text/css" href="<?= $this->e($this->getResource('resource/css/070-fullcalendar.css')) ?>">
            <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/001-jquery.js')) ?>"></script>
            <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/080-moment.js')) ?>"></script>
            <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/120-fullcalendar.js')) ?>"></script>
            </head>
            <body>
            <div id="cal" class="schedule2">adasdasd</div>
<script>
    jQuery("#cal").fullCalendar({
                    header: {
                right:  'agendaWeek'
            },
            locale: "en",
            defaultView: 'agendaWeek',
    });
</script>
            </body>
</html>