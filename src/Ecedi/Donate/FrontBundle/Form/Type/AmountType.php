<?php
namespace Ecedi\Donate\FrontBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Ecedi\Donate\FrontBundle\Form\DataTransformer\AmountChoiceToIntentAmountTransformer;

class AmountType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    //TODO find a way to remove @translator
    //TODO move validation translations to validators
    /**
     * @since 2.4 flip keys and values and add choices_as_values option
     * @param  FormBuilderInterface $builder [description]
     * @param  array                $options [description]
     * @return [type]               [description]
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Ajout d'un champ de saisi manuel si voulu
        $options['choices']['Other amount'] = 'manual';
        $builder
            ->addViewTransformer(new AmountChoiceToIntentAmountTransformer([
                'manual',
                'preselected',
            ]))
            ->add('preselected', 'choice', [
                'choices'   => $options['choices'],
                'required'  => false,
                'expanded'    => true,
                'multiple'    => false,
                'label'    => false,
                'data'        => $options['default'],
                //'placeholder' => false,
                'choices_as_values' => true,
                'choice_value' => function ($choice) {
                    return $choice;
                },
            ])
            ->add('manual', 'money', [
                'currency'    => 'EUR',
                'required'  => false,
                'label'    => 'Another amount',
                'scale' => 0,
                'constraints' => [
                    new Assert\Range(
                        [
                          'min'        => $options['min_amount'],
                          'max'        => $options['max_amount'],
                          'minMessage'    => $this->translator->trans('Amount must be greater than %amount% €', ['%amount%' => $options['min_amount']], 'validation'),
                          'maxMessage'    => $this->translator->trans('Amount must be lower than  %amount% €', [ '%amount%' => $options['max_amount']], 'validation'),
                        ]
                    ),
                ]
            ]
        );
    }

        /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['title'] = $options['title'];
    }
    /**
     * {@inheritdoc}
     * @since 2.4 use new method signature since sf 2.7
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices'        => [],
            'min_amount'     => 5,
            'max_amount'     => 4000,
            'default'        => '',
            'title'          => '',
        ]);
    }

    public function getName()
    {
        return 'amount_selector';
    }
}
