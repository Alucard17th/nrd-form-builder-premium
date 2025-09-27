(function ($) {
  "use strict";

  // Run on DOM ready
  jQuery(function ($) {
    //
    // 1) RENDER ALL INSTANCES
    //
    function getFormJsonFromScript($jsonTag) {
      if (!$jsonTag || !$jsonTag.length) return "[]";
      var txt = ($jsonTag.text() || "").trim();
      if (!txt) return "[]";

      // If it accidentally got double-encoded (e.g. '"[{}]"'), try to unquote once
      if (
        (txt.startsWith('"') && txt.endsWith('"')) ||
        (txt.startsWith("'") && txt.endsWith("'"))
      ) {
        try {
          txt = JSON.parse(txt);
        } catch (_) {}
      }
      // If we somehow have an object already, stringify
      if (typeof txt !== "string") {
        try {
          txt = JSON.stringify(txt);
        } catch (_) {
          txt = "[]";
        }
      }
      return txt;
    }

    // function renderAllForms() {
    //   // --- Preferred: new instance-safe markup ---
    //   // Each form has class .nrd-fb-form and a per-form JSON script tag: #nrd-fb-json-{id}
    //   var $instanceForms = $(".nrd-fb-form");
    //   if ($instanceForms.length) {
    //     $instanceForms.each(function () {
    //       var $form = $(this);
    //       var formId =
    //         $form.data("form-id") || $form.find('input[name="form_id"]').val();

    //       var $json = $("#nrd-fb-json-" + formId);
    //       var formDataJson = getFormJsonFromScript($json);

    //       try {
    //         $form.formRender({ formData: formDataJson });
    //       } catch (e) {
    //         console.error("NRD FB render error (instance):", e);
    //       }
    //     });
    //     return; // done
    //   }

    //   // --- Legacy fallback: old markup with duplicated ids (#fb-rendered-form) and global formRenderData ---
    //   // NOTE: This will render the SAME form JSON into all legacy containers (limitation of legacy PHP)
    //   var $legacyForms = $('[id="fb-rendered-form"]'); // attribute selector returns ALL elements even if id is duplicated
    //   if ($legacyForms.length && typeof window.formRenderData !== "undefined") {
    //     $legacyForms.each(function () {
    //       try {
    //         $(this).formRender({ formData: window.formRenderData });
    //       } catch (e) {
    //         console.error("NRD FB render error (legacy):", e);
    //       }
    //     });
    //   }
    // }

    function renderAllForms() {
      // Loop over each instance wrapper
      $(".nrd-fb-instance").each(function () {
        var $inst = $(this);
        var $form = $inst.find(".nrd-fb-form");
        var $target = $inst.find(".nrd-fb-render-target");

        // Get form id and JSON script tag scoped to this instance
        var formId =
          $form.data("form-id") || $form.find('input[name="form_id"]').val();

        var $json = $inst.find("#nrd-fb-json-" + formId);
        var formDataJson = getFormJsonFromScript($json); // your helper

        try {
          // IMPORTANT: render into the target div, not the <form>
          $target.empty().formRender({ formData: formDataJson });
        } catch (e) {
          console.error("NRD FB render error:", e);
        }
      });

      // --- Legacy fallback (unchanged) ---
      var $legacyForms = $('[id="fb-rendered-form"]');
      if ($legacyForms.length && typeof window.formRenderData !== "undefined") {
        $legacyForms.each(function () {
          try {
            $(this).formRender({ formData: window.formRenderData });
          } catch (e) {
            console.error("NRD FB render error (legacy):", e);
          }
        });
      }
    }

    renderAllForms();

    //
    // 2) SUBMIT HANDLER (per instance)
    //
    $(document).on("submit", ".nrd-fb-form", function (e) {
      e.preventDefault();
      var $form = $(this);
      var fd = new FormData(this);

      // WP AJAX action
      fd.append("action", "post_nrd_wp_fb");

      // --- 👇 Ensure nonce + form_id are present (even if nested form caused them to be missed)
      var formId = $form.find('input[name="form_id"]').val();
      var nonce = $form.find('input[name="nrd_fb_nonce"]').val();

      // Fallbacks (legacy/edge cases)
      if (!formId) {
        formId =
          $form.closest(".nrd-fb-instance").data("form-id") ||
          $('[id="form_id"]').first().val() ||
          "";
      }
      if (!nonce) {
        nonce =
          $form.closest(".nrd-fb-instance").data("nonce") ||
          $('[id="nrd_fb_nonce"]').first().val() ||
          "";
      }

      fd.set("form_id", formId);
      fd.set("nrd_fb_nonce", nonce);

      if (!formId || !nonce) {
        console.error("Missing form_id or nonce", { formId, nonce });
        alert("Form configuration error. Please refresh and try again.");
        return;
      }

      var $submitBtn = $form
        .find('button[type="submit"], input[type="submit"]')
        .first();
      if ($submitBtn.length) {
        $submitBtn.prop("disabled", true);
        if ($submitBtn.is("button")) $submitBtn.text("Loading...");
        if ($submitBtn.is("input")) $submitBtn.val("Loading...");
      }

      $(".nrd-form-bd-message", $form).remove();

      $.ajax({
        url:
          window.my_ajax_object && my_ajax_object.ajax_url
            ? my_ajax_object.ajax_url
            : window.ajaxurl || "/wp-admin/admin-ajax.php",
        method: "POST",
        data: fd,
        contentType: false,
        processData: false,
      })
        .done(function (res) {
          if ($submitBtn.length) {
            $submitBtn.prop("disabled", false);
            if ($submitBtn.is("button")) $submitBtn.text("Submit");
            if ($submitBtn.is("input")) $submitBtn.val("Submit");
          }
          $(
            '<div class="nrd-form-bd-message nrd-form-bd-success-message">Form submitted successfully!</div>'
          ).insertAfter($submitBtn.length ? $submitBtn : $form);
          // Optional: reset only text/file inputs, keep hidden meta
          try {
            $form[0].reset();
          } catch (_) {}
        })
        .fail(function (xhr) {
          if ($submitBtn.length) {
            $submitBtn.prop("disabled", false);
            if ($submitBtn.is("button")) $submitBtn.text("Submit");
            if ($submitBtn.is("input")) $submitBtn.val("Submit");
          }
          console.error(
            "Submit error:",
            (xhr && (xhr.responseJSON || xhr.responseText)) || xhr
          );
          $(
            '<div class="nrd-form-bd-message nrd-form-bd-error-message">An error occurred while submitting the form. Please try again later.</div>'
          ).insertAfter($submitBtn.length ? $submitBtn : $form);
        });
    });

    //
    // 3) LEGACY SUBMIT FALLBACK
    // If you haven’t moved to the new markup yet, also bind legacy containers.
    //
    $(document).on("submit", '[id="fb-rendered-form"]', function (e) {
      // If we already matched .nrd-fb-form above, skip
      if ($(this).hasClass("nrd-fb-form")) return;

      e.preventDefault();
      var $form = $(this);
      var fd = new FormData(this);
      var $submitBtn = $form
        .find('button[type="submit"], input[type="submit"]')
        .first();

      // These hidden inputs were printed outside the <form> in legacy markup.
      // We’ll try to read them by attribute selector (may still collide if multiple shortcodes are present).
      var sheetId = $('[id="google_sheet_id"]').first().val() || "";
      var sheetPage = $('[id="google_sheet_page"]').first().val() || "";
      var formTitle = $('[id="form_title"]').first().val() || "";
      var formIdHidden = $('[id="form_id"]').first().val() || ""; // if present
      var nonceHidden = $('[id="nrd_fb_nonce"]').first().val() || "";

      fd.append("action", "post_nrd_wp_fb");
      if (sheetId) fd.append("google_sheet_id", sheetId);
      if (sheetPage) fd.append("google_sheet_page", sheetPage);
      if (formTitle) fd.append("form_title", formTitle);
      if (formIdHidden) fd.append("form_id", formIdHidden);
      if (nonceHidden) fd.append("nrd_fb_nonce", nonceHidden);

      if ($submitBtn.length) {
        $submitBtn.prop("disabled", true);
        if ($submitBtn.is("button")) $submitBtn.text("Loading...");
        if ($submitBtn.is("input")) $submitBtn.val("Loading...");
      }

      $(".nrd-form-bd-message", $form).remove();

      $.ajax({
        url:
          window.my_ajax_object && my_ajax_object.ajax_url
            ? my_ajax_object.ajax_url
            : window.ajaxurl || "/wp-admin/admin-ajax.php",
        method: "POST",
        data: fd,
        contentType: false,
        processData: false,
      })
        .done(function () {
          if ($submitBtn.length) {
            $submitBtn.prop("disabled", false);
            if ($submitBtn.is("button")) $submitBtn.text("Submit");
            if ($submitBtn.is("input")) $submitBtn.val("Submit");
          }
          $(
            '<div class="nrd-form-bd-message nrd-form-bd-success-message">Form submitted successfully!</div>'
          ).insertAfter($submitBtn.length ? $submitBtn : $form);
          try {
            $form[0].reset();
          } catch (_) {}
        })
        .fail(function (xhr) {
          if ($submitBtn.length) {
            $submitBtn.prop("disabled", false);
            if ($submitBtn.is("button")) $submitBtn.text("Submit");
            if ($submitBtn.is("input")) $submitBtn.val("Submit");
          }
          console.error(
            "Submit error (legacy):",
            (xhr && (xhr.responseJSON || xhr.responseText)) || xhr
          );
          $(
            '<div class="nrd-form-bd-message nrd-form-bd-error-message">An error occurred while submitting the form. Please try again later.</div>'
          ).insertAfter($submitBtn.length ? $submitBtn : $form);
        });
    });
  });
})(jQuery);
