<?php
class MACP_Minification {
    private $html_minifier;

    public function __construct() {
        $this->html_minifier = new \voku\helper\HtmlMin();
        $this->configure_minifier();
    }

    private function configure_minifier() {
        $this->html_minifier
            ->doOptimizeViaHtmlDomParser(true)
            ->doRemoveComments(true)
            ->doSumUpWhitespace(true)
            ->doRemoveWhitespaceAroundTags(true)
            ->doOptimizeAttributes(true)
            ->doRemoveHttpPrefixFromAttributes(true)
            ->doRemoveDefaultAttributes(true)
            ->doRemoveEmptyAttributes(true)
            ->doRemoveValueFromEmptyInput(true)
            ->doSortCssClassNames(true)
            ->doSortHtmlAttributes(true)
            ->doRemoveSpacesBetweenTags(true);
    }

    public function process_output($buffer) {
        if (empty($buffer)) {
            return $buffer;
        }

        try {
            return $this->html_minifier->minify($buffer);
        } catch (Exception $e) {
            MACP_Debug::log("HTML minification error: " . $e->getMessage());
            return $buffer;
        }
    }
}