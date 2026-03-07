(function ($) {
  "use strict";

  /**
   * All of the code for your admin-facing JavaScript source
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
  $(function () {
    // Guard + helper
    const hasLocalized = typeof nrdFormBD !== "undefined" && nrdFormBD;
    const ajaxUrl =
      hasLocalized && nrdFormBD.ajax_url ? nrdFormBD.ajax_url : undefined;
    const adminNonce =
      hasLocalized && nrdFormBD.nonce ? nrdFormBD.nonce : undefined;

    const withNonce = (data = {}) => {
      if (!adminNonce) {
        return Object.assign({}, data);
      }
      return Object.assign({}, data, { nonce: adminNonce });
    };

    var $fbEditor = $(document.getElementById("fb-editor"));
    var $formContainer = $(document.getElementById("fb-rendered-form"));
    var formBuilder = null;

    if ($fbEditor.length) {
      var fbOptions = {
        onSave: function () {
          let saveBtn = $(".save-template");
          // $fbEditor.toggle();
          // $formContainer.toggle();
          $("form", $formContainer).formRender({
            formData: formBuilder.formData,
          });
          var postIdInput = document.getElementById("post_id");
          var postId = postIdInput ? postIdInput.value : null;
          saveNrdFb(formBuilder.formData, postId, saveBtn);
          // make the clicked save button disabled
        },
      };

      // Check if formData is defined
      if (typeof formData !== "undefined") {
        // Add formData to fbOptions
        fbOptions.formData = formData;
      }

      formBuilder = $fbEditor.formBuilder(fbOptions);

      $(".edit-form", $formContainer).click(function () {
        $fbEditor.toggle();
        $formContainer.toggle();
      });
    }

    function saveNrdFb(data, postID, saveBtn) {
      saveBtn.prop("disabled", true);
      saveBtn.text("Saving...");
      let title = document.getElementById("title").value;
      let googleSheetID = document.getElementById("google_sheet_id")
        ? document.getElementById("google_sheet_id").value
        : "";
      let googleSheetPage = document.getElementById("google_sheet_page")
        ? document.getElementById("google_sheet_page").value
        : "";
      if (title == "") {
        title = "Untitled Form";
      }

      if (googleSheetID !== "" && googleSheetPage == "") {
        // Get the google_sheet_page element
        let googleSheetPageInput = document.getElementById("google_sheet_page");

        // Check if an error message already exists, to avoid duplicate messages
        let existingError = document.getElementById("google_sheet_error");

        if (!existingError) {
          // Create an error message element
          let errorMessage = document.createElement("span");
          errorMessage.id = "google_sheet_error"; // Assign an ID to prevent duplicate errors
          errorMessage.style.color = "red"; // Style the error message (you can also add more styling)
          errorMessage.textContent =
            "Error: Please provide a value for Google Sheet Page";

          // Insert the error message after the google_sheet_page input
          googleSheetPageInput.parentNode.insertBefore(
            errorMessage,
            googleSheetPageInput.nextSibling
          );
        }

        saveBtn.prop("disabled", false);
        saveBtn.text("Save");
        return;
      }

      $.ajax({
        //   data: {
        //     action: "save_nrd_wp_fb",
        //     title: title,
        //     content: data,
        //     post_id: postID,
        //     google_sheet_id: googleSheetID,
        //     google_sheet_page: googleSheetPage,
        //   },
        data: withNonce({
          action: "save_nrd_wp_fb",
          title: title,
          content: data,
          post_id: postID,
          google_sheet_id: googleSheetID,
          google_sheet_page: googleSheetPage,
        }),
        type: "post",
        url: ajaxUrl,
        success: function (data) {
          let editUrl = data.data;
          window.location.href = editUrl;
        },
        error: function (jqXHR, textStatus, errorThrown) {
        },
      });
    }

      $(".nrd-short-code").click(function () {
        const postID = $("#post_id").val();
        const customerLink = '[nrd_form_bd id="' + postID + '"]';
        navigator.clipboard.writeText(customerLink);
        $(".nrd-short-code-copy").show();
        // hide the copy button after 3 seconds
        setTimeout(function () {
          $(".nrd-short-code-copy").hide();
        }, 1500);
      });

      // activate license
      $(document)
        .off("click.nrd", "#nrd-form-bd-activate-button")
        .on("click.nrd", "#nrd-form-bd-activate-button", function (e) {
          e.preventDefault();
          const licenseKey = $("#nrd-form-bd-license-key").val();
          $("#nrd-form-bd-activate-button").prop("disabled", true);
          $("#nrd-form-bd-activate-button").text("Activating...");
          $(".nrd-form-bd-message").remove();

          $.ajax({
            data: withNonce({
              action: "nrd_form_bd_activate_license",
              license_key: licenseKey,
            }),
            type: "post",
            url: ajaxUrl,
            success: function (resp) {
              if (resp && resp.success) {
                window.location.reload();
                return;
              }

              const message =
                resp && resp.data && resp.data.message
                  ? resp.data.message
                  : "Activation failed";
              $(
                '<div class="nrd-form-bd-message nrd-form-bd-error-message"></div>'
              )
                .text(message)
                .insertAfter($("#nrd-form-bd-activate-button"));
            },
            error: function (jqXHR) {
              let message = "An unexpected error occurred.";
              if (jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.data) {
                message = jqXHR.responseJSON.data.message || message;
              }

              $(
                '<div class="nrd-form-bd-message nrd-form-bd-error-message"></div>'
              )
                .text(message)
                .insertAfter($("#nrd-form-bd-activate-button"));
            },
            complete: function () {
              $("#nrd-form-bd-activate-button").prop("disabled", false);
              $("#nrd-form-bd-activate-button").text("Activate");
            },
          });
        });

      // deactivate license
      $(document)
        .off("click.nrd", "#nrd-form-bd-deactivate-button")
        .on("click.nrd", "#nrd-form-bd-deactivate-button", function (e) {
          e.preventDefault();
          $("#nrd-form-bd-deactivate-button").prop("disabled", true);
          $("#nrd-form-bd-deactivate-button").text("Deactivating...");
          $(".nrd-form-bd-message").remove();

          $.ajax({
            data: withNonce({
              action: "nrd_form_bd_deactivate_license",
            }),
            type: "post",
            url: ajaxUrl,
            success: function (resp) {
              if (resp && resp.success) {
                window.location.reload();
                return;
              }

              const message =
                resp && resp.data && resp.data.message
                  ? resp.data.message
                  : "Deactivation failed";
              $(
                '<div class="nrd-form-bd-message nrd-form-bd-error-message"></div>'
              )
                .text(message)
                .insertAfter($("#nrd-form-bd-deactivate-button"));
            },
            error: function (jqXHR) {
              let message = "An unexpected error occurred.";
              if (jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.data) {
                message = jqXHR.responseJSON.data.message || message;
              }

              $(
                '<div class="nrd-form-bd-message nrd-form-bd-error-message"></div>'
              )
                .text(message)
                .insertAfter($("#nrd-form-bd-deactivate-button"));
            },
            complete: function () {
              $("#nrd-form-bd-deactivate-button").prop("disabled", false);
              $("#nrd-form-bd-deactivate-button").text("Deactivate");
            },
          });
        });

      // copy clicked item
      $(".copiable-item").click(function () {
        let element = $(this);
        // Create a temporary textarea element to hold the text to be copied
        const tempInput = document.createElement("textarea");
        // Get the text content of the clicked element using jQuery's .text() method
        tempInput.value = element.text();
        // Append the textarea element to the document
        document.body.appendChild(tempInput);
        // Select the text in the textarea
        tempInput.select();
        // Copy the selected text to the clipboard
        document.execCommand("copy");
        // Remove the temporary textarea element
        document.body.removeChild(tempInput);

        // Optional: You can provide feedback to the user
        alert("Copied to clipboard: " + tempInput.value);
      });

      // SMTP Test
      $("#nrd-smtp-test-form").on("submit", function (e) {
        e.preventDefault();

        var data = {
          action: "nrd_smtp_test_ajax",
          smtp_to: $("#nrd_smtp_to").val(),
          _ajax_nonce: hasLocalized ? nrdFormBD.smtp_nonce : undefined,
        };

        $("#nrd-smtp-notice").html("<p>Sending test email...</p>");

        $.post(ajaxUrl, data, function (response) {
          if (response.success) {
            $("#nrd-smtp-notice").html(
              '<div class="notice notice-success"><p>' +
                response.data +
                "</p></div>"
            );
          } else {
            $("#nrd-smtp-notice").html(
              '<div class="notice notice-error"><p>' +
                response.data +
                "</p></div>"
            );
          }
        });
      });
  });
})(jQuery);
