<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use PDO;

class CategoriesController extends Controller
{
    
    /**
     * @Route("/newCategorie", name="newCategorie")
     */
    public function newCategorieAction(Request $request)
    {
        $defaultController = new DefaultController();
        $db = $this->getDoctrine()->getManager()->getConnection();
        $session = $request->getSession();
        $sessid = $session->get('id');
        $sessrole = $session->get('role');

        $errors = [];

        if (empty($sessid) || ($sessrole != 'administrateur'))
        {
            return $this->redirectToRoute('index');
        }
        
        if ($request->getMethod() == 'POST')
        {
            $nom = $request->request->get('nom');
        

            if (empty($nom))
            {
                $errors[] = 'Veuillez saisir un nom.';
            }
            
            if (empty($errors))
            {
                $db = $this->getDoctrine()->getManager()->getConnection();

                $query = $db->prepare('SELECT COUNT(*) AS nCat FROM categorie WHERE nom = :nom');
                $query->bindValue('nom', $nom);
                $query->execute();
                $result = $query->fetch();

                if ($result['nCat'] > 0)
                {
                    $errors[] = 'Une catégorie avec ce nom existe déjà.';
                }
                else
                {
                    $query = $db->prepare(
                        'INSERT INTO categorie (nom) VALUES (:nom)'
                    );
                    $query->bindValue('nom', $nom);
                    $query->execute();

                    return $this->redirectToRoute('listCategorie');
                    
                }
            }
        }

        return $this->render('default/createCategorie.html.twig', [
            'errors' => $errors,
            'user' => [
                'name' => $session->get('name'),
                'role' => $session->get('role')
            ],
            'sections' => $defaultController->menu('cat')
        ]);
        
    }

    /**
     * @Route("/deleteCategorie/{cid}", name="deleteCategorie")
     */
    public function deleteCategorieAction(Request $request, $cid)
    {
        $db = $this->getDoctrine()->getManager()->getConnection();
        $session = $request->getSession();
        $sessid = $session->get('id');
        $sessrole = $session->get('role');
        $query = $db->prepare(
            'DELETE FROM categorie WHERE id = :id'
        );
        $query->bindValue('id', $cid);
        $query->execute();

        return $this->redirectToRoute('listCategorie');
    }

    /**
     * @Route("/updateCategorie/{cid}", name="updateCategorie")
     */
    public function updateCategorieAction(Request $request, $cid)
    {
        $defaultController = new DefaultController();
        $db = $this->getDoctrine()->getManager()->getConnection();
        $session = $request->getSession();
        $sessid = $session->get('id');
        $sessrole = $session->get('role');

        $errors = [];

        if (empty($sessid) || ($sessrole != 'administrateur'))
        {
            return $this->redirectToRoute('index');
        }
        
        if ($request->getMethod() == 'POST')
        {
            $nom = $request->request->get('nom');
        

            if (empty($nom))
            {
                $errors[] = 'Veuillez saisir un nom.';
            }
            
            if (empty($errors))
            {
                $db = $this->getDoctrine()->getManager()->getConnection();

                $query = $db->prepare('SELECT COUNT(*) AS nCat FROM categorie WHERE nom = :nom AND id != :id');
                $query->bindValue('nom', $nom);
                $query->bindValue('id', $cid);
                $query->execute();
                $result = $query->fetch();

                if ($result['nCat'] > 0)
                {
                    $errors[] = 'Une catégorie avec ce nom existe déjà.';
                }
                else
                {
                    $query = $db->prepare(
                        'UPDATE categorie SET nom = :nom WHERE id = :id'
                    );
                    $query->bindValue('id', $cid);
                    $query->bindValue('nom', $nom);
                    $query->execute();
                    
                    return $this->redirectToRoute('listCategorie');
                    
                }
            }
        }

       

        $query = $db->prepare('SELECT * FROM categorie WHERE id = :id');
        $query->bindValue('id', $cid);
        $query->execute();
        $result = $query->fetch();

        return $this->render('default/createCategorie.html.twig', [
            'errors' => $errors,
            'user' => [
                'name' => $session->get('name'),
                'role' => $session->get('role')
            ],
            'categorieToUpdate' => $result,
            'sections' => $defaultController->menu('cat')
        ]);
        
    }

    /**
     * @Route("/listCategories", name="listCategorie")
     */
    public function listCategorieAction(Request $request)
    {
        $db = $this->getDoctrine()->getManager()->getConnection();
        $session = $request->getSession();
        $sessid = $session->get('id');
        $sessrole = $session->get('role');
        $defaultController = new DefaultController();
        $errors = [];

        if (empty($sessid) || ($sessrole != 'administrateur'))
        {
            return $this->redirectToRoute('index');
        }

        $query = $db->prepare('SELECT * FROM categorie');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);


        return $this->render('default/listCategories.html.twig', [
            'errors' => $errors,
            'user' => [
                'name' => $session->get('name'),
                'role' => $session->get('role')
            ],
            'Categories' => $result,
            'sections' => $defaultController->menu('cat')
        ]);
        
    }

     
}
