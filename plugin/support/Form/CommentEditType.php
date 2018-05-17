<?php

namespace FormaLibre\SupportBundle\Form;

use FormaLibre\SupportBundle\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentEditType extends AbstractType
{
    private $type;

    public function __construct($type = Comment::PUBLIC_COMMENT)
    {
        $this->type = $type;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            ContentType::class,
            'tinymce',
            [
                'required' => true,
                'label' => ContentType::class,
                'translation_domain' => 'platform',
            ]
        );
    }

    public function getName()
    {
        return $this->type === Comment::PUBLIC_COMMENT ? 'comment_edit_form' : 'private_comment_edit_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'support']);
    }
}
