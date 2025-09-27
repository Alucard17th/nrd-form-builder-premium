<?php
if (!defined('ABSPATH'))
    exit;

// Free Version
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
// Premium Version
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;

class NRD_Form_Builder_Elementor_Widget extends Widget_Base
{

    public function get_name()
    {
        return 'nrd_form_builder';
    }

    public function get_title()
    {
        return 'NRD Form Builder';
    }

    public function get_icon()
    {
        return 'eicon-form-horizontal';
    }

    public function get_categories()
    {
        return ['general'];
    }

    protected function _register_controls()
    {

        /* ---------------------------------
         * CONTENT SECTION
         * --------------------------------- */
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Form Settings', 'nrd-form-builder'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'form_id',
            [
                'label' => __('Select Form', 'nrd-form-builder'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->get_all_forms(),
            ]
        );

        $this->end_controls_section();

        /* ---------------------------------
         * FORM (WRAPPER & SPACING)
         * --------------------------------- */
        $this->start_controls_section(
            'form_wrapper_section',
            [
                'label' => __('Form', 'nrd-form-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        // Max width + alignment
        $this->add_responsive_control(
            'form_max_width',
            [
                'label' => __('Max Width', 'nrd-form-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'vw'],
                'range' => [
                    'px' => ['min' => 200, 'max' => 1600],
                    '%' => ['min' => 10, 'max' => 100],
                    'vw' => ['min' => 10, 'max' => 100],
                ],
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'form_align',
            [
                'label' => __('Alignment', 'nrd-form-builder'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'nrd-form-builder'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'nrd-form-builder'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'nrd-form-builder'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'left',
                'toggle' => true,
                'selectors_dictionary' => [
                    'left' => 'margin-left:0;margin-right:auto;',
                    'center' => 'margin-left:auto;margin-right:auto;',
                    'right' => 'margin-left:auto;margin-right:0;',
                ],
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance' => '{{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'wrapper_bg',
            [
                'label' => __('Background Color', 'nrd-form-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'wrapper_padding',
            [
                'label' => __('Padding', 'nrd-form-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'wrapper_border',
                'label' => __('Border', 'nrd-form-builder'),
                'selector' => '{{WRAPPER}} .nrd-fb-instance',
            ]
        );

        $this->add_responsive_control(
            'wrapper_radius',
            [
                'label' => __('Border Radius', 'nrd-form-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'wrapper_shadow',
                'label' => __('Box Shadow', 'nrd-form-builder'),
                'selector' => '{{WRAPPER}} .nrd-fb-instance',
            ]
        );

        // Field vertical spacing
        $this->add_responsive_control(
            'field_gap',
            [
                'label' => __('Field Spacing (vertical)', 'nrd-form-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 80],
                    'em' => ['min' => 0, 'max' => 5],
                ],
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form .form-group' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* ---------------------------------
         * INPUTS (FIELDS)
         * --------------------------------- */
        $this->start_controls_section(
            'inputs_section',
            [
                'label' => __('Inputs', 'nrd-form-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        // Labels
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'label_typography',
                'label' => __('Label Typography', 'nrd-form-builder'),
                'selector' => '{{WRAPPER}} .nrd-fb-instance .rendered-form label',
            ]
        );

        $this->add_control(
            'label_color',
            [
                'label' => __('Label Color', 'nrd-form-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'required_color',
            [
                'label' => __('Required Asterisk Color', 'nrd-form-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form label .required, {{WRAPPER}} .nrd-fb-instance .rendered-form .required' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Help/description text
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'desc_typography',
                'label' => __('Description Typography', 'nrd-form-builder'),
                'selector' => '{{WRAPPER}} .nrd-fb-instance .rendered-form .form-text, {{WRAPPER}} .nrd-fb-instance .rendered-form .help-block, {{WRAPPER}} .nrd-fb-instance .rendered-form small',
            ]
        );

        $this->add_control(
            'desc_color',
            [
                'label' => __('Description Color', 'nrd-form-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form .form-text, {{WRAPPER}} .nrd-fb-instance .rendered-form .help-block, {{WRAPPER}} .nrd-fb-instance .rendered-form small' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Inputs typography & colors
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'input_typography',
                'label' => __('Input Typography', 'nrd-form-builder'),
                'selector' => '{{WRAPPER}} .nrd-fb-instance .rendered-form input, {{WRAPPER}} .nrd-fb-instance .rendered-form select, {{WRAPPER}} .nrd-fb-instance .rendered-form textarea',
            ]
        );

        $this->add_control(
            'input_text_color',
            [
                'label' => __('Text Color', 'nrd-form-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form input, {{WRAPPER}} .nrd-fb-instance .rendered-form select, {{WRAPPER}} .nrd-fb-instance .rendered-form textarea' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_bg',
            [
                'label' => __('Background', 'nrd-form-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form input, {{WRAPPER}} .nrd-fb-instance .rendered-form select, {{WRAPPER}} .nrd-fb-instance .rendered-form textarea' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        // Placeholder
        $this->add_control(
            'placeholder_color',
            [
                'label' => __('Placeholder Color', 'nrd-form-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form input::placeholder,
                    {{WRAPPER}} .nrd-fb-instance .rendered-form textarea::placeholder' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Input padding / height
        $this->add_responsive_control(
            'input_padding',
            [
                'label' => __('Padding', 'nrd-form-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form input, {{WRAPPER}} .nrd-fb-instance .rendered-form select, {{WRAPPER}} .nrd-fb-instance .rendered-form textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'input_height',
            [
                'label' => __('Height (inputs only)', 'nrd-form-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 24, 'max' => 120]],
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form input:not([type="checkbox"]):not([type="radio"])' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Border, Radius, Shadow
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'input_border',
                'label' => __('Border', 'nrd-form-builder'),
                'selector' => '{{WRAPPER}} .nrd-fb-instance .rendered-form input, {{WRAPPER}} .nrd-fb-instance .rendered-form select, {{WRAPPER}} .nrd-fb-instance .rendered-form textarea',
            ]
        );

        $this->add_responsive_control(
            'input_radius',
            [
                'label' => __('Border Radius', 'nrd-form-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form input, {{WRAPPER}} .nrd-fb-instance .rendered-form select, {{WRAPPER}} .nrd-fb-instance .rendered-form textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'input_shadow',
                'label' => __('Box Shadow', 'nrd-form-builder'),
                'selector' => '{{WRAPPER}} .nrd-fb-instance .rendered-form input, {{WRAPPER}} .nrd-fb-instance .rendered-form select, {{WRAPPER}} .nrd-fb-instance .rendered-form textarea',
            ]
        );

        // Focus / Error states
        $this->add_control(
            'input_focus_border_color',
            [
                'label' => __('Focus Border Color', 'nrd-form-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form input:focus, {{WRAPPER}} .nrd-fb-instance .rendered-form select:focus, {{WRAPPER}} .nrd-fb-instance .rendered-form textarea:focus' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_focus_shadow',
            [
                'label' => __('Focus Glow (outline)', 'nrd-form-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form input:focus, {{WRAPPER}} .nrd-fb-instance .rendered-form select:focus, {{WRAPPER}} .nrd-fb-instance .rendered-form textarea:focus' => 'box-shadow: 0 0 0 3px {{VALUE}}33;',
                ],
            ]
        );

        $this->add_control(
            'input_error_color',
            [
                'label' => __('Error Text Color', 'nrd-form-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form .error, {{WRAPPER}} .nrd-fb-instance .rendered-form .invalid-feedback' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_error_border',
            [
                'label' => __('Error Border Color', 'nrd-form-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form input.error, {{WRAPPER}} .nrd-fb-instance .rendered-form select.error, {{WRAPPER}} .nrd-fb-instance .rendered-form textarea.error' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* ---------------------------------
         * BUTTONS (SUBMIT)
         * --------------------------------- */
        $this->start_controls_section(
            'buttons_section',
            [
                'label' => __('Buttons', 'nrd-form-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        // Alignment (targets the wrapper that holds the button)
        $this->add_control(
            'submit_align',
            [
                'label' => __('Submit Alignment', 'nrd-form-builder'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => ['title' => __('Left', 'nrd-form-builder'), 'icon' => 'eicon-text-align-left'],
                    'center' => ['title' => __('Center', 'nrd-form-builder'), 'icon' => 'eicon-text-align-center'],
                    'right' => ['title' => __('Right', 'nrd-form-builder'), 'icon' => 'eicon-text-align-right'],
                ],
                'default' => 'left',
                'toggle' => true,
                'selectors_dictionary' => [
                    'left' => 'text-align:left;',
                    'center' => 'text-align:center;',
                    'right' => 'text-align:right;',
                ],
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form .formbuilder-button' => '{{VALUE}}',
                ],
            ]
        );

        // Full width toggle
        $this->add_control(
            'submit_full_width',
            [
                'label' => __('Full Width', 'nrd-form-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'nrd-form-builder'),
                'label_off' => __('No', 'nrd-form-builder'),
                'return_value' => 'yes',
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form button[type="submit"],
                    {{WRAPPER}} .nrd-fb-instance .rendered-form input[type="submit"],
                    {{WRAPPER}} .nrd-fb-instance .rendered-form .btn' => 'display:block;width:100%;',
                ],
            ]
        );

        // Typography
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'submit_typography',
                'label' => __('Typography', 'nrd-form-builder'),
                'selector' => '{{WRAPPER}} .nrd-fb-instance .rendered-form button[type="submit"], {{WRAPPER}} .nrd-fb-instance .rendered-form input[type="submit"], {{WRAPPER}} .nrd-fb-instance .rendered-form .btn',
            ]
        );

        // Colors
        $this->add_control(
            'submit_text_color',
            [
                'label' => __('Text Color', 'nrd-form-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form button[type="submit"],
                    {{WRAPPER}} .nrd-fb-instance .rendered-form input[type="submit"],
                    {{WRAPPER}} .nrd-fb-instance .rendered-form .btn' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'submit_bg',
            [
                'label' => __('Background', 'nrd-form-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form button[type="submit"],
                    {{WRAPPER}} .nrd-fb-instance .rendered-form input[type="submit"],
                    {{WRAPPER}} .nrd-fb-instance .rendered-form .btn' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        // Padding / min width
        $this->add_responsive_control(
            'submit_padding',
            [
                'label' => __('Padding', 'nrd-form-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form button[type="submit"],
                    {{WRAPPER}} .nrd-fb-instance .rendered-form input[type="submit"],
                    {{WRAPPER}} .nrd-fb-instance .rendered-form .btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'submit_min_width',
            [
                'label' => __('Min Width', 'nrd-form-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 0, 'max' => 600]],
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form button[type="submit"],
                    {{WRAPPER}} .nrd-fb-instance .rendered-form input[type="submit"],
                    {{WRAPPER}} .nrd-fb-instance .rendered-form .btn' => 'min-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Border, radius, shadow
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'submit_border',
                'label' => __('Border', 'nrd-form-builder'),
                'selector' => '{{WRAPPER}} .nrd-fb-instance .rendered-form button[type="submit"], {{WRAPPER}} .nrd-fb-instance .rendered-form input[type="submit"], {{WRAPPER}} .nrd-fb-instance .rendered-form .btn',
            ]
        );

        $this->add_responsive_control(
            'submit_radius',
            [
                'label' => __('Border Radius', 'nrd-form-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form button[type="submit"],
                    {{WRAPPER}} .nrd-fb-instance .rendered-form input[type="submit"],
                    {{WRAPPER}} .nrd-fb-instance .rendered-form .btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'submit_shadow',
                'label' => __('Box Shadow', 'nrd-form-builder'),
                'selector' => '{{WRAPPER}} .nrd-fb-instance .rendered-form button[type="submit"], {{WRAPPER}} .nrd-fb-instance .rendered-form input[type="submit"], {{WRAPPER}} .nrd-fb-instance .rendered-form .btn',
            ]
        );

        // Hover state
        $this->add_control(
            'submit_text_color_hover',
            [
                'label' => __('Text Color (Hover)', 'nrd-form-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form button[type="submit"]:hover,
                    {{WRAPPER}} .nrd-fb-instance .rendered-form input[type="submit"]:hover,
                    {{WRAPPER}} .nrd-fb-instance .rendered-form .btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'submit_bg_hover',
            [
                'label' => __('Background (Hover)', 'nrd-form-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form button[type="submit"]:hover,
                    {{WRAPPER}} .nrd-fb-instance .rendered-form input[type="submit"]:hover,
                    {{WRAPPER}} .nrd-fb-instance .rendered-form .btn:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'submit_border_hover',
            [
                'label' => __('Border Color (Hover)', 'nrd-form-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .nrd-fb-instance .rendered-form button[type="submit"]:hover,
                    {{WRAPPER}} .nrd-fb-instance .rendered-form input[type="submit"]:hover,
                    {{WRAPPER}} .nrd-fb-instance .rendered-form .btn:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }


    private function get_all_forms()
    {
        $forms = get_posts([
            'post_type' => 'nrd-form-bd',
            'posts_per_page' => -1,
        ]);

        $options = [];
        foreach ($forms as $form) {
            $options[$form->ID] = $form->post_title;
        }
        return $options;
    }

    // ----------------------------
    // FRONTEND RENDER
    // ----------------------------

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $formId = isset($settings['form_id']) ? (int) $settings['form_id'] : 0;

        echo $this->render_form($formId);

        $json = wp_json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        ?>

        <script>
            (function () {
                var settings = <?php echo $json; ?>;
                console.log('[NRD] Settings:', settings);

                function getFormJsonFromScript($jsonTag) {
                    if (!$jsonTag || !$jsonTag.length) return "[]";
                    var txt = ($jsonTag.text() || "").trim();
                    if (!txt) return "[]";
                    if ((txt.startsWith('"') && txt.endsWith('"')) || (txt.startsWith("'") && txt.endsWith("'"))) {
                        try {
                            txt = JSON.parse(txt);
                        } catch (_) { }
                    }
                    if (typeof txt !== "string") {
                        try {
                            txt = JSON.stringify(txt);
                        } catch (_) {
                            txt = "[]";
                        }
                    }
                    return txt;
                }

                function renderAllForms() {
                    // Loop over each instance wrapper
                    jQuery(".nrd-fb-instance").each(function () {
                        console.log('[NRD] Rendering form...');
                        var $inst = jQuery(this);
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
                    var $legacyForms = jQuery('[id="fb-rendered-form"]');
                    if ($legacyForms.length && typeof window.formRenderData !== "undefined") {
                        $legacyForms.each(function () {
                            try {
                                jQuery(this).formRender({ formData: window.formRenderData });
                            } catch (e) {
                                console.error("NRD FB render error (legacy):", e);
                            }
                        });
                    }
                }

                renderAllForms();

            })();
        </script>

        <?php
    }

    private function render_form($id)
    {
        if (!$id)
            return '';

        $post = get_post($id);
        if (!$post || $post->post_type !== 'nrd-form-bd')
            return '';

        $content = $post->post_content; // RAW JSON
        $title = get_the_title($id);
        $nonce = wp_create_nonce('nrd_form_submit_' . $id);

        $form_id = 'nrd-fb-form-' . $id;
        $json_id = 'nrd-fb-json-' . $id;

        $json_raw = trim((string) $content);
        if ($json_raw === '') {
            $json_raw = '[]';
        }

        ob_start(); ?>
        <div class="nrd-fb-instance" data-form-id="<?php echo esc_attr($id); ?>" data-nonce="<?php echo esc_attr($nonce); ?>">

            <!-- The FORM is the render target -->
            <form id="<?php echo esc_attr($form_id); ?>" class="nrd-fb-form" enctype="multipart/form-data">
                <div class="nrd-fb-meta" style="display:none;">
                    <input type="hidden" name="form_id" value="<?php echo esc_attr($id); ?>">
                    <input type="hidden" name="form_title" value="<?php echo esc_attr($title); ?>">
                    <input type="hidden" name="nrd_fb_nonce" value="<?php echo esc_attr($nonce); ?>">
                </div>
                <div class="nrd-fb-render-target"></div>
            </form>

            <!-- JSON payload (RAW) -->
            <script type="application/json" id="<?php echo esc_attr($json_id); ?>">
                    <?php echo $json_raw; ?>
                    </script>
        </div>
        <?php
        return ob_get_clean();
    }
}