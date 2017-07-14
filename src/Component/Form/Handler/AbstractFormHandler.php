<?php

namespace AppVerk\FormHandlerBundle\Form\Handler;

use Component\Form\Model\FormModelInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;

abstract class AbstractFormHandler
{
    /** @var  FormFactory */
    private $formFactory;

    /** @var FormInterface */
    private $form;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @required
     * @param FormFactory $formFactory
     */
    public function setFormFactory(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function process(string $formTypeClass, FormModelInterface $model) : bool
    {
        $this->createForm($formTypeClass, $model);
        if(!$this->isValid()){
            $this->errors = $this->getErrorsFromForm($this->form);
            return false;
        }

        $this->success();
        return true;
    }

    /**
     * Build inheritance form errors
     *
     * @param FormInterface $form
     * @return array
     */
    protected function getErrorsFromForm(FormInterface $form) : array
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }

    public function isValid() : bool
    {
        if (true === $this->form->isValid()) {
            return true;
        }
        return false;
    }

    /**
     * @param $formTypeClass
     * @param $model
     */
    private function createForm($formTypeClass, $model)
    {
        $this->form = $this->formFactory->create($formTypeClass, $model);
    }

    /**
     * @return array
     */
    public function getErrors() : array
    {
        return $this->errors;
    }

    /**
     * Place for your logic
     * Database operation or some kind of API action, etc
     */
    abstract protected function success();

}
