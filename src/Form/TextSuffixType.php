<?php

namespace App\Form;

use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TextSuffixType extends TextType {
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);

        $resolver->setRequired('suffix');
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->addModelTransformer(new CallbackTransformer(
                function($username) use($options) {
                    return rtrim($username, $options['suffix']);
                },
                function($input) use ($options) {
                    return sprintf('%s%s', $input, $options['suffix']);
                }
            ));
    }

    public function finishView(FormView $view, FormInterface $form, array $options) {
        parent::finishView($view, $form, $options);

        $view->vars['suffix'] = $options['suffix'];
    }

    public function getBlockPrefix(): string {
        return 'text_suffix';
    }
}