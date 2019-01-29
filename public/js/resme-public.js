(function ($) {
	'use strict';



	function loadFacilitySchedule() {

		$.ajax({
			type: "POST",
			url: my_ajax_object.ajax_url,
			data: "action=get_facility_schedule" +
				"&id=" + $("#courtData").attr('data-courtid') +
				"&mayEdit=" + $("#courtData").attr('data-mayedit') +
				"&startDate=" + $("#courtData").attr('data-startdate') +
				"&endDate=" + $("#courtData").attr('data-enddate') +
				"&displayDays=" + $("#courtData").attr('data-displaydays'),
			success: function (output) {
				$("#courtReservations").html(output);
			}
		});
	}

	function addDaysToDateString(passeddate, days) {
		// add days to a string that is passed as 'yyyy-mm-dd'
		// and return as a string in the same format.

		var dateParts = passeddate.split('-');
		var d = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);

		d.setDate(d.getDate() + parseInt(days));

		return [
			d.getFullYear(),
			('0' + (d.getMonth() + 1)).slice(-2),
			('0' + d.getDate()).slice(-2)
		  ].join('-');
	}

	function calcDisplayDates(passedDate) {
		//center the passed in date on the schedule
		var displayDays = parseInt($("#courtData").attr('data-displaydays'));
		var startDays = parseInt(displayDays / 2);

		var startDate = addDaysToDateString(passedDate, -startDays);
		var endDate = addDaysToDateString(passedDate, displayDays - startDays);

			
		// update the current start and end times
		$("#courtData").attr('data-startdate', startDate);
		$("#courtData").attr('data-enddate', endDate);


	};

	function displayAlert(msg) {
		$("#messageText").text(msg);
		$("#message").show();
	}

	$(document).ready(function () {

		const backendURL = $("form.resform").attr('action');
		const displayDays = $("#courtData").attr('data-displaydays');
		const maxDate = $("#courtData").attr('data-maxdate');
		const minDate = $("#courtData").attr('data-mindate');
		
		$('#loader').hide();

		$(".ui-widget-overlay").live('click', function () {
			$(".ui-dialog-titlebar-close").trigger('click');
		});



		$("div.datepager").on("click", "button", function() {
			var change = $(this).attr('id');
			var startDate = $("#courtData").attr('data-startdate');
			var endDate = $("#courtData").attr('data-enddate');
			var changeDays = 0;
			switch (change) {
				case "page-left":
					changeDays = -parseInt(displayDays);
					break;
				case "left":
					changeDays = -1;
					break;
				case "right":
					changeDays = 1;
					break;
				case "page-right":
					changeDays = parseInt(displayDays);
					break;
			} 
			startDate = addDaysToDateString(startDate, changeDays);
			endDate = addDaysToDateString(endDate, changeDays);

			// check to make sure we are not passed the minimum.
			if (startDate < minDate) {
				startDate = minDate;
				endDate = addDaysToDateString(startDate, displayDays);
				displayAlert('Reached the end of calendar');
			}

			// check to make sure we are not passed the maximum.
			if (endDate > maxDate) {
				endDate = maxDate;
				startDate = addDaysToDateString(endDate, - displayDays);
				displayAlert('Reached the end of calendar');
			}

			// update the current start and end times
			$("#courtData").attr('data-startdate', startDate);
			$("#courtData").attr('data-enddate', endDate);
		
			//refresh the screen
			loadFacilitySchedule();
		});


		$("table.reservations").on("click", "a.delete", function () {
			$.ajax({
				type: "POST",
				url: backendURL,
				data: "action=add_reservation&id=" + $(this).attr('data-id') + "&delete=true",
				beforeSend: function() {
					$("#loader").show();
				},
				success: function (msg) {
					loadFacilitySchedule();
					$("#loader").hide();
				},
				error: function (err) {
					console.error(err.responseText);
					$("#loader").hide();
				}
			});
		});

		
		$("#btnSearch").click( function() {
			var searchDate = $("#searchDate").val();
			if (searchDate === undefined) return;

			// check to make sure dates are in range.
			if (searchDate > maxDate || searchDate < minDate) {
				displayAlert("Search went beyond the end of the calendar");
				return;
			}

			calcDisplayDates(searchDate);
			loadFacilitySchedule();
		});

		$("#today").click( function() {
			var today = new Date();
			var searchDate = today.getFullYear() + '-' + today.getMonth() + 1 + '-' + today.getDate(); 
			calcDisplayDates(searchDate);
			loadFacilitySchedule();
		});

		$("#messageAlert").click(function() {
			$("#message").hide();
		});

		$("table.reservations").on('click', "a.reservation", function (e) {
			var d = $('#dialog');
			d.find('[name="day"]').val($(this).attr('data-day'));
			d.find('[name="hour"]').val($(this).attr('data-hour'));
			d.find('#date').val($(this).attr('data-date') + ' from ' + $(this).attr('data-time'));

		});

		$("button#submit").click(function(){
			$.ajax({
				type: "POST",
				url: backendURL,
				data: $('#resform').serialize(),
				beforeSend: function() {
					$("#loader").show();
				},
			
				success: function (msg) {
					loadFacilitySchedule();
					$("#loader").hide();
				},
				error: function (xhr, status, error) {
					var errmsg = xhr.responseText;
					displayAlert(errmsg);
					console.error(xhr.responseText + ' ' + errmsg);
					$("#loader").hide();
				}
			});
		})

	

		if (displayDays !== undefined) loadFacilitySchedule();
	});
})(jQuery);