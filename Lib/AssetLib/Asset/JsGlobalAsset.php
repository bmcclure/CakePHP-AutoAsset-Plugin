<?php
namespace AssetLib\Asset;

/**
 * Represents a global Javascript variable
 */
class JsGlobalAsset extends ValueAsset implements AssetInterface {
    /**
     * The template used with sprintf to output the global
     *
     * @var string
     */
    protected $template = 'var %s = %s;';

    /**
     * @return array
     */
    public function getParameters() {
        return [$this->getString()];
    }

    /**
     * @return string
     */
    public function getString() {
        $template = 'var %s = %s;';

        $value = $this->getValue();

        if (is_array($value)) {
            // Output the entire array as JSON
            $valueString = json_encode($value);
        } elseif (is_bool($value)) {
            // Output a raw true/false value
            $valueString = ($value) ? 'true' : 'false';
        } elseif (is_string($value)) {
            // Wrap it in quotes if needed
            $valueString = preg_match('/^(["\']).*\1$/m', $value) ? $value : "'$value'";
        } else {
            // I don't know what it is, just output it.
            $valueString = $value;
        }

        return sprintf($template, $this->getName(), $valueString);
    }

    /**
     * @return string
     */
    public function getTemplate() {
        return $this->template;
    }

    /**
     * @param $template
     */
    public function setTemplate($template) {
        $this->template = $template;
    }
}

?>