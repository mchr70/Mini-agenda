<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventFormType;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
 
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $eventsDeleted = false;

        $events = $this->getDoctrine()->getRepository(Event::class)->findAllByDate();

        $now = new \DateTime();
        
        foreach($events as $event)
        {
            if($event->getDate() < $now)
            {
                $em->remove($event);
                $em->flush();
                
                $eventsDeleted = true;
            }
        }

        $events = $this->getDoctrine()->getRepository(Event::class)->findAllByDate();

        if($eventsDeleted)
        {
            $this->addFlash('success', 'La liste des événements a bien été mise à jour. Les événements antérieurs à la date et heure actuelle ont été supprimés.');
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'events' => $events
        ]);
    }

    public function delete($id)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $this->getDoctrine()->getRepository(Event::class)->find($id);

        $em->remove($event);
        $em->flush();

        $this->addFlash('success', 'Cet événement a été supprimé');

        return $this->redirectToRoute('index');
    }

    public function add(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $event = new Event();
        $form = $this->createForm(EventFormType::class, $event);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Cet événement a bien été ajouté');
            return $this->redirectToRoute('index');
        }

        return $this->render('home/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function edit(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $this->getDoctrine()->getRepository(Event::class)->find($id);

        $form = $this->createForm(EventFormType::class, $event);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->flush();

            $this->addFlash('success', 'Cet événement a bien été modifié');
            return $this->redirectToRoute('index');
        }

        return $this->render('home/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
