<?php
declare(strict_types=1);

namespace BootstrapUI\View\Helper;

use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Utility\Hash;
use Cake\View\Helper\FormHelper as Helper;
use Cake\View\View;
use InvalidArgumentException;

class FormHelper extends Helper
{
    use OptionsAwareTrait;

    /**
     * The default feedback style.
     *
     * @var string
     */
    public const FEEDBACK_STYLE_DEFAULT = 'default';

    /**
     * The tooltip feedback style.
     *
     * @var string
     */
    public const FEEDBACK_STYLE_TOOLTIP = 'tooltip';

    /**
     * Absolute positioning.
     *
     * @var string
     */
    public const POSITION_ABSOLUTE = 'absolute';

    /**
     * Fixed positioning.
     *
     * @var string
     */
    public const POSITION_FIXED = 'fixed';

    /**
     * Relative positioning.
     *
     * @var string
     */
    public const POSITION_RELATIVE = 'relative';

    /**
     * Static positioning.
     *
     * @var string
     */
    public const POSITION_STATIC = 'static';

    /**
     * Sticky positioning.
     *
     * @var string
     */
    public const POSITION_STICKY = 'sticky';

    /**
     * Form alignment types.
     *
     * @var array
     */
    public const ALIGN_TYPES = ['default', 'horizontal', 'inline'];

    /**
     * Default alignment.
     *
     * @var string
     */
    public const ALIGN_DEFAULT = 'default';

    /**
     * Horizontal alignment.
     *
     * @var string
     */
    public const ALIGN_HORIZONTAL = 'horizontal';

    /**
     * Inlline alignment.
     *
     * @var string
     */
    public const ALIGN_INLINE = 'inline';

    /**
     * Set on `Form::create()` to tell if the type of alignment used (i.e. horizontal).
     *
     * @var string|null
     */
    protected $_align;

    /**
     * Set on `Form::create()` to tell grid type.
     *
     * @var array|null
     */
    protected $_grid;

    /**
     * Default Bootstrap string templates.
     *
     * @var array
     */
    protected $_templates = [
        'error' => '<div class="invalid-feedback">{{content}}</div>',
        'errorTooltip' => '<div class="invalid-tooltip">{{content}}</div>',
        'label' => '<label{{attrs}}>{{text}}{{tooltip}}</label>',
        'help' => '<small{{attrs}} class="d-block form-text text-muted">{{content}}</small>',
        'tooltip' => '<span data-bs-toggle="tooltip" title="{{content}}" class="fas fa-info-circle"></span>',
        'datetimeContainer' =>
            '<div{{containerAttrs}} ' .
                'class="{{containerClass}}form-group {{type}}{{required}}">{{content}}{{help}}</div>',
        'datetimeContainerError' =>
            '<div{{containerAttrs}} ' .
                'class="{{containerClass}}form-group {{formGroupPosition}}{{type}}{{required}} is-invalid">' .
                    '{{content}}{{error}}{{help}}</div>',
        'datetimeLabel' => '<label{{attrs}}>{{text}}{{tooltip}}</label>',
        'inputContainer' =>
            '<div{{containerAttrs}} ' .
                'class="{{containerClass}}form-group {{type}}{{required}}">{{content}}{{help}}</div>',
        'inputContainerError' =>
            '<div{{containerAttrs}} ' .
                'class="{{containerClass}}form-group {{formGroupPosition}}{{type}}{{required}} is-invalid">' .
                    '{{content}}{{error}}{{help}}</div>',
        'checkboxContainer' =>
            '<div{{containerAttrs}} ' .
                'class="{{containerClass}}form-group form-check{{variant}} ' .
                    '{{type}}{{required}}">{{content}}{{help}}</div>',
        'checkboxContainerError' =>
            '<div{{containerAttrs}} ' .
                'class="{{containerClass}}form-group form-check{{variant}} ' .
                    '{{formGroupPosition}}{{type}}{{required}} is-invalid">{{content}}{{error}}{{help}}</div>',
        'checkboxInlineContainer' =>
            '<div{{containerAttrs}} ' .
                'class="{{containerClass}}form-check{{variant}} form-check-inline {{type}}{{required}}">' .
                    '{{content}}</div>',
        'checkboxInlineContainerError' =>
            '<div{{containerAttrs}} ' .
                'class="{{containerClass}}form-check{{variant}} form-check-inline {{type}}{{required}} is-invalid">' .
                    '{{content}}</div>',
        'checkboxFormGroup' => '{{input}}{{label}}',
        'checkboxWrapper' => '<div class="form-check{{variant}}">{{label}}</div>',
        'checkboxInlineWrapper' => '<div class="form-check{{variant}} form-check-inline">{{label}}</div>',
        'radioContainer' =>
            '<div{{containerAttrs}} class="{{containerClass}}form-group {{type}}{{required}}" role="group" ' .
                'aria-labelledby="{{groupId}}">{{content}}{{help}}</div>',
        'radioContainerError' =>
            '<div{{containerAttrs}} ' .
                'class="{{containerClass}}form-group {{formGroupPosition}}{{type}}{{required}} is-invalid" ' .
                    'role="group" aria-labelledby="{{groupId}}">{{content}}{{error}}{{help}}</div>',
        'radioLabel' => '<label{{attrs}}>{{text}}{{tooltip}}</label>',
        'radioWrapper' => '<div class="form-check">{{hidden}}{{label}}</div>',
        'radioInlineWrapper' => '<div class="form-check form-check-inline">{{label}}</div>',
        'staticControl' => '<p class="form-control-plaintext">{{content}}</p>',
        'inputGroupContainer' => '<div{{attrs}}>{{prepend}}{{content}}{{append}}</div>',
        'inputGroupText' => '<span class="input-group-text">{{content}}</span>',
        'multicheckboxContainer' =>
            '<div{{containerAttrs}} class="{{containerClass}}form-group {{type}}{{required}}" role="group" ' .
                'aria-labelledby="{{groupId}}">{{content}}{{help}}</div>',
        'multicheckboxContainerError' =>
            '<div{{containerAttrs}} ' .
                'class="{{containerClass}}form-group {{formGroupPosition}}{{type}}{{required}} is-invalid" ' .
                    'role="group" aria-labelledby="{{groupId}}">{{content}}{{error}}{{help}}</div>',
        'multicheckboxLabel' => '<label{{attrs}}>{{text}}{{tooltip}}</label>',
        'multicheckboxWrapper' => '<fieldset class="mb-3 form-group">{{content}}</fieldset>',
        'multicheckboxTitle' => '<legend class="col-form-label pt-0">{{text}}</legend>',
        'nestingLabel' => '{{hidden}}{{input}}<label{{attrs}}>{{text}}{{tooltip}}</label>',
        'nestingLabelNestedInput' => '{{hidden}}<label{{attrs}}>{{input}}{{text}}{{tooltip}}</label>',
        'submitContainer' => '<div{{containerAttrs}} class="{{containerClass}}submit">{{content}}</div>',
    ];

    /**
     * Templates set per alignment type
     *
     * @var array
     */
    protected $_templateSet = [
        'default' => [
        ],
        'inline' => [
            'elementWrapper' =>
                '<div class="col-auto">{{content}}</div>',
            'help' => '<small{{attrs}} class="visually-hidden form-text text-muted">{{content}}</small>',
            'checkboxInlineContainer' =>
                '<div{{containerAttrs}} class="{{containerClass}}form-check{{variant}} {{type}}{{required}}">' .
                    '{{content}}{{help}}</div>',
            'checkboxInlineContainerError' =>
                '<div{{containerAttrs}} ' .
                    'class="{{containerClass}}form-check{{variant}} ' .
                        '{{formGroupPosition}}{{type}}{{required}} is-invalid">{{content}}{{error}}{{help}}</div>',
            'datetimeContainer' =>
                '<div{{containerAttrs}} ' .
                    'class="{{containerClass}}form-group {{formGroupPosition}}{{type}}{{required}}">' .
                        '{{content}}{{help}}</div>',
            'datetimeContainerError' =>
                '<div{{containerAttrs}} ' .
                    'class="{{containerClass}}form-group {{formGroupPosition}}{{type}}{{required}} is-invalid">' .
                        '{{content}}{{error}}{{help}}</div>',
            'datetimeLabel' => '<label{{attrs}}>{{text}}{{tooltip}}</label>',
            'radioContainer' =>
                '<div{{containerAttrs}} ' .
                    'class="{{containerClass}}form-group {{formGroupPosition}}{{type}}{{required}}" role="group" ' .
                        'aria-labelledby="{{groupId}}">{{content}}{{help}}</div>',
            'radioContainerError' =>
                '<div{{containerAttrs}} ' .
                    'class="{{containerClass}}form-group {{formGroupPosition}}{{type}}{{required}} is-invalid" ' .
                        'role="group" aria-labelledby="{{groupId}}">{{content}}{{error}}{{help}}</div>',
            'radioLabel' => '<span{{attrs}}>{{text}}{{tooltip}}</span>',
            'multicheckboxContainer' =>
                '<div{{containerAttrs}} ' .
                    'class="{{containerClass}}form-group d-flex {{formGroupPosition}}{{type}}{{required}}" ' .
                        'role="group" aria-labelledby="{{groupId}}">{{content}}{{help}}</div>',
            'multicheckboxContainerError' =>
                '<div{{containerAttrs}} ' .
                    'class="{{containerClass}}form-group d-flex ' .
                        '{{formGroupPosition}}{{type}}{{required}} is-invalid" ' .
                            'role="group" aria-labelledby="{{groupId}}">{{content}}{{error}}{{help}}</div>',
            'multicheckboxLabel' => '<span{{attrs}}>{{text}}{{tooltip}}</span>',
            'multicheckboxWrapper' =>
                '<fieldset class="form-group">{{content}}</fieldset>',
            'multicheckboxTitle' =>
                '<legend class="col-form-label float-none pt-0">{{text}}</legend>',
        ],
        'horizontal' => [
            'label' => '<label{{attrs}}>{{text}}{{tooltip}}</label>',
            'formGroup' => '{{label}}<div class="%s">{{input}}{{error}}{{help}}</div>',
            'checkboxFormGroup' =>
                '<div class="%s"><div class="form-check{{variant}}">{{input}}{{label}}{{error}}{{help}}</div></div>',
            'datetimeContainer' =>
                '<div{{containerAttrs}} ' .
                    'class="{{containerClass}}form-group row {{type}}{{required}}">{{content}}</div>',
            'datetimeContainerError' =>
                '<div{{containerAttrs}} ' .
                    'class="{{containerClass}}form-group row {{formGroupPosition}}{{type}}{{required}} is-invalid">' .
                        '{{content}}</div>',
            'datetimeLabel' => '<label{{attrs}}>{{text}}{{tooltip}}</label>',
            'checkboxInlineFormGroup' =>
                '<div class="%s"><div class="form-check{{variant}} form-check-inline">{{input}}{{label}}</div></div>',
            'submitContainer' =>
                '<div{{containerAttrs}} class="{{containerClass}}form-group row">' .
                    '<div class="%s">{{content}}</div></div>',
            'inputContainer' =>
                '<div{{containerAttrs}} class="{{containerClass}}form-group row {{type}}{{required}}">' .
                    '{{content}}</div>',
            'inputContainerError' =>
                '<div{{containerAttrs}} ' .
                    'class="{{containerClass}}form-group row {{formGroupPosition}}{{type}}{{required}} is-invalid">' .
                        '{{content}}</div>',
            'checkboxContainer' =>
                '<div{{containerAttrs}} class="{{containerClass}}form-group row {{type}}{{required}}">' .
                    '{{content}}</div>',
            'checkboxContainerError' =>
                '<div{{containerAttrs}} ' .
                    'class="{{containerClass}}form-group row {{formGroupPosition}}{{type}}{{required}} is-invalid">' .
                        '{{content}}</div>',
            'radioContainer' =>
                '<div{{containerAttrs}} class="{{containerClass}}form-group row {{type}}{{required}}" role="group" ' .
                    'aria-labelledby="{{groupId}}">{{content}}</div>',
            'radioContainerError' =>
                '<div{{containerAttrs}} ' .
                    'class="{{containerClass}}form-group row {{formGroupPosition}}{{type}}{{required}} is-invalid" ' .
                        'role="group" aria-labelledby="{{groupId}}">{{content}}</div>',
            'radioLabel' =>
                '<label{{attrs}}>{{text}}{{tooltip}}</label>',
            'multicheckboxContainer' =>
                '<div{{containerAttrs}} class="{{containerClass}}form-group row {{type}}{{required}}" role="group" ' .
                    'aria-labelledby="{{groupId}}">{{content}}</div>',
            'multicheckboxContainerError' =>
                '<div{{containerAttrs}} ' .
                    'class="{{containerClass}}form-group row {{formGroupPosition}}{{type}}{{required}} is-invalid" ' .
                        'role="group" aria-labelledby="{{groupId}}">{{content}}</div>',
            'multicheckboxLabel' =>
                '<label{{attrs}}>{{text}}{{tooltip}}</label>',
        ],
    ];

    /**
     * Default Bootstrap widgets.
     *
     * @var array
     */
    protected $_widgets = [
        'button' => 'BootstrapUI\View\Widget\ButtonWidget',
        'datetime' => 'BootstrapUI\View\Widget\DateTimeWidget',
        'file' => ['BootstrapUI\View\Widget\FileWidget', 'label'],
        'select' => 'BootstrapUI\View\Widget\SelectBoxWidget',
        'textarea' => 'BootstrapUI\View\Widget\TextareaWidget',
        '_default' => 'BootstrapUI\View\Widget\BasicWidget',
    ];

    /**
     * Construct the widgets and binds the default context providers.
     *
     * @param \Cake\View\View $View The View this helper is being attached to.
     * @param array $config Configuration settings for the helper.
     */
    public function __construct(View $View, array $config = [])
    {
        $this->_defaultConfig = [
            'align' => 'default',
            'errorClass' => 'is-invalid',
            'grid' => [
                'left' => 2,
                'middle' => 10,
                'right' => 0,
            ],
            'templates' => $this->_templates + $this->_defaultConfig['templates'],
        ] + $this->_defaultConfig;

        if (isset($this->_defaultConfig['templateSet'])) {
            $templateSet = Hash::merge($this->_templateSet, $this->_defaultConfig['templateSet']);
        } else {
            $templateSet = $this->_templateSet;
        }
        $this->_defaultConfig['templateSet'] = $templateSet;

        $this->_defaultWidgets = $this->_widgets + $this->_defaultWidgets;

        parent::__construct($View, $config);
    }

    /**
     * Returns an HTML FORM element.
     *
     * @param mixed $context The context for which the form is being defined.
     *   Can be a ContextInterface instance, ORM entity, ORM resultset, or an
     *   array of meta data. You can use `null` to make a context-less form.
     * @param array $options An array of html attributes and options.
     * @return string An formatted opening FORM tag.
     */
    public function create($context = null, array $options = []): string
    {
        $options += [
            'class' => null,
            'role' => 'form',
            'align' => null,
            'templates' => [],
        ];

        return parent::create($context, $this->_formAlignment($options));
    }

    /**
     * @inheritDoc
     */
    public function button(string $title, array $options = []): string
    {
        $result = parent::button($title, $options);

        return $this->_postProcessElement($result, null, $options);
    }

    /**
     * Creates a submit button element.
     *
     * Overrides parent method to add CSS class `btn`, to the element.
     *
     * @param string $caption The label appearing on the button OR if string contains :// or the
     *  extension .jpg, .jpe, .jpeg, .gif, .png use an image if the extension
     *  exists, AND the first character is /, image is relative to webroot,
     *  OR if the first character is not /, image is relative to webroot/img.
     * @param array $options Array of options. See above.
     * @return string A HTML submit button
     * @link http://book.cakephp.org/3.0/en/views/helpers/form.html#creating-buttons-and-submit-elements
     */
    public function submit(?string $caption = null, array $options = []): string
    {
        $options += [
            'class' => 'primary',
        ];
        $options = $this->applyButtonClasses($options);
        $options = $this->_containerOptions(null, $options);

        $result = parent::submit($caption, $options);

        return $this->_postProcessElement($result, null, $options);
    }

    /**
     * Generates a form input element complete with label and wrapper div.
     *
     * Adds extra option besides the ones supported by parent class method:
     * - `container` - An array of container attributes, with `class` being a special case, prepending the value to
     *   the existing list of classes instead of replacing them.
     * - `append` - Append addon to input.
     * - `prepend` - Prepend addon to input.
     * - `inline` - Boolean for generating inline checkbox/radio.
     * - `switch` - Boolean for generating switch style checkboxes.
     * - `help` - Help text to include in the input container.
     * - `tooltip` - Tooltip text to include in the control's label.
     * - `feedbackStyle` - The feedback style to use, `default`, or `tooltip` (will cause `formGroupPosition` to be set
     *   to `relative` unless explicitly configured otherwise).
     * - `formGroupPosition` - CSS positioning of form groups, `absolute`, `fixed`, `relative`, `static`, or `sticky`.
     *
     * @param string $fieldName This should be "Modelname.fieldname".
     * @param array $options Each type of input takes different options.
     * @return string Completed form widget.
     */
    public function control(string $fieldName, array $options = []): string
    {
        $options += [
            'feedbackStyle' => null,
            'formGroupPosition' => null,
            'prepend' => null,
            'append' => null,
            'inline' => null,
            'nestedInput' => false,
            'switch' => null,
            'type' => null,
            'label' => null,
            'error' => null,
            'required' => null,
            'options' => null,
            'help' => null,
            'tooltip' => null,
            'templates' => [],
            'templateVars' => [],
            'labelOptions' => true,
            'container' => null,
        ];
        $options = $this->_parseOptions($fieldName, $options);

        $newTemplates = $options['templates'];
        if ($newTemplates) {
            $this->templater()->push();
            $templateMethod = is_string($options['templates']) ? 'load' : 'add';
            $this->templater()->{$templateMethod}($options['templates']);
            $options['templates'] = [];
        }

        switch ($options['type']) {
            case 'datetime-local':
            case 'datetime':
            case 'date':
            case 'time':
                $options = $this->_dateTimeOptions($fieldName, $options);
                break;

            case 'checkbox':
            case 'radio':
            case 'select':
            case 'range':
                $function = '_' . $options['type'] . 'Options';
                $options = $this->{$function}($fieldName, $options);
                break;

            default:
                $options = $this->_labelOptions($fieldName, $options);
                break;
        }

        $options = $this->_containerOptions($fieldName, $options);
        $options = $this->_feedbackStyleOptions($fieldName, $options);
        $options = $this->_helpOptions($fieldName, $options);
        $options = $this->_tooltipOptions($fieldName, $options);

        if (
            isset($options['append']) ||
            isset($options['prepend'])
        ) {
            $options['injectErrorClass'] = $this->_config['errorClass'];
        }

        unset(
            $options['formGroupPosition'],
            $options['feedbackStyle'],
            $options['inline'],
            $options['nestedInput'],
            $options['switch']
        );

        $result = parent::control($fieldName, $options);

        $result = $this->_postProcessElement($result, $fieldName, $options);

        if ($newTemplates) {
            $this->templater()->pop();
        }

        return $result;
    }

    /**
     * Modify the options for container templates.
     *
     * @param string|null $fieldName Field name.
     * @param array $options Options. See `$options` argument of `control()` method.
     * @return array
     */
    protected function _containerOptions(?string $fieldName, array $options): array
    {
        if (
            $this->_align !== static::ALIGN_INLINE &&
            isset($options['type'])
        ) {
            $options['container'] = $this->injectClasses('mb-3', (array)($options['container'] ?? []));
        }

        if (!isset($options['container'])) {
            return $options;
        }

        $containerOptions = $options['container'];
        unset($options['container']);

        if (isset($containerOptions['class'])) {
            $options['templateVars']['containerClass'] = $containerOptions['class'] . ' ';
            unset($containerOptions['class']);
        }
        if (!empty($containerOptions)) {
            $options['templateVars']['containerAttrs'] = $this->templater()->formatAttributes($containerOptions);
        }

        return $options;
    }

    /**
     * Modify options for date time controls.
     *
     * @param string $fieldName Field name.
     * @param array $options Options. See `$options` argument of `control()` method.
     * @return array
     */
    protected function _dateTimeOptions(string $fieldName, array $options): array
    {
        $options = $this->_labelOptions($fieldName, $options);

        // group IDs are no longer required for date/time controls,
        // this is just kept for backwards compatibility

        $groupId =
        $options['templateVars']['groupId'] =
            $this->_domId($fieldName . '-group-label');

        if ($options['label'] !== false) {
            $options['label']['templateVars']['groupId'] = $groupId;
        }

        $options['templates']['label'] = $this->templater()->get('datetimeLabel');
        $options['templates']['inputContainer'] = $this->templater()->get('datetimeContainer');
        $options['templates']['inputContainerError'] = $this->templater()->get('datetimeContainerError');

        return $options;
    }

    /**
     * Modify options for checkbox controls.
     *
     * @param string $fieldName Field name.
     * @param array $options Options. See `$options` argument of `control()` method.
     * @return array
     */
    protected function _checkboxOptions(string $fieldName, array $options): array
    {
        if ($options['label'] !== false) {
            $options['label'] = $this->injectClasses('form-check-label', (array)$options['label']);
        }
        $options = $this->injectClasses('form-check-input', $options);

        if ($this->_align === static::ALIGN_HORIZONTAL) {
            $options['inline'] = false;
        }

        if (
            $options['inline'] ||
            $this->_align === static::ALIGN_INLINE
        ) {
            $checkboxContainer = $this->templater()->get('checkboxInlineContainer');
            $checkboxContainerError = $this->templater()->get('checkboxInlineContainerError');

            $options['templates']['checkboxContainer'] = $checkboxContainer;
            $options['templates']['checkboxContainerError'] = $checkboxContainerError;
        }

        if ($options['nestedInput']) {
            $options['templates']['nestingLabel'] = $this->templater()->get('nestingLabelNestedInput');
        }

        if ($options['switch']) {
            $options['templateVars']['variant'] = ' form-switch';
        }

        return $options;
    }

    /**
     * Modify options for radio controls.
     *
     * @param string $fieldName Field name.
     * @param array $options Options. See `$options` argument of `control()` method.
     * @return array
     */
    protected function _radioOptions(string $fieldName, array $options): array
    {
        $options = $this->_labelOptions($fieldName, $options);

        $options = $this->injectClasses('form-check-input', $options);

        $groupId =
        $options['templateVars']['groupId'] =
            $this->_domId($fieldName . '-group-label');

        if ($options['label'] !== false) {
            $options['label']['templateVars']['groupId'] = $groupId;
            $options['label']['id'] = $groupId;
        }

        if ($options['label'] !== false) {
            $labelClasses = [];
            if ($this->_align !== static::ALIGN_INLINE) {
                $labelClasses[] = 'd-block';
            }
            if ($this->_align === static::ALIGN_HORIZONTAL) {
                $labelClasses[] = 'pt-0';
            }
            if ($labelClasses) {
                $options['label'] = $this->injectClasses($labelClasses, (array)$options['label']);
            }
        }

        $options['templates']['label'] = $this->templater()->get('radioLabel');

        if (
            $options['inline'] ||
            $this->_align === static::ALIGN_INLINE
        ) {
            $options['templates']['radioWrapper'] = $this->templater()->get('radioInlineWrapper');
        }

        if ($options['nestedInput']) {
            $options['templates']['nestingLabel'] = $this->templater()->get('nestingLabelNestedInput');
        }

        return $options;
    }

    /**
     * Modify options for select controls.
     *
     * @param string $fieldName Field name.
     * @param array $options Options. See `$options` argument of `control()` method.
     * @return array
     */
    protected function _selectOptions(string $fieldName, array $options): array
    {
        $options = $this->_labelOptions($fieldName, $options);

        $labelClasses = [];

        if (isset($options['multiple']) && $options['multiple'] === 'checkbox') {
            $options['type'] = 'multicheckbox';

            $groupId =
            $options['templateVars']['groupId'] =
                $this->_domId($fieldName . '-group-label');

            if ($options['label'] !== false) {
                $options['label']['templateVars']['groupId'] = $groupId;
                $options['label']['id'] = $groupId;
            }

            if ($options['label'] !== false) {
                if ($this->_align !== static::ALIGN_INLINE) {
                    $labelClasses[] = 'd-block';
                }
                if ($this->_align === static::ALIGN_HORIZONTAL) {
                    $labelClasses[] = 'pt-0';
                }
            }

            $options['templates']['label'] = $this->templater()->get('multicheckboxLabel');

            $options = $this->injectClasses('form-check-input', $options);

            if (
                $options['inline'] ||
                $this->_align === static::ALIGN_INLINE
            ) {
                $wrapper = $this->templater()->get('checkboxInlineWrapper');
                $options['templates']['checkboxWrapper'] = $wrapper;
            }

            if ($options['nestedInput']) {
                $options['templates']['nestingLabel'] = $this->templater()->get('nestingLabelNestedInput');
            }

            if ($options['switch']) {
                $options['templateVars']['variant'] = ' form-switch';
            }
        }

        if (
            $this->_align === static::ALIGN_INLINE &&
            $options['label'] !== false
        ) {
            $labelClasses[] = 'visually-hidden';
        }

        if ($labelClasses) {
            $options['label'] = $this->injectClasses($labelClasses, (array)$options['label']);
        }

        if ($options['type'] !== 'multicheckbox') {
            $options['injectFormControl'] = false;
            $options = $this->injectClasses('form-select', $options);
        }

        return $options;
    }

    /**
     * Modify options for range controls.
     *
     * @param string $fieldName Field name.
     * @param array $options Options. See `$options` argument of `control()` method.
     * @return array
     */
    protected function _rangeOptions(string $fieldName, array $options): array
    {
        $options = $this->_labelOptions($fieldName, $options);
        $options['injectFormControl'] = false;

        if (
            $options['label'] !== false &&
            $this->_align === static::ALIGN_HORIZONTAL
        ) {
            $options['label'] = $this->injectClasses('pt-0', (array)$options['label']);
        }

        return $this->injectClasses('form-range', $options);
    }

    /**
     * Modify the options for labels.
     *
     * @param string|null $fieldName Field name.
     * @param array $options Options. See `$options` argument of `control()` method.
     * @return array
     */
    protected function _labelOptions(?string $fieldName, array $options): array
    {
        if ($options['label'] !== false) {
            $labelClasses = [];
            if ($this->_align !== static::ALIGN_HORIZONTAL) {
                $labelClasses[] = 'form-label';
            }
            if ($this->_align === static::ALIGN_HORIZONTAL) {
                $size = $this->_gridClass('left');
                $labelClasses[] = "col-form-label $size";
            }
            if ($this->_align === static::ALIGN_INLINE) {
                $labelClasses[] = 'visually-hidden';
            }
            if ($labelClasses) {
                $options['label'] = $this->injectClasses($labelClasses, (array)$options['label']);
            }
        }

        return $options;
    }

    /**
     * Modify templates based on error style.
     *
     * @param string $fieldName Field name.
     * @param array $options Options. See `$options` argument of `control()` method.
     * @return array
     */
    protected function _feedbackStyleOptions(string $fieldName, array $options): array
    {
        $formGroupPosition = $options['formGroupPosition'] ?: $this->getConfig('formGroupPosition');
        $feedbackStyle = $options['feedbackStyle'] ?: $this->getConfig('feedbackStyle');

        if (
            $this->_align === static::ALIGN_INLINE &&
            $feedbackStyle === null
        ) {
            $feedbackStyle = static::FEEDBACK_STYLE_TOOLTIP;
        }

        if ($feedbackStyle === static::FEEDBACK_STYLE_TOOLTIP) {
            $options['templates']['error'] = $this->templater()->get('errorTooltip');
        }

        if (
            $formGroupPosition === null &&
            $feedbackStyle === static::FEEDBACK_STYLE_TOOLTIP
        ) {
            $formGroupPosition = static::POSITION_RELATIVE;
        }

        if ($formGroupPosition !== null) {
            $options['templateVars']['formGroupPosition'] = 'position-' . $formGroupPosition . ' ';
        }

        return $options;
    }

    /**
     * Modify options for control's help.
     *
     * @param string $fieldName Field name.
     * @param array $options Options. See `$options` argument of `control()` method.
     * @return array
     */
    protected function _helpOptions(string $fieldName, array $options): array
    {
        if ($options['help']) {
            if (is_string($options['help'])) {
                $options['help'] = $this->templater()->format(
                    'help',
                    ['content' => $options['help']]
                );
            } elseif (is_array($options['help'])) {
                $options['help'] = $this->templater()->format(
                    'help',
                    [
                        'content' => $options['help']['content'],
                        'attrs' => $this->templater()->formatAttributes($options['help'], ['class', 'content']),
                    ]
                );
            }
        }

        return $options;
    }

    /**
     * Modify options for control's tooltip.
     *
     * @param string $fieldName Field name.
     * @param array $options Options. See `$options` argument of `control()` method.
     * @return array
     */
    protected function _tooltipOptions(string $fieldName, array $options): array
    {
        if (
            $options['tooltip'] &&
            $options['label'] !== false
        ) {
            $tooltip = $this->templater()->format(
                'tooltip',
                ['content' => $options['tooltip']]
            );
            $options['label']['templateVars']['tooltip'] = ' ' . $tooltip;
        }
        unset($options['tooltip']);

        return $options;
    }

    /**
     * Post processes a generated form element.
     *
     * @param string $html The form element HTML.
     * @param string|null $fieldName The field name.
     * @param array $options The element generation options (see `$options` argument for `button()`, `submit()`, and
     *  `control()`).
     * @return string
     * @see button()
     * @see submit()
     * @see control()
     */
    protected function _postProcessElement(string $html, ?string $fieldName, array $options): string
    {
        if ($this->_align === static::ALIGN_INLINE) {
            $html = $this->templater()->format('elementWrapper', [
                'content' => $html,
            ]);
        }

        return $html;
    }

    /**
     * Creates a set of radio widgets.
     *
     * ### Attributes:
     *
     * - `value` - Indicates the value when this radio button is checked.
     * - `label` - Either `false` to disable label around the widget or an array of attributes for
     *    the label tag. `selected` will be added to any classes e.g. `'class' => 'myclass'` where widget
     *    is checked
     * - `hiddenField` - boolean to indicate if you want the results of radio() to include
     *    a hidden input with a value of ''. This is useful for creating radio sets that are non-continuous.
     * - `disabled` - Set to `true` or `disabled` to disable all the radio buttons. Use an array of
     *   values to disable specific radio buttons.
     * - `empty` - Set to `true` to create an input with the value '' as the first option. When `true`
     *   the radio label will be 'empty'. Set this option to a string to control the label value.
     *
     * @param string $fieldName Name of a field, like this "modelname.fieldname"
     * @param iterable $options Radio button options array.
     * @param array $attributes Array of attributes.
     * @return string Completed radio widget set.
     * @link https://book.cakephp.org/3.0/en/views/helpers/form.html#creating-radio-buttons
     */
    public function radio(string $fieldName, iterable $options = [], array $attributes = []): string
    {
        $attributes = $this->multiInputAttributes($attributes);

        return parent::radio($fieldName, $options, $attributes);
    }

    /**
     * Creates a set of checkboxes out of options.
     *
     * ### Options
     *
     * - `escape` - If true contents of options will be HTML entity encoded. Defaults to true.
     * - `val` The selected value of the input.
     * - `class` - When using multiple = checkbox the class name to apply to the divs. Defaults to 'checkbox'.
     * - `disabled` - Control the disabled attribute. When creating checkboxes, `true` will disable all checkboxes.
     *   You can also set disabled to a list of values you want to disable when creating checkboxes.
     * - `hiddenField` - Set to false to remove the hidden field that ensures a value
     *   is always submitted.
     * - `label` - Either `false` to disable label around the widget or an array of attributes for
     *   the label tag. `selected` will be added to any classes e.g. `'class' => 'myclass'` where
     *   widget is checked
     *
     * Can be used in place of a select box with the multiple attribute.
     *
     * @param string $fieldName Name attribute of the SELECT
     * @param iterable $options Array of the OPTION elements
     *   (as 'value'=>'Text' pairs) to be used in the checkboxes element.
     * @param array $attributes The HTML attributes of the select element.
     * @return string Formatted SELECT element
     * @see \Cake\View\Helper\FormHelper::select() for supported option formats.
     */
    public function multiCheckbox(string $fieldName, iterable $options, array $attributes = []): string
    {
        $attributes = $this->multiInputAttributes($attributes);

        return parent::multiCheckbox($fieldName, $options, $attributes);
    }

    /**
     * Set options for radio and multi checkbox inputs.
     *
     * @param array $attributes Attributes
     * @return array
     */
    protected function multiInputAttributes(array $attributes): array
    {
        $classPrefix = 'form-check';

        $attributes += ['label' => true];
        $attributes = $this->injectClasses($classPrefix . '-input', $attributes);

        if ($attributes['label'] === true) {
            $attributes['label'] = [];
        }
        if ($attributes['label'] !== false) {
            $attributes['label'] = $this->injectClasses($classPrefix . '-label', $attributes['label']);
        }

        return $attributes;
    }

    /**
     * Closes an HTML form, cleans up values set by FormHelper::create(), and writes hidden
     * input fields where appropriate.
     *
     * Overrides parent method to reset the form alignment and grid size.
     *
     * @param array $secureAttributes Secure attributes which will be passed as HTML attributes
     *   into the hidden input elements generated for the Security Component.
     * @return string A closing FORM tag.
     */
    public function end(array $secureAttributes = []): string
    {
        $this->_align = $this->_grid = null;

        return parent::end($secureAttributes);
    }

    /**
     * Used to place plain text next to label within a form.
     *
     * ### Options:
     *
     * - `hiddenField` - boolean to indicate if you want value for field included
     *    in a hidden input. Defaults to true.
     *
     * @param string $fieldName Name of a field, like this "modelname.fieldname"
     * @param array $options Array of HTML attributes.
     * @return string An HTML text input element.
     */
    public function staticControl(string $fieldName, array $options = []): string
    {
        $options += [
            'escape' => true,
            'required' => false,
            'secure' => true,
            'hiddenField' => true,
        ];

        $secure = $options['secure'];
        $hiddenField = $options['hiddenField'];
        unset($options['secure'], $options['hiddenField']);

        $options = $this->_initInputField(
            $fieldName,
            ['secure' => static::SECURE_SKIP] + $options
        );

        $content = $options['escape'] ? h($options['val']) : $options['val'];
        $static = $this->formatTemplate('staticControl', [
            'content' => $content,
        ]);

        if (!$hiddenField) {
            return $static;
        }

        if ($secure === true && $this->formProtector) {
            /** @psalm-suppress InternalMethod */
            $this->formProtector->addField(
                $options['name'],
                true,
                (string)$options['val']
            );
        }

        $options['type'] = 'hidden';

        return $static . $this->widget('hidden', $options);
    }

    /**
     * Generates an input element.
     *
     * Overrides parent method to unset 'help' key.
     *
     * @param string $fieldName The field's name.
     * @param array $options The options for the input element.
     * @return string|array The generated input element.
     */
    protected function _getInput(string $fieldName, array $options)
    {
        unset($options['help']);

        return parent::_getInput($fieldName, $options);
    }

    /**
     * Generates an group template element
     *
     * @param array $options The options for group template
     * @return string The generated group template
     */
    protected function _groupTemplate(array $options): string
    {
        $groupTemplate = $options['options']['type'] . 'FormGroup';
        if (!$this->templater()->get($groupTemplate)) {
            $groupTemplate = 'formGroup';
        }

        return $this->templater()->format($groupTemplate, [
            'input' => $options['input'] ?? [],
            'label' => $options['label'],
            'error' => $options['error'],
            'templateVars' => $options['options']['templateVars'] ?? [],
            'help' => $options['options']['help'],
        ]);
    }

    /**
     * Generates an input container template
     *
     * @param array $options The options for input container template.
     * @return string The generated input container template.
     */
    protected function _inputContainerTemplate(array $options): string
    {
        $inputContainerTemplate = $options['options']['type'] . 'Container' . $options['errorSuffix'];
        if (!$this->templater()->get($inputContainerTemplate)) {
            $inputContainerTemplate = 'inputContainer' . $options['errorSuffix'];
        }

        return $this->templater()->format($inputContainerTemplate, [
            'content' => $options['content'],
            'error' => $options['error'],
            'required' => $options['options']['required'] ? ' required' : '',
            'type' => $options['options']['type'],
            'templateVars' => $options['options']['templateVars'] ?? [],
            'help' => $options['options']['help'],
        ]);
    }

    /**
     * Generates input options array
     *
     * @param string $fieldName The name of the field to parse options for.
     * @param array $options Options list.
     * @return array Options
     */
    protected function _parseOptions(string $fieldName, array $options): array
    {
        $options = parent::_parseOptions($fieldName, $options);
        $options += ['id' => $this->_domId($fieldName)];
        if (is_string($options['label'])) {
            $options['label'] = ['text' => $options['label']];
        }

        return $options;
    }

    /**
     * Form alignment detector/switcher.
     *
     * @param array $options Options.
     * @return array Modified options.
     */
    protected function _formAlignment(array $options): array
    {
        if (!$options['align']) {
            $options['align'] = $this->_detectFormAlignment($options);
        }

        if (is_array($options['align'])) {
            $this->_grid = $options['align'];
            $options['align'] = static::ALIGN_HORIZONTAL;
        } elseif ($options['align'] === static::ALIGN_HORIZONTAL) {
            $this->_grid = $this->getConfig('grid');
        }

        if (!in_array($options['align'], static::ALIGN_TYPES)) {
            throw new InvalidArgumentException(
                'Invalid valid for `align` option. Valid values are: ' . implode(', ', static::ALIGN_TYPES)
            );
        }

        $this->_align = $options['align'];

        unset($options['align']);

        $templates = $this->_config['templateSet'][$this->_align];
        if (is_string($options['templates'])) {
            $options['templates'] = (new PhpConfig())->read($options['templates']);
        }

        if ($this->_align === 'default') {
            $options['templates'] += $templates;

            return $options;
        }

        $options = $this->injectClasses('form-' . $this->_align, $options);

        if ($this->_align === 'inline') {
            $options = $this->injectClasses(['row g-3 align-items-center'], $options);
            $options['templates'] += $templates;

            return $options;
        }

        $offsetedGridClass = implode(' ', [$this->_gridClass('left', true), $this->_gridClass('middle')]);

        $templates['label'] = sprintf($templates['label'], $this->_gridClass('left'));
        $templates['datetimeLabel'] = sprintf($templates['datetimeLabel'], $this->_gridClass('left'));
        $templates['radioLabel'] = sprintf($templates['radioLabel'], $this->_gridClass('left'));
        $templates['multicheckboxLabel'] = sprintf($templates['multicheckboxLabel'], $this->_gridClass('left'));
        $templates['formGroup'] = sprintf($templates['formGroup'], $this->_gridClass('middle'));
        $containers = [
            'checkboxFormGroup',
            'checkboxInlineFormGroup',
            'submitContainer',
        ];
        foreach ($containers as $value) {
            $templates[$value] = sprintf($templates[$value], $offsetedGridClass);
        }

        $options['templates'] += $templates;

        return $options;
    }

    /**
     * Returns a Bootstrap grid class (i.e. `col-md-2`).
     *
     * @param string $position One of `left`, `middle` or `right`.
     * @param bool $offset If true, will append `offset-` to the class.
     * @return string Classes.
     */
    protected function _gridClass(string $position, bool $offset = false): string
    {
        if ($this->_grid === null) {
            return '';
        }

        $class = 'col-%s-';
        if ($offset) {
            $class = 'offset-%s-';
        }

        if (isset($this->_grid[$position])) {
            return sprintf($class, 'md') . $this->_grid[$position];
        }

        $classes = [];
        foreach ($this->_grid as $screen => $positions) {
            if (isset($positions[$position])) {
                array_push($classes, sprintf($class, $screen) . $positions[$position]);
            }
        }

        return implode(' ', $classes);
    }

    /**
     * Detects the form alignment when possible.
     *
     * @param array $options Options.
     * @return string Form alignment type. One of `default`, `horizontal` or `inline`.
     */
    protected function _detectFormAlignment(array $options): string
    {
        foreach ([static::ALIGN_HORIZONTAL, static::ALIGN_INLINE] as $align) {
            if ($this->checkClasses('form-' . $align, (array)$options['class'])) {
                return $align;
            }
        }

        return $this->getConfig('align');
    }
}
