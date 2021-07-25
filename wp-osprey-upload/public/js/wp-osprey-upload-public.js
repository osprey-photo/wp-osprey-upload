// Part of wp-osprey-upload
//
// SPDX-License-Identifier: GPL-2.0-or-later
//
// Matthew B White https://github.com/osprey-photo/wp-osprey-upload

(function ($) {
	"use strict";

	const NAMETITLE_SEP = "%";

	console.log("Hello Upload starting");
	const username = "";
	const showDetails = function () {
		var details = $('#filedetails :input[name="title"]');

		$(details).each(function (index) {
			var imgid = details[index].id;
			var title = details[index].value;
			var purpose = $(`#${imgid}-purpose option:selected`).text();
			let purposeId = $(`#${imgid}-purpose option:selected`).val();
			// if (purpose.startsWith("Other")) {
			// 	purpose = "Other:" + $(`#${imgid}-other`).val();
			// }
			var file = $(".my-pond").filepond("getFile", imgid);
			console.log(` ID=${imgid} Title=${title}, Purpose=${purpose} ${purposeId}`);
			file.setMetadata({
				title,
				purpose:purposeId,
				imgid,
				filename: file.filename,
				username: php_vars.user,
				displayname: php_vars.displayname
			});
			$(".my-pond").filepond("processFile", imgid);
		});
		//;
	};

	var isLoadingCheck = function () {
		var isLoading =
			$(".my-pond")
				.filepond("getFiles")
				.filter((x) => x.status !== 5).length !== 0;
		if (isLoading) {
			console.log("Loading images...");
		} else {
			console.log("Images loaded");
			var dataString = new FormData($("#ofu_uploadform")[0]);
			$.ajax({
				type: "POST",
				url: "/osprey/api/submit.php",
				contentType: false,
				data: dataString,
				processData: false,
				success: function (data, textStatus) {
					console.log("done [" + textStatus + "] " + data);
					$("#results").text(
						"Thanks!  If you wish to submit more, please refresh the page."
					);
					$("#osprey-submit-btn").prop("disabled", true);
					$("#osprey-submit-btn span").text("All submitted");
				},
			});
		}
	};

	$(function () {
		if (typeof php_vars === "undefined") {
			return;
		}
		console.log(php_vars);

		// Turn a file input into a file pond
		// var pond = FilePond.create(document.querySelector('input[type="file"]'));
		// First register any plugins
		$.fn.filepond.registerPlugin(
			FilePondPluginImagePreview,
			FilePondPluginFileRename,
			FilePondPluginFileMetadata,
			FilePondPluginImageExifOrientation
		);

		// Turn input element into a pond with configuration options
		var pond = $(".my-pond").filepond({
			allowMultiple: true,
			// upload to this server end point
			server: "/osprey/api/index.php",
			instantUpload: false,
			allowRevert: false,
			allowProcess: false,
			imageResizeTargetWidth: 1600,
			imageResizeMode: "contain",
			fileMetadataObject: {
				user: `${php_vars.user}`,
			},
			onaddfilestart: (file) => {
				isLoadingCheck();
			},
			onprocessfile: (files) => {
				isLoadingCheck();
			},
			fileRenameFunction: (file) => {
				return `${file.name}`;
			},
		});


		// Listen for addfile event
		// A file has been added to the queue for uploading
		$(".my-pond").on("FilePond:addfile", function (e) {
			$("#osprey-submit-btn").prop("disabled", false);

			console.log("file added event", e.detail.file);
			var fileUploaded = e.detail.file;
			let filerowId = `${fileUploaded.id}-row`;

			let purposeOptions = php_vars.purposes
				.map((p) => {
					return `<option value="${p.id}">${p.title}</option>`;
				})
				.join("\n");

			const reasonSelect = `
<select id="${fileUploaded.id}-purpose" name="purpose">
${purposeOptions}
</select> 
`;

			/*
			The oringal other field
			<input type="text" id="${fileUploaded.id}-other" name="other">
			*/

			let trimmed_title = fileUploaded.filenameWithoutExtension.substring(
				fileUploaded.filenameWithoutExtension.indexOf(NAMETITLE_SEP) + 1
			);

			const titleInput = `<input type="text" id="${fileUploaded.id}" name="title" value="${trimmed_title}">`;

			$("#filedetails > tbody:last-child").append(
				`<tr id="${filerowId}"><td>${fileUploaded.filename}</td><td>${titleInput}</td> <td>${reasonSelect}</td></tr>`
			);
		});

		$(".my-pond").on("FilePond:removefile", function (e) {
			console.log("file added event", e.detail.file);
			let filerowId = `${e.detail.file.id}-row`;
			$(`#${filerowId}`).remove();
		});

		$(".button").click(function (e) {
			e.preventDefault();

			var details = $('#filedetails :input[name="title"]');
			let inError = false;
			// validate the input
			$(details).each(function (index) {
				var imgid = details[index].id;
				var title = details[index].value;
				if (title.trim() === "") {
					$("#results").text(`Please make sure images have titles`);
					inError = true;
				}
			});

			if (!inError) {
				pond.disabled = true;
				showDetails();
				$("#osprey-submit-btn").prop("disabled", true);
				$("#osprey-submit-btn span").text("Submission in progress");
				$("#results").text("Working...");
			}
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

	$(".addMediaLibrary").click(function (e) {
		var productID = $(this).attr("imageid");
		$.ajax({
			url: "/wp-admin/admin-ajax.php",
			type: "POST",
			dataType: "JSON",
			data: {
				// the value of data.action is the part AFTER 'wp_ajax_' in
				// the add_action ('wp_ajax_xxx', 'yyy') in the PHP above
				action: "call_add_media_library",
				// ANY other properties of data are passed to your_function()
				// in the PHP global $_REQUEST (or $_POST in this case)
				id: productID,
			},
			success: function (resp) {
				if (resp.success) {
					// if you wanted to use the return value you set
					// in your_function(), you would do so via
					// resp.data, but in this case I guess you don't
					// need it
					console.log("Got response");
				} else {
					// this "error" case means the ajax call, itself, succeeded, but the function
					// called returned an error condition
					alert("Error: " + resp.data);
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {
				// this error case means that the ajax call, itself, failed, e.g., a syntax error
				// in your_function()
				alert("Request failed: " + thrownError.message);
			},
		});
	});
})(jQuery);
