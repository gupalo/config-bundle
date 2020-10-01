<?php

namespace Gupalo\ConfigBundle\Controller;

use Gupalo\BrowserNotifier\BrowserNotifier;
use Gupalo\ConfigBundle\Entity\Config;
use Gupalo\ConfigBundle\Form\ConfigType;
use Gupalo\ConfigBundle\Repository\ConfigRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class ConfigController extends AbstractController
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    public function index(ConfigRepository $configRepository): Response
    {
        $items = $configRepository->findBy([], ['name' => 'ASC']);

        return $this->render('@Config/index.html.twig', [
            'items' => $items,
        ]);
    }

    public function new(Request $request, EntityManagerInterface $entityManager, BrowserNotifier $notifier): Response
    {
        $deal = new Config();
        $form = $this->createForm(ConfigType::class, $deal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($deal);
            try {
                $entityManager->flush();
                $notifier->success(sprintf($this->translator->trans('config.notification.created'), $deal->getName()));

                return $this->redirectToRoute('config_index');
            } catch (Throwable $e) {
                $notifier->error($e->getMessage());
            }
        }

        return $this->render('@Config/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function edit(string $id, Request $request, ConfigRepository $repository, EntityManagerInterface $entityManager, BrowserNotifier $notifier): Response
    {
        $deal = $repository->find($id);
        if (!$deal) {
            $notifier->warning(sprintf($this->translator->trans('config.notification.not_found'), $id));

            return $this->redirectToRoute('config_index');
        }

        $form = $this->createForm(ConfigType::class, $deal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($deal);
            try {
                $entityManager->flush();
                $notifier->success(sprintf($this->translator->trans('config.notification.updated'), $deal->getName()));

                return $this->redirectToRoute('config_index');
            } catch (Throwable $e) {
                $notifier->error($e->getMessage());
            }
        }

        return $this->render('@Config/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function delete(string $id, ConfigRepository $repository, EntityManagerInterface $entityManager, BrowserNotifier $notifier): Response
    {
        $item = $repository->find($id);
        if ($item) {
            try {
                $entityManager->remove($item);
                $entityManager->flush();

                $notifier->success(sprintf($this->translator->trans('config.notification.deleted'), $id, $item->getName()));
            } catch (Throwable $e) {
                $notifier->error($e->getMessage());
            }
        } else {
            $notifier->warning(sprintf($this->translator->trans('config.notification.not_found'), $id));
        }

        return $this->redirectToRoute('config_index');
    }
}
