<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
<?php if (isset($repository_full_class_name)): ?>
use <?= $repository_full_class_name ?>;
<?php endif ?>
<?php if ($config['create']['activate'] || $config['update']['activate']): ?>
use <?= $form_full_class_name ?>;
<?php if (isset($form_update_full_class_name) && $form_full_class_name !== $form_update_full_class_name): ?>
use <?= $form_update_full_class_name ?>;
<?php endif ?>
<?php endif ?>
<?php if ($config['search']['filter_view']['activate'] or $config['search']['multi_select']): ?>
use <?= $manager_full_class_name ?>;
<?php endif ?>
<?php if ($config['search']['filter_view']['activate']): ?>
use <?= $form_filter_full_class_name ?>;
<?php endif ?>
<?php if ($config['update']['multi_select']): ?>
use <?= $form_collection_update_full_class_name ?>;
<?php endif ?>
<?php if ($config['search']['multi_select']): ?>
use <?= $form_batch_full_class_name ?>;
<?php endif ?>
<?php if ($config['delete']['multi_select']): ?>
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
<?php endif ?>
use Symfony\Bundle\FrameworkBundle\Controller\<?= $parent_class_name ?>;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
<?php if ($config['search']['filter_view']['activate'] || $config['search']['pagination']): ?>
use Symfony\Component\HttpFoundation\Session\Session;
<?php endif ?>
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("<?= $route_path ?>")
 */
class <?= $class_name ?> extends <?= $parent_class_name; ?><?= "\n" ?>
{

    /**
     * 
     * @var <?= $repository_upper_camel_case ?>
     */
    private $<?= $repository_lower_camel_case ?>;
    
<?php if ($config['search']['filter_view']['activate'] or $config['search']['multi_select']): ?>
    /**
     * 
     * @var <?= $manager_upper_camel_case ?>
     */
    private $<?= $manager_lower_camel_case ?>;
    
<?php endif ?>
    /**
     * 
     * @var TranslatorInterface 
     */
    private $translator;
    
    public function __construct(<?= $repository_upper_camel_case ?> $<?= $repository_lower_camel_case ?>, <?php if ($config['search']['filter_view']['activate'] or $config['search']['multi_select']): ?><?= $manager_upper_camel_case ?> $<?= $manager_lower_camel_case ?>, <?php endif ?>TranslatorInterface $translator)
    {
        $this-><?= $repository_lower_camel_case ?> = $<?= $repository_lower_camel_case ?>;
<?php if ($config['search']['filter_view']['activate'] or $config['search']['multi_select']): ?>
        $this-><?= $manager_lower_camel_case ?> = $<?= $manager_lower_camel_case ?>;
<?php endif ?>
        $this->translator = $translator;
    }

    /**
     * @Route("/search<?php if ($config['search']['pagination']): ?>/{page}<?php endif ?>", name="<?= $route_name ?>_search", methods="GET<?php
        if ($config['search']['multi_select']): ?>|POST<?php
        endif ?>")
     */
<?php
$array_param = [];
if ($config['search']['filter_view']['activate'] or $config['search']['pagination'] or $config['search']['multi_select']) {
    $array_param[] = 'Request $request';
}
if ($config['search']['filter_view']['activate'] or $config['search']['pagination']) {
    $array_param[] = 'Session $session';
}
if ($config['search']['pagination']) {
    $array_param[] = '$page=null';
}
$controller_search_method_param = implode(', ', $array_param);
?>
    public function search(<?= $controller_search_method_param ?>)
    {
<?php if ($config['search']['pagination']): ?>
        if (!$page) { $page = $session->get('<?= $route_name ?>_page', 1); }
<?php if (!$config['search']['filter_view']['activate']): ?>
        $session->set('<?= $route_name ?>_page', $page);
        $numberByPage = 15;
<?php endif ?>
<?php endif ?>
<?php if ($config['search']['filter_view']['activate']): ?>
        $formFilter = $this->createForm(<?= $form_filter_upper_camel_case ?>::class, null, [ 'action' => $this->generateUrl('<?= $route_name ?>_search'<?php if ($config['search']['pagination']): ?>, [ 'page' => 1 ]<?php endif ?>),]);
        $formFilter->handleRequest($request);
        $data = $this-><?= $manager_lower_camel_case ?>->configFormFilter($formFilter)->getData();
<?php endif ?>
<?php if ($config['voter']): ?>
        $this->denyAccessUnlessGranted('<?= $route_name ?>_search', $data);
<?php endif ?>
<?php if ($config['search']['filter_view']['activate'] || $config['search']['pagination']): ?>
<?php
$array_param = [];
if ($config['search']['pagination']) {
    $array_param[] = '$request';
    $array_param[] = '$session';
}
if ($config['search']['filter_view']['activate']) {
    $array_param[] = '$data';
}
if ($config['search']['pagination']) {
    $array_param[] = '$page';
}
if ($config['search']['pagination'] && !$config['search']['filter_view']['activate']) {
    $array_param[] = '$numberByPage';
}
$repository_search_method_param = implode(', ', $array_param);
?>
        $<?= $entity_lower_camel_case_plural ?> = $this-><?= $repository_lower_camel_case ?>-><?= $search_method ?>(<?= $repository_search_method_param ?>);
<?php else: ?>
        $<?= $entity_lower_camel_case_plural ?> = $this-><?= $repository_lower_camel_case ?>-><?= $search_method ?>();
<?php endif ?>
<?php if ($config['search']['filter_view']['activate'] && $config['search']['pagination']): ?>
        $queryData = $this-><?= $manager_lower_camel_case ?>->getQueryData($data);
<?php endif ?>
<?php if ($config['search']['multi_select']): ?>
        $formBatch = $this->createForm(<?= $form_batch_upper_camel_case ?>::class, null, [
            'action' => $this->generateUrl('<?= $route_name ?>_search'<?php
                if ($config['search']['filter_view']['activate'] and $config['search']['pagination']): ?>, array_merge([ 'page' => $page ], $queryData)<?php
                elseif ($config['search']['pagination']): ?>, [ 'page' => $page ]<?php
                endif ?>),
            '<?= $entity_snake_case_plural ?>' => $<?= $entity_lower_camel_case_plural ?>,
        ]);
        $formBatch->handleRequest($request);
        if ($formBatch->isSubmitted() && $formBatch->isValid()) {
            $url = $this-><?= $manager_lower_camel_case ?>->dispatchBatchForm($formBatch);
            if ($url) { return $this->redirect($url); }
        }
<?php endif ?>
        return $this->render('<?= $templates_path ?>/search/index.html.twig', [
            '<?= $entity_snake_case_plural ?>' => $<?= $entity_lower_camel_case_plural ?>,
<?php if ($config['search']['filter_view']['activate']): ?>
            'form_filter' => $formFilter->createView(),
<?php endif ?>
<?php if ($config['search']['multi_select']): ?>
            'form_batch' => $formBatch->createView(),
<?php endif ?>
<?php if ($config['delete']['activate']): ?>
            'form_delete' => $this->createFormBuilder()->getForm()->createView(),
<?php endif ?>
<?php if ($config['search']['pagination']): ?>
            'number_page' => ceil(count($<?= $entity_lower_camel_case_plural ?>) / <?php
if ($config['search']['filter_view']['activate']): ?>$formFilter->get('number_by_page')->getData()<?php
else: ?>$numberByPage<?php endif ?>) ?: 1,
            'page' => $page,
<?php if ($config['search']['filter_view']['activate']): ?>
            'query_data' => $queryData,
<?php endif ?>
<?php endif ?>
        ]);
    }
<?php if ($config['create']['activate']): ?>

    /**
     * @Route("/create", name="<?= $route_name ?>_create", methods="GET|POST")
     */
    public function create(Request $request): Response
    {
<?php if ($config['voter']): ?>
        $this->denyAccessUnlessGranted('<?= $route_name ?>_create');
<?php endif ?>
        $<?= $entity_lower_camel_case ?> = new <?= $entity_upper_camel_case ?>();
        $form = $this->createForm(<?= $form_upper_camel_case ?>::class, $<?= $entity_lower_camel_case ?>);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($<?= $entity_lower_camel_case ?>);
            $em->flush();
            $msg = $this->translator->trans('<?= $name_snake_case ?>.create.flash.success', [ '%identifier%' => $<?= $entity_lower_camel_case ?>, ], '<?= $file_translation_name ?>');
            $this->addFlash('success', $msg);
            return $this->redirectToRoute('<?= $route_name ?>_search');
        }

        return $this->render('<?= $templates_path ?>/create.html.twig', [
            '<?= $entity_snake_case ?>' => $<?= $entity_lower_camel_case ?>,
            'form' => $form->createView(),
        ]);
    }
<?php endif ?>
<?php if ($config['read']['activate']): ?>

    /**
     * @Route("/read/{<?= $entity_identifier_snake_case ?>}", name="<?= $route_name ?>_read", methods="GET")
     */
    public function read(<?= $entity_upper_camel_case ?> $<?= $entity_lower_camel_case ?>): Response
    {
<?php if ($config['voter']): ?>
        $this->denyAccessUnlessGranted('<?= $route_name ?>_read', $<?= $entity_lower_camel_case ?>);
<?php endif ?>
        return $this->render('<?= $templates_path ?>/read.html.twig', [
            '<?= $entity_snake_case ?>' => $<?= $entity_lower_camel_case ?>,
<?php if ($config['read']['action_up'] or $config['read']['action_down']): ?>
<?php if ($config['delete']['activate']): ?>
            'form_delete' => $this->createFormBuilder()->getForm()->createView(),
<?php endif ?>
<?php endif ?>
        ]);
    }
<?php endif ?>
<?php if ($config['update']['activate']): ?>
<?php if ($config['update']['multi_select']): ?>

    /**
     * @Route("/update", name="<?= $route_name ?>_update", methods="GET|POST")
     */
    public function update(Request $request): Response
    {
        $<?= $entity_lower_camel_case_plural ?> = $this-><?= $manager_lower_camel_case ?>->get<?= $entity_upper_camel_case_plural ?>();
<?php if ($config['voter']): ?>
        $this->denyAccessUnlessGranted('<?= $route_name ?>_update', $<?= $entity_lower_camel_case_plural ?>);
<?php endif ?>
        $form = $this->createForm(<?= $form_collection_update_upper_camel_case ?>::class, null, [ '<?= $entity_snake_case_plural ?>' => $<?= $entity_lower_camel_case_plural ?>, ]);
<?php else: ?>

    /**
     * @Route("/update/{<?= $entity_identifier_snake_case ?>}", name="<?= $route_name ?>_update", methods="GET|POST")
     */
    public function update(Request $request, <?= $entity_upper_camel_case ?> $<?= $entity_lower_camel_case ?>): Response
    {
<?php if ($config['voter']): ?>
        $this->denyAccessUnlessGranted('<?= $route_name ?>_update', $<?= $entity_lower_camel_case ?>);
<?php endif ?>
        $form = $this->createForm(<?= $form_update_upper_camel_case ?>::class, $<?= $entity_lower_camel_case ?>);
<?php endif ?>
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $msg = $this->translator->trans('<?= $name_snake_case ?>.update.flash.success', [], '<?= $file_translation_name ?>');
            $this->addFlash('success', $msg);
            return $this->redirectToRoute('<?= $route_name ?>_search');
        }

        return $this->render('<?= $templates_path ?>/update.html.twig', [
<?php if ($config['update']['multi_select']): ?>
            '<?= $entity_snake_case_plural ?>' => $<?= $entity_lower_camel_case_plural ?>,
<?php else: ?>
            '<?= $entity_snake_case ?>' => $<?= $entity_lower_camel_case ?>,
<?php endif ?>
            'form' => $form->createView(),
        ]);
    }
<?php endif ?>
<?php if ($config['delete']['activate']): ?>
<?php if ($config['delete']['multi_select']): ?>

    /**
     * @Route("/delete", name="<?= $route_name ?>_delete", methods="GET|POST")
     */
    public function delete(Request $request): Response
    {    
        $<?= $entity_lower_camel_case_plural ?> = $this-><?= $manager_lower_camel_case ?>->get<?= $entity_upper_camel_case_plural ?>();
<?php if ($config['voter']): ?>
        $this->denyAccessUnlessGranted('<?= $route_name ?>_delete', $<?= $entity_lower_camel_case_plural ?>);
<?php endif ?>
        $formBuilder = $this->createFormBuilder();
        $formBuilder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($<?= $entity_lower_camel_case_plural ?>) {
            $result = $this-><?= $manager_lower_camel_case ?>->validationDelete($<?= $entity_lower_camel_case_plural ?>);
            if (true !== $result) {
                $event->getForm()->addError(new FormError($result));
            }
        });
        $form = $formBuilder->getForm();
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            foreach($<?= $entity_lower_camel_case_plural ?> as $<?= $entity_lower_camel_case ?>) { 
                $em->remove($<?= $entity_lower_camel_case ?>);
            }
            try {
                $em->flush();
                $this->addFlash('success', $this->translator->trans('<?= $name_snake_case ?>.delete.flash.success', [], '<?= $file_translation_name ?>'));
            } catch (\Doctrine\DBAL\DBALException $e) {
                $this->addFlash('warning', $e->getMessage());
            }
            return $this->redirectToRoute('<?= $route_name ?>_search');
        }
        return $this->render('<?= $templates_path ?>/delete.html.twig', [
            '<?= $entity_snake_case_plural ?>' => $<?= $entity_lower_camel_case_plural ?>,
            'form' => $form->createView(),
        ]);
    }
<?php else: ?>

    /**
     * @Route("/delete/{<?= $entity_identifier_snake_case ?>}", name="<?= $route_name ?>_delete", methods="GET|POST")
     */
    public function delete(Request $request, <?= $entity_upper_camel_case ?> $<?= $entity_lower_camel_case ?>): Response
    {
<?php if ($config['voter']): ?>
        $this->denyAccessUnlessGranted('<?= $route_name ?>_delete', $<?= $entity_lower_camel_case ?>);
<?php endif ?>
        $formBuilder = $this->createFormBuilder();
        $form = $formBuilder->getForm();
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $msg = $this->translator->trans('<?= $name_snake_case ?>.delete.flash.success', [ '%identifier%' => $<?= $entity_lower_camel_case ?>, ], '<?= $file_translation_name ?>');
            $em = $this->getDoctrine()->getManager();
            $em->remove($<?= $entity_lower_camel_case ?>);
            try {
                $em->flush();
                $this->addFlash('success', $msg);
            } catch (\Doctrine\DBAL\DBALException $e) {
                $this->addFlash('warning', $e->getMessage());
            }
        }
        return $this->redirectToRoute('<?= $route_name ?>_search');
    }
<?php endif ?>
<?php endif ?>
}
