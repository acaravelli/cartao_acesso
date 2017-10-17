$(function() {
	var segundos = 30;

	var datepicker_cfg = {
		numberOfMonths: 2,
		showButtonPanel: true
	};

	var init = function()
	{
		$('[data-toggle="tooltip"]').tooltip();
		$(':text[data-mask="dd/mm/yyyy"]').mask('99/99/9999');
	}

	init();

	$("#f_data").datepicker({
		changeMonth: true,
		maxDate: '0',
		onClose: function(selectedDate) {
			selectedDate = selectedDate.split('/');

			var date_ini = new Date(selectedDate[2], parseInt(selectedDate[1])-1, selectedDate[0]);
			var date_fim = new Date(selectedDate[2], parseInt(selectedDate[1])-1, selectedDate[0]);
			var hoje = new Date;

			date_fim.setDate(date_fim.getDate() + 120);

			if (date_fim > hoje) {
				date_fim = hoje;
			}

			$( "#f_data_fim" ).val($.datepicker.formatDate('dd/mm/yy', date_fim));
			$( "#f_data_fim" ).datepicker( "option", "minDate", date_ini);
			$( "#f_data_fim" ).datepicker( "option", "maxDate", date_fim );
		}
	});

	$("#f_data_fim" ).datepicker({
		defaultDate: "+1w",
		minDate: $("#f_data").val(),
		maxDate: '0',
		changeMonth: true
	});

	$('#contador').bind('contadorTempo', function(e, segundos)
	{
		$(this).text(segundos);
		segundos = segundos - 1;

		if (segundos == -1) {
			document.location.href = '/rcfs/cartao_acesso/relatorio.php';
			return;
		}

		setTimeout(function()
		{
			$('#contador').trigger('contadorTempo', [segundos]);
		}, 1000);
	});

	$(window).load(function()
	{
		$('#contador').trigger('contadorTempo', [segundos]);
	});
});