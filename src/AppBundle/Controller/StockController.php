<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use PDO;

class StockController extends Controller
{
    /**
     * @Route("/addStock", name="addStock")
     */
    public function addStockAction(Request $request)
    {
        $defaultController = new DefaultController();
        
        $db = $this->getDoctrine()->getManager()->getConnection();
        $session = $request->getSession();
        $sessid = $session->get('id');
        $sessrole = $session->get('role');

        $errors = [];

        

        if (empty($sessid) || (($sessrole != 'administrateur') && ($sessrole != 'responsable')))
        {
        
            return $this->redirectToRoute('index');
        }
        

        $query = $db->prepare('SELECT * FROM categorie');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        $categories = [];
        foreach ($result as $id => $cat)
        {
            $categories[$id] = $cat["nom"];
        }

        $query = $db->prepare('SELECT * FROM produit WHERE isInStock = 0');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach($result as $id=>$res){
            
            $query = $db->prepare('SELECT nom FROM categorie WHERE id = :id');
            $query->bindValue('id', $res['idCategorie']);
            $query->execute();
            $result2 = $query->fetch();

            $result[$id]["categorie"] = $result2['nom'];
        }

        return $this->render('default/addProductStock.html.twig', [
            'errors' => $errors,
            'user' => [
                'name' => $session->get('name'),
                'role' => $session->get('role')
            ],
            'categories' => $categories,
            'produits' => $result,
            'sections' => $defaultController->menu('stock')
        ]);
        
    }

    /**
     * @Route("/suppStock/{pref}", name="suppStock")
     */
    public function suppStockAction(Request $request, $pref)
    {
        $defaultController = new DefaultController();
        
        $db = $this->getDoctrine()->getManager()->getConnection();
        $session = $request->getSession();
        $sessid = $session->get('id');
        $sessrole = $session->get('role');

        $errors = [];

        

        if (empty($sessid) || (($sessrole != 'administrateur') && ($sessrole != 'responsable')))
        {
        
            return $this->redirectToRoute('index');
        }
        
        $query = $db->prepare(
            'DELETE FROM stock WHERE refProduit = :ref'
        );
        $query->bindValue('ref', $pref);
        $query->execute();

        $query = $db->prepare(
            'UPDATE produit SET isInStock = 0 WHERE reference = :ref'
        );
        $query->bindValue('ref', $pref);
        $query->execute();

        return $this->redirectToRoute('listStock');
        
        
    }

    /**
     * @Route("/addStock/{pref}", name="addStockProd")
     */
    public function addStockProdAction(Request $request, $pref)
    {
        $defaultController = new DefaultController();
        
        $db = $this->getDoctrine()->getManager()->getConnection();
        $session = $request->getSession();
        $sessid = $session->get('id');
        $sessrole = $session->get('role');

        $errors = [];

        

        if (empty($sessid) || (($sessrole != 'administrateur') && ($sessrole != 'responsable')))
        {
        
            return $this->redirectToRoute('index');
        }
        
        $query = $db->prepare(
            'INSERT INTO stock (refProduit,nbProduit) VALUES (:ref,0)'
        );
        $query->bindValue('ref', $pref);
        $query->execute();

        $query = $db->prepare(
            'UPDATE produit SET isInStock = 1 WHERE reference = :ref'
        );
        $query->bindValue('ref', $pref);
        $query->execute();

        return $this->redirectToRoute('listStock');
        
        
    }


    /**
     * @Route("/updateProdStock/{pref}/{nb}", name="updateProdStock")
     */
    public function updateProdStockAction(Request $request, $pref, $nb)
    {
         $defaultController = new DefaultController();
        
        $db = $this->getDoctrine()->getManager()->getConnection();
        $session = $request->getSession();
        $sessid = $session->get('id');
        $sessrole = $session->get('role');

        $errors = [];

        $query = $db->prepare('SELECT nbProduit FROM stock WHERE refProduit = :ref');
        $query->bindValue('ref', $pref);
        $query->execute();
        $result2 = $query->fetch();

        if(($result2['nbProduit'] < $nb) && ($sessrole == 'magasinier')){
            $errors[] = "Vous n'avez pas l'autorisation d'ajouter des produits.";
        }
        if(($errors == [])){
            if($nb >= 0){
            
                $query = $db->prepare(
                    'UPDATE stock SET nbProduit = :nb WHERE refProduit = :ref'
                );
                $query->bindValue('ref', $pref);
                $query->bindValue('nb', $nb);
                $query->execute();
                return $this->redirectToRoute('listStock');

            }else{
                $errors[] = "Le stock est déjà vide";
            }
        }

        $query = $db->prepare('SELECT * FROM produit WHERE isInStock = 1');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);


        foreach($result as $id=>$res){
            
            $query = $db->prepare('SELECT nom FROM categorie WHERE id = :id');
            $query->bindValue('id', $res['idCategorie']);
            $query->execute();
            $result2 = $query->fetch();

            $result[$id]["categorie"] = $result2['nom'];

            $query = $db->prepare('SELECT nbProduit FROM stock WHERE refProduit = :ref');
            $query->bindValue('ref', $res['reference']);
            $query->execute();
            $result2 = $query->fetch();

            $result[$id]["nbProduit"] = $result2['nbProduit'];
        }

        
        return $this->render('default/listStock.html.twig', [
            'errors' => $errors,
            'user' => [
                'name' => $session->get('name'),
                'role' => $session->get('role')
            ],
            'produits' => $result,
            'sections' => $defaultController->menu('stock')
        ]);
        
    }


       

    /**
     * @Route("/listStock", name="listStock")
     */
    public function listStockAction(Request $request)
    {
        $defaultController = new DefaultController();
        $db = $this->getDoctrine()->getManager()->getConnection();
        $session = $request->getSession();
        $sessid = $session->get('id');
        $sessrole = $session->get('role');

        $errors = [];
    

        $query = $db->prepare('SELECT * FROM produit WHERE isInStock = 1');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach($result as $id=>$res){
            
            $query = $db->prepare('SELECT nom FROM categorie WHERE id = :id');
            $query->bindValue('id', $res['idCategorie']);
            $query->execute();
            $result2 = $query->fetch();

            $result[$id]["categorie"] = $result2['nom'];

            $query = $db->prepare('SELECT nbProduit FROM stock WHERE refProduit = :ref');
            $query->bindValue('ref', $res['reference']);
            $query->execute();
            $result2 = $query->fetch();

            $result[$id]["nbProduit"] = $result2['nbProduit'];
        }

        return $this->render('default/listStock.html.twig', [
            'errors' => $errors,
            'user' => [
                'name' => $session->get('name'),
                'role' => $session->get('role')
            ],
            'produits' => $result,
            'sections' => $defaultController->menu('stock')
        ]);
        
    }

     
}
