<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014 - 2015
 * @package yii2-widgets
 * @subpackage yii2-widget-depdrop
 * @version 1.0.1
 */

namespace kartik\depdrop;

use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;
use kartik\base\Config;
use kartik\select2\Select2;

/**
 * Dependent Dropdown widget is a wrapper widget for the dependent-dropdown
 * JQuery plugin by Krajee. The plugin enables setting up dependent dropdowns
 * with nested dependencies.
 *
 * @see http://plugins.krajee.com/dependent-dropdown
 * @see http://github.com/kartik-v/dependent-dropdown
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0.1
 */
class DepDrop extends \kartik\base\InputWidget
{
    const TYPE_DEFAULT = 1;
    const TYPE_SELECT2 = 2;

    /**
     * @var int the type of the dropdown element.
     * - 1 or [[DepDrop::TYPE_DEFAULT]] will render using \yii\helpers\Html::dropDownList
     * - 2 or [[DepDrop::TYPE_SELECT2]] will render using \kartik\widgets\Select2 widget
     */
    public $type = self::TYPE_DEFAULT;

    /**
     * @var array the configuration options for the Select2 widget. Applicable
     * only if the `type` property is set to [[DepDrop::TYPE_SELECT2]].
     */
    public $select2Options = [];

    /**
     * @inherit doc
     * @throw InvalidConfigException
     */
    public function init()
    {
        if (empty($this->pluginOptions['url'])) {
            throw new InvalidConfigException("The 'pluginOptions[\"url\"]' property has not been set.");
        }
        if (empty($this->pluginOptions['depends']) || !is_array($this->pluginOptions['depends'])) {
            throw new InvalidConfigException("The 'pluginOptions[\"depends\"]' property must be set and must be an array of dependent dropdown element ID.");
        }
        if (empty($this->options['class'])) {
            $this->options['class'] = 'form-control';
        }
        if ($this->type === self::TYPE_SELECT2) {
            Config::checkDependency('select2\Select2', 'yii2-widget-select2', 'for dependent dropdown for TYPE_SELECT2');
        }
        parent::init();
        if ($this->type !== self::TYPE_SELECT2 && !empty($this->options['placeholder'])) {
            $this->data = ['' => $this->options['placeholder']] + $this->data;
        }
        $this->registerAssets();
    }

    /**
     * Registers the needed assets
     */
    public function registerAssets()
    {
        $view = $this->getView();
        DepDropAsset::register($view);
        DepDropExtAsset::register($view);
        $this->registerPlugin('depdrop');
        if ($this->type === self::TYPE_SELECT2) {
            $loading = ArrayHelper::getValue($this->pluginOptions, 'loadingText', 'Loading ...');
            $this->select2Options['data'] = $this->data;
            $this->select2Options['options'] = $this->options;
            if ($this->hasModel()) {
                $settings = ArrayHelper::merge($this->select2Options, [
                    'model' => $this->model,
                    'attribute' => $this->attribute
                ]);
            } else {
                $settings = ArrayHelper::merge($this->select2Options, [
                    'name' => $this->name,
                    'value' => $this->value
                ]);
            }
            echo Select2::widget($settings);
            $id = $this->options['id'];
            $view->registerJs("initDepdropS2('{$id}','{$loading}');");
        } else {
            echo $this->getInput('dropdownList', true);
        }
    }

}