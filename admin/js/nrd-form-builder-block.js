(function (wp) {
  var registerBlockType = wp.blocks.registerBlockType;
  var el = wp.element.createElement;
  var SelectControl = wp.components.SelectControl;
  var PanelBody = wp.components.PanelBody;
  var InspectorControls = wp.blockEditor.InspectorControls;
  var useSelect = wp.data.useSelect;

  registerBlockType("nrd/form-builder", {
    title: "NRD Form Builder",
    icon: "feedback",
    category: "widgets",
    attributes: {
      formId: {
        type: "number",
        default: 0,
      },
    },
    edit: function (props) {
      var formId = props.attributes.formId || 0;

      var forms = useSelect(
        function (select) {
          return select("core").getEntityRecords("postType", "nrd-form-bd", {
            per_page: -1,
            orderby: "title",
            order: "asc",
          });
        },
        []
      );

      var options = [{ label: "Select a form", value: 0 }];
      if (forms && forms.length) {
        forms.forEach(function (f) {
          options.push({ label: f.title && f.title.rendered ? f.title.rendered : "(no title)", value: f.id });
        });
      }

      return el(
        wp.element.Fragment,
        {},
        el(
          InspectorControls,
          {},
          el(
            PanelBody,
            { title: "Form Settings", initialOpen: true },
            el(SelectControl, {
              label: "Form",
              value: formId,
              options: options,
              onChange: function (newVal) {
                props.setAttributes({ formId: parseInt(newVal || 0, 10) });
              },
            })
          )
        ),
        el(
          "div",
          { className: props.className },
          formId
            ? "NRD Form: #" + formId
            : "Select a form from the block settings (right sidebar)."
        )
      );
    },
    save: function () {
      return null;
    },
  });
})(window.wp);
