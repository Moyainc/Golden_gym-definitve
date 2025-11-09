document.addEventListener("DOMContentLoaded", async () => {
    /* ============================================================
     📅 FLATPICKR – Calendario Dashboard
  ============================================================ */
    const calendar = document.getElementById("datetimepicker-dashboard");
    if (calendar) {
        flatpickr(calendar, {
            inline: true,
            dateFormat: "Y-m-d",
            defaultDate: "today",
            locale: {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: [
                        "Dom",
                        "Lun",
                        "Mar",
                        "Mié",
                        "Jue",
                        "Vie",
                        "Sáb",
                    ],
                    longhand: [
                        "Domingo",
                        "Lunes",
                        "Martes",
                        "Miércoles",
                        "Jueves",
                        "Viernes",
                        "Sábado",
                    ],
                },
                months: {
                    shorthand: [
                        "Ene",
                        "Feb",
                        "Mar",
                        "Abr",
                        "May",
                        "Jun",
                        "Jul",
                        "Ago",
                        "Sep",
                        "Oct",
                        "Nov",
                        "Dic",
                    ],
                    longhand: [
                        "Enero",
                        "Febrero",
                        "Marzo",
                        "Abril",
                        "Mayo",
                        "Junio",
                        "Julio",
                        "Agosto",
                        "Septiembre",
                        "Octubre",
                        "Noviembre",
                        "Diciembre",
                    ],
                },
            },
        });
    }
});
