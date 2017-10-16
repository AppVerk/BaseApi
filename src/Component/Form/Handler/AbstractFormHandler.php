<?php

namespace Component\Form\Handler;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class AbstractFormHandler
{
    /** @var  FormFactory */
    protected $formFactory;

    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @required
     *
     * @param FormFactoryInterface $formFactory
     */
    public function setFormFactory(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @required
     *
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(string $formTypeClass, $model)
    {
        $this->createForm($formTypeClass, $model);

        return $this;
    }

    /**
     * @required
     *
     * @param RequestStack $requestStack
     */
    public function setRequest(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function process()
    {
        $this->validateForm();

        $this->form->handleRequest($this->request);
        if ($this->form->isSubmitted() === false) {
            $this->form->submit($this->request->request->get($this->form->getName()));
        }
        if (!$this->isValid()) {
            $this->errors = $this->getErrorsFromForm($this->form);

            return false;
        }
        $success = $this->success();
        if (!$success) {
            $this->errors = $this->getErrorsFromForm($this->form);

            return false;
        }

        if ($success) {
            return ($success) ? $success : false;
        }

        return true;
    }

    /**
     * Build inheritance form errors
     *
     * @param FormInterface $form
     *
     * @return array
     */
    protected function getErrorsFromForm(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors = array_merge($errors, $childErrors);
                }
            }
        }

        return $errors;
    }

    public function isValid(): bool
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
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getErrorsAsString(): string
    {
        $message = '';
        foreach ($this->errors as $error) {
            if (is_string($error)) {
                $message .= $error;
                continue;
            }
            if (!is_array($error)) {
                $message .= implode(", ", $error);
            } else {
                foreach ($error as $messages) {
                    $message .= implode(", ", $messages);
                }
            }
        }

        return $message;
    }

    public function getFormView(): FormView
    {
        $this->validateForm();

        return $this->form->createView();
    }

    protected function validateForm()
    {
        if ($this->form === null) {
            throw new \Exception("First u need call buildForm method to create form");
        }
    }

    protected function addFormError(string $message, array $params = [], $field = null)
    {
        $message = $this->translator->trans($message, $params, 'forms');

        if ($field !== null) {
            $this->form->get($field)->addError(new FormError($message));
        } else {
            $this->form->addError(new FormError($message));
        }
    }

    protected function createAccessDeniedException($attributes, $object = null)
    {
        $exception = new AccessDeniedException('Access Denied.');
        $exception->setAttributes($attributes);
        $exception->setSubject($object);

        throw $exception;
    }

    /**
     * Place for your logic
     * Database operation or some kind of API action, etc
     */
    abstract protected function success();

}
