// Part of wp-osprey-upload
//
// SPDX-License-Identifier: GPL-2.0-or-later
//
// Matthew B White https://github.com/osprey-photo/wp-osprey-upload
(function( $ ) {
	'use strict';
	
	const username='';
	const showDetails = function () {
		var details = $('#filedetails :input[name="title"]')
		console.log(php_vars);
		$(details).each(function (index) {
			var imgid = details[index].id;
			var title = details[index].value;
			var purpose = $(`#${imgid}-purpose option:selected`).text();
			var file = $('.my-pond').filepond('getFile', imgid);
			console.log(` ID=${imgid} Title=${title}, Purpose=${purpose}`)
			file.setMetadata({title, purpose, imgid, filename: file.filename, username:php_vars.user})
			$('.my-pond').filepond('processFile', imgid);
		});
		//;
	}

	var isLoadingCheck = function () {
		var isLoading = $('.my-pond')
			.filepond('getFiles')
			.filter(x => x.status !== 5)
			.length !== 0;
		if (isLoading) {
			console.log('Loading images...')
		} else {
			console.log('Images loaded')
			var dataString = new FormData($('#ofu_uploadform')[0])
			$.ajax({
				type: "POST",
				url: "osprey/api/submit.php",
				contentType: false,
				data: dataString,
				processData: false,
				success: function (data, textStatus) {
					console.log('done [' + textStatus + '] ' + data);
					$('#results').text("All submitted.. thanks!");
				}
			});
		}
	}

	$(function () {
		if (typeof php_vars === "undefined"){
			return;
		}
		console.log(php_vars);
		
		// Turn a file input into a file pond
		// var pond = FilePond.create(document.querySelector('input[type="file"]'));
		// First register any plugins
		$
			.fn
			.filepond
			.registerPlugin(FilePondPluginImagePreview,FilePondPluginFileRename,FilePondPluginFileMetadata,FilePondPluginImageExifOrientation,FilePondFileResize,FilePondPluginImageTransform/*, , FilePondPluginFileValidateSize, , , */);

		// Turn input element into a pond with configuration options
		var pond = $('.my-pond').filepond({
			allowMultiple: true,
			// upload to this server end point
			server: 'osprey/api/index.php',
			instantUpload: false,
			imageResizeTargetWidth: 800,
			imageResizeMode: 'contain',
			fileMetadataObject: {
				'hello': 'world'
			},
			onaddfilestart: (file) => {
				isLoadingCheck();
			},
			onprocessfile: (files) => {
				isLoadingCheck();
			},
			fileRenameFunction: (file) => {
				console.log(file);
				return `${php_vars.user}_${file.name}`;
			}
		});
               // Listen for addfile event
			   $('.my-pond').on('FilePond:addfile', function (e) {
				console.log('file added event', e.detail.file);
				var fileUploaded = e.detail.file;

				const reasonSelect = `  <select id="${fileUploaded.id}-purpose" name="purpose">
<option value="competition">Competition</option>
<option value="exhibition">Exhibition</option>
<option value="other">Other</option>
</select>`;

				const titleInput = `<input type="text" id="${fileUploaded.id}" name="title">`;
				var trimmed_filename = fileUploaded.filename.substring(fileUploaded.filename.indexOf('_')+1);
				$('#filedetails > tbody:last-child').append(`<tr><td>${ trimmed_filename}</td><td>${titleInput}</td> <td>${reasonSelect}</td></tr>`)

			});

			$(".button").click(function (e) {
				e.preventDefault();

				showDetails();

				$('#results').text("Submitting...");
			});
		});		
	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

})( jQuery );
