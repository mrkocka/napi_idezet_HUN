<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NIEL_Daily_Quote_Widget extends \Elementor\Widget_Base {
    public function get_name() { return 'niel_daily_quote'; }
    public function get_title() { return __( 'Daily Quotes MRK', 'napi-idezet-elementor' ); }
    public function get_icon() { return 'eicon-blockquote'; }
    public function get_categories() { return array( 'general' ); }
    public function get_style_depends() { return array( 'niel-widget' ); }
    public function get_keywords() { return array( __( 'quote', 'napi-idezet-elementor' ), 'quote', __( 'author', 'napi-idezet-elementor' ), 'daily' ); }
    protected function is_dynamic_content(): bool { return true; }

    protected function register_controls() {
        $this->start_controls_section( 'layout_section', array( 'label' => __( 'Layout', 'napi-idezet-elementor' ) ) );
        $this->add_responsive_control( 'direction', array(
            'label' => __( 'Quote and author', 'napi-idezet-elementor' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'column',
            'options' => array( 'column' => __( 'Stacked', 'napi-idezet-elementor' ), 'row' => __( 'Side by side', 'napi-idezet-elementor' ) ),
            'selectors' => array( '{{WRAPPER}} .niel-quote' => 'flex-direction: {{VALUE}};' ),
        ) );
        $this->add_responsive_control( 'gap', array(
            'label' => __( 'Spacing', 'napi-idezet-elementor' ),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => array( 'px', 'em', 'rem' ),
            'range' => array( 'px' => array( 'min' => 0, 'max' => 120 ) ),
            'default' => array( 'size' => 12, 'unit' => 'px' ),
            'selectors' => array( '{{WRAPPER}} .niel-quote' => 'gap: {{SIZE}}{{UNIT}};' ),
        ) );
        $this->add_responsive_control( 'alignment', array(
            'label' => __( 'Text alignment', 'napi-idezet-elementor' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'left',
            'options' => array( 'left' => __( 'Left', 'napi-idezet-elementor' ), 'center' => __( 'Center', 'napi-idezet-elementor' ), 'right' => __( 'Right', 'napi-idezet-elementor' ) ),
            'selectors' => array( '{{WRAPPER}} .niel-quote' => 'text-align: {{VALUE}};' ),
        ) );
        $this->end_controls_section();

        foreach ( array( 'text' => __( 'Quote', 'napi-idezet-elementor' ), 'author' => __( 'Author', 'napi-idezet-elementor' ) ) as $part => $label ) {
            $selector = '{{WRAPPER}} .niel-quote__' . $part;
            $this->start_controls_section( $part . '_style', array(
                'label' => $label, 'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ) );
            $this->add_control( $part . '_color', array(
                'label' => __( 'Color', 'napi-idezet-elementor' ), 'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array( $selector => 'color: {{VALUE}};' ),
            ) );
            $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), array(
                'name' => $part . '_typography', 'selector' => $selector,
            ) );
            $this->add_group_control( \Elementor\Group_Control_Text_Shadow::get_type(), array(
                'name' => $part . '_shadow', 'selector' => $selector,
            ) );
            $this->end_controls_section();
        }
    }

    protected function render() {
        $quote = niel_daily_quote();
        if ( ! $quote ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<p>' . esc_html__( 'No active quotes yet. Add a quote on the Quotes dashboard page.', 'napi-idezet-elementor' ) . '</p>';
            }
            return;
        }
        $author = (string) get_post_meta( $quote->ID, '_napi_idezet_attribution', true );
        echo '<div class="niel-quote">';
        echo '<div class="niel-quote__text">' . esc_html( $quote->post_content ) . '</div>';
        if ( '' !== $author ) {
            echo '<div class="niel-quote__author">' . esc_html( $author ) . '</div>';
        }
        echo '</div>';
    }
}
