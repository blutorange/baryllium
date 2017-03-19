<?php

namespace ViewModel;

use ReflectionProperty;
use Symfony\Component\Validator\Constraint;
use View\FormFieldViewInterface;
use ViewModel\FormModelInterface;

class FormFieldModel
{
    /**
     * @var string
     */
    protected $name;
    
    protected $label;
    
    /**
     * @var ReflectionProperty
     */
    protected $rpValue;

    protected $id;
    
    /**
     * @var FormModelInterface
     */
    protected $model;
    
    /**
     * @var string
     */
    protected $labelI18n;
    
    /**
     * @var Constraint[]
     */
    protected $constraints;
    
    /**
     * @var FormFieldViewInterface
     */
    protected $view;
    
    protected $type;
    
    /**
     * 
     * @param FormModelInterface $model
     * @param \Symfony\Component\Validator\Constraints[] $constraints
     * @param \ViewModel\ReflectionProperty $rpValue
     */
    public function __construct(FormModelInterface $model, FormFieldViewInterface $view, ReflectionProperty $rpValue, array & $constraints, $type) {
        $rpValue->setAccessible(true);
        $this->rpValue = $rpValue;
        $this->model = $model;
        $this->view = $view;
        $this->type = $type;
        $this->constraints = $constraints;
        $this->name = $rpValue->getName();
        $id = str_replace('\\', '.', $rpValue->getDeclaringClass()->getName()) . "." . $this->name;
        $this->id = $id;
        $this->label = "$id.label";
        $this->placeholder= "$id.ph";
    }

    /**
     * @return string
     */
    public function getLabelI18n() {
        return $this->labelI18n;
    }

    /**
     * @return string The name of the field
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Gets the value of the field.
     *
     * @return string|array The value of the field
     */
    public function getValue() {
        return $this->rpValue->getValue($this->model);
    }
    
    /**
     * @return Constraint[]
     */
    public function getConstraints() : array {
        return $this->constraints;
    }

    /**
     * @param string $value The value of the field
     */
    public function setValue(string $value = null) {
        $this->rpValue->setValue($this->model, $value);
    }
    
    public function getRpValue(): ReflectionProperty {
        return $this->rpValue;
    }

    public function getModel(): FormModelInterface {
        return $this->model;
    }

    /**
     * @return FormFieldViewInterface
     */
    public function getView(): FormFieldViewInterface {
        return $this->view;
    }
       
    public function getPlaceholder() {
        return $this->placeholder;
    }

    public function getLabel() {
        return $this->label;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function setFromForm($value) {
        $this->setValue($this->convertFromForm($value));
    }
    
    public function getForForm() {
        return $this->convertForForm($this->getValue());
    }

    public function convertForForm(string $value = null) {
        if ($value === null) {
            return '';
        }
        switch ($this->type) {
            case "bool":
                return $value ? "on" : "";
            default:
                return (string)$value;
        }
        
    }
    
    public function convertFromForm(string $value) {
        if ($this->type === null || $this->type === "string") {
            return $value;
        }
        switch ($this->type) {
            case "int":
                return intval($value);
            case "float":
            case "double":
                return doubleval($value);
            case "bool":
                return strcasecmp($value, "1") || strcasecmp($value,"on") || strcasecmp($value, "true");
            default:
                error_log("Unhandled type $this->type");
                return $value;
        }
    }
}