<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class <?= $class_name ?><?= "\n" ?>
{
<?php if ($config['search']['filter_view']['activate']): ?>
    const NUMBER_BY_PAGE = 15;

<?php endif ?>
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        RequestStack $requestStack,
        SessionInterface $session,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator
    ) {
        $this->requestStack = $requestStack;
        $this->session = $session;
        $this->em = $em;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
    }

<?php if ($config['search']['filter_view']['activate']): ?>
    /**
     * Configure the filter form.
     *
     *  Set the filter's default fields, save and retrieve the last search in session.
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function configFormFilter(FormInterface $form)
    {
<?php if ($config['search']['pagination']): ?>
        $page = $this->requestStack->getCurrentRequest()->get('page');
        $page ?: $page = $this->session->get('<?= $route_name ?>_page', 1);
        $this->session->set('<?= $route_name ?>_page', $page);
<?php endif ?>
        if (!$form->getData()) {
            $form->setData($this->getDefaultFormSearchData());
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $this->session->set('<?= $route_name ?>_search', $form->get('search')->getData());
<?php if ($config['search']['pagination']): ?>
            $this->session->set('<?= $route_name ?>_number_by_page', $form->get('number_by_page')->getData());
<?php endif ?>
        }

        return $form;
    }

    /**
     * Get the default data from the filter form.
     *
     *  Get saved data in session or default filter form.
     *
     * @return array
     */
    public function getDefaultFormSearchData()
    {
        return [
            'search' => $this->session->get('<?= $route_name ?>_search', null),
<?php if ($config['search']['pagination']): ?>
            'number_by_page' => $this->session->get('<?= $route_name ?>_number_by_page', self::NUMBER_BY_PAGE),
<?php endif ?>
        ];
    }
<?php if ($config['search']['pagination']): ?>

    /**
     * Get query data.
     *
     *  Transform filter form data into an array compatible with url parameters.
     *  The returned array must be merged with the parameters of the route.
     *
     * @return array
     */
    public function getQueryData(array $data)
    {
        $queryData['filter'] = [];
        foreach ($data as $key => $value) {
            if (null === $value) {
                $queryData['filter'][$key] = '';
            } else {
                $queryData['filter'][$key] = $value;
            }
        }

        return $queryData;
    }
<?php endif ?>

<?php endif ?>
<?php if ($config['search']['multi_select']): ?>
    /**
     * Valid the multiple selection form.
     *
     *  If the result returned is a string the form is not validated and the message is added in the flash bag
     *
     * @throws LogicException
     *
     * @return bool|string
     */
    public function validationBatchForm(FormInterface $form)
    {
        $<?= $entity_lower_camel_case_plural ?> = $form->get('<?= $entity_snake_case_plural ?>')->getData();
        if (0 === count($<?= $entity_lower_camel_case_plural ?>)) {
            return $this->translator->trans('error.no_element_selected', [], '<?= $file_translation_name ?>');
        }
        $action = $form->get('action')->getData();

        switch ($action) {
<?php if ($config['update']['multi_select']): ?>
            case 'update':
                return $this->validationUpdate($<?= $entity_lower_camel_case_plural ?>);
<?php endif ?>
<?php if ($config['delete']['multi_select']): ?>
            case 'delete':
                return $this->validationDelete($<?= $entity_lower_camel_case_plural ?>);
<?php endif ?>
        }

        return true;
    }

<?php if ($config['update']['multi_select']): ?>
    /**
     * Valid the update action from multiple selection form.
     *
     *  If the result returned is a string the form is not validated and the message is added in the flash bag
     *
     * @return bool|string
     */
    public function validationUpdate($<?= $entity_lower_camel_case_plural ?>)
    {
        <?= '/*' ?>foreach($<?= $entity_lower_camel_case_plural ?> as $<?= $entity_lower_camel_case ?>) {

        }<?= '*/' ?>

        return true;
    }

<?php endif ?>
<?php if ($config['delete']['multi_select']): ?>
    /**
     * Valid the delete action from multiple selection form.
     *
     *  If the result returned is a string the form is not validated and the message is added in the flash bag
     *
     * @return bool|string
     */
    public function validationDelete($<?= $entity_lower_camel_case_plural ?>)
    {
        <?= '/*' ?>foreach($<?= $entity_lower_camel_case_plural ?> as $<?= $entity_lower_camel_case ?>) {

        }<?= '*/' ?>

        return true;
    }

<?php endif ?>
    /**
     * Dispatch the multiple selection form.
     *
     *  This method is called after the validation of the multiple selection form.
     *  Different actions can be performed on the list of entities.
     *  If the result returned is a string (url) the controller redirects to this page else if the result returned is false the controller does nothing.
     *
     * @return bool|string
     */
    public function dispatchBatchForm(FormInterface $form)
    {
        $<?= $entity_lower_camel_case_plural ?> = $form->get('<?= $entity_snake_case_plural ?>')->getData();
        $action = $form->get('action')->getData();
        switch ($action) {
<?php if ($config['update']['multi_select']): ?>
            case 'update':
                return $this->urlGenerator->generate('<?= $route_name ?>_update', $this->get<?= $entity_identifier_upper_camel_case_plural ?>($<?= $entity_lower_camel_case_plural ?>));
<?php endif ?>
<?php if ($config['delete']['multi_select']): ?>
            case 'delete':
                return $this->urlGenerator->generate('<?= $route_name ?>_delete', $this->get<?= $entity_identifier_upper_camel_case_plural ?>($<?= $entity_lower_camel_case_plural ?>));
<?php endif ?>
        }

        return false;
    }

    /**
     * Get ids.
     *
     *  Transform entities list into an array compatible with url parameters.
     *  The returned array must be merged with the parameters of the route.
     *
     * @return array
     */
    private function get<?= $entity_identifier_upper_camel_case_plural ?>($<?= $entity_lower_camel_case_plural ?>)
    {
        $<?= $entity_identifier_lower_camel_case_plural ?> = [];
        foreach ($<?= $entity_lower_camel_case_plural ?> as $<?= $entity_lower_camel_case ?>) {
            $<?= $entity_identifier_lower_camel_case_plural ?>[] = $<?= $entity_lower_camel_case ?>->get<?= $entity_identifier_upper_camel_case ?>();
        }

        return ['<?= $entity_identifier_snake_case_plural ?>' => $<?= $entity_identifier_lower_camel_case_plural ?>];
    }

    /**
     * Get $<?= $entity_lower_camel_case_plural ?>
     *
     *  Transform query parameter ids list into an array entities list.
     *
     * @throws InvalidParameterException
     * @throws NotFoundHttpException
     *
     * @return array
     */
    public function get<?= $entity_upper_camel_case_plural ?>()
    {
        $request = $this->requestStack->getCurrentRequest();
        $<?= $entity_identifier_lower_camel_case_plural ?> = $request->query->get('<?= $entity_identifier_snake_case_plural ?>', null);
        if (!is_array($<?= $entity_identifier_lower_camel_case_plural ?>)) {
            throw new InvalidParameterException();
        }
        $<?= $entity_lower_camel_case_plural ?> = $this->em->getRepository('App\Entity\<?= $entity_upper_camel_case ?>')->findBy<?= $entity_identifier_upper_camel_case ?>($<?= $entity_identifier_lower_camel_case_plural ?>);
        if (count($<?= $entity_identifier_lower_camel_case_plural ?>) !== count($<?= $entity_lower_camel_case_plural ?>)) {
            throw new NotFoundHttpException();
        }

        return $<?= $entity_lower_camel_case_plural ?>;
    }
<?php endif ?>
}
