<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use PDO;

class ProductsController extends Controller
{
    /**
     * @Route("/newProduct", name="newProduct")
     */
    public function newProductAction(Request $request)
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
        
        if ($request->getMethod() == 'POST')
        {
            $libelle = $request->request->get('libelle');
            $reference = $request->request->get('reference');
            $prixAchat = $request->request->get('prixAchat');
            $prixVente = $request->request->get('prixVente');
            $tva = $request->request->get('tva');
            $cat = $request->request->get('cat');
        

            if (empty($libelle))
            {
                $errors[] = 'Veuillez saisir un libelle.';
            }
            if (empty($reference))
            {
                $errors[] = 'Veuillez saisir une référence.';
            }else{
                if (is_int($reference))
                {
                    $errors[] = 'La référence doit être un entier.';
                }
            }
            
            if (empty($prixAchat))
            {
                $errors[] = 'Veuillez saisir un prix d\'achat.';
            }
            if (empty($prixVente))
            {
                $errors[] = 'Veuillez saisir un prix de vente.';
            }

            if (empty($tva))
            {
                $errors[] = 'Veuillez saisir la tva.';
            }

            if (empty($errors))
            {
                $db = $this->getDoctrine()->getManager()->getConnection();

                $query = $db->prepare('SELECT id FROM categorie WHERE nom = :nom');
                $query->bindValue('nom', $cat);
                $query->execute();
                $result2 = $query->fetch();

                

                $query = $db->prepare('SELECT COUNT(*) AS ncat FROM produit WHERE idCategorie = :cat AND libelle = :libelle');
                $query->bindValue('cat', $result2['id']);
                $query->bindValue('libelle', $libelle);
                $query->execute();
                $result = $query->fetch();

                if ($result['ncat'] > 0)
                {
                    $errors[] = 'Un produit dans cette catégorie et avec ce nom existe déjà.';
                }
                else
                {
                    $query = $db->prepare('SELECT COUNT(*) AS ncat FROM produit WHERE reference = :reference');
                    $query->bindValue('reference', $reference);
                    $query->execute();
                    $result = $query->fetch();
                    
                    if ($result['ncat'] > 0)
                    {
                        $errors[] = 'Un produit avec cette référence existe déjà.';
                    }
                    else
                    {
                        $query = $db->prepare(
                            'INSERT INTO produit (libelle,reference,prixAchat,prixVente,tva,idCategorie) VALUES (:libelle,:reference,:prixAchat,:prixVente,:tva,:idCategorie)'
                        );
                        $query->bindValue('libelle', $libelle);
                        $query->bindValue('reference', $reference);
                        $query->bindValue('prixAchat', $prixAchat);
                        $query->bindValue('prixVente', $prixVente);
                        $query->bindValue('tva', $tva);
                        $query->bindValue('idCategorie', $result2['id']);
                        $query->execute();

                        return $this->redirectToRoute('listProducts');
                    }
                    
                }
            }
        }

        $query = $db->prepare('SELECT * FROM categorie');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        $categories = [];
        foreach ($result as $id => $cat)
        {
            $categories[$id] = $cat["nom"];
        }

        return $this->render('default/createProduct.html.twig', [
            'errors' => $errors,
            'user' => [
                'name' => $session->get('name'),
                'role' => $session->get('role')
            ],
            'categories' => $categories,
            'sections' => $defaultController->menu('prod')
        ]);
        
    }

    /**
     * @Route("/deleteProduct/{pid}", name="deleteProduct")
     */
    public function deleteProductAction(Request $request, $pid)
    {
        $db = $this->getDoctrine()->getManager()->getConnection();
        $session = $request->getSession();
        $sessid = $session->get('id');
        $sessrole = $session->get('role');

        if (empty($sessid) || (($sessrole != 'administrateur') && ($sessrole != 'responsable')))
        {
            return $this->redirectToRoute('index');
        }

        $query = $db->prepare(
            'DELETE FROM produit WHERE id = :id'
        );
        $query->bindValue('id', $pid);
        $query->execute();

        return $this->redirectToRoute('listProducts');
    }

    /**
     * @Route("/updateProduct/{pid}", name="updateProduct")
     */
    public function updateProductAction(Request $request, $pid)
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
        
        if ($request->getMethod() == 'POST')
        {
            $libelle = $request->request->get('libelle');
            $reference = $request->request->get('reference');
            $prixAchat = $request->request->get('prixAchat');
            $prixVente = $request->request->get('prixVente');
            $tva = $request->request->get('tva');
            $cat = $request->request->get('cat');
        

            if (empty($libelle))
            {
                $errors[] = 'Veuillez saisir un libelle.';
            }
            if (empty($reference))
            {
                $errors[] = 'Veuillez saisir une référence.';
            }else{
                if (is_int($reference))
                {
                    $errors[] = 'La référence doit être un entier.';
                }
            }
            if (empty($prixAchat))
            {
                $errors[] = 'Veuillez saisir un prix d\'achat.';
            }
            if (empty($prixVente))
            {
                $errors[] = 'Veuillez saisir un prix de vente.';
            }

            if (empty($tva))
            {
                $errors[] = 'Veuillez saisir la tva.';
            }
            
            if (empty($errors))
            {
                $db = $this->getDoctrine()->getManager()->getConnection();
                
                $query = $db->prepare('SELECT id FROM categorie WHERE nom = :nom');
                $query->bindValue('nom', $cat);
                $query->execute();
                $result2 = $query->fetch();


                $query = $db->prepare('SELECT COUNT(*) AS nb FROM produit WHERE libelle = :libelle AND idCategorie = :idCat AND id != :id');
                $query->bindValue('libelle', $libelle);
                $query->bindValue('idCat', $result2['id']);
                $query->bindValue('id', $pid);
                $query->execute();
                $result = $query->fetch();

                if ($result['nb'] > 0)
                {
                    $errors[] = 'Un produit avec ce nom et cette catégorie existe déjà.';
                }
                else
                {
                    $query = $db->prepare('SELECT COUNT(*) AS nb FROM produit WHERE reference = :reference AND id != :id');
                    $query->bindValue('reference', $reference);
                    $query->bindValue('id', $pid);
                    $query->execute();
                    $result = $query->fetch();

                    if ($result['nb'] > 0)
                    {
                        $errors[] = 'Un produit avec cette référence existe déjà.';
                    }else{

                        $query = $db->prepare(
                            'UPDATE produit SET libelle = :libelle, idCategorie = :idCat, reference = :reference, prixAchat = :prixAchat, prixVente = :prixVente, tva = :tva WHERE id = :id'
                        );
                        $query->bindValue('libelle', $libelle);
                        $query->bindValue('reference', $reference);
                        $query->bindValue('prixAchat', $prixAchat);
                        $query->bindValue('prixVente', $prixVente);
                        $query->bindValue('tva', $tva);
                        $query->bindValue('idCat', $result2['id']);
                        $query->bindValue('id', $pid);
                        $query->execute();
                         
                        
                        return $this->redirectToRoute('listProducts');
                    }
                }
            
            }
        }

       

        $query = $db->prepare('SELECT * FROM produit WHERE id = :id');
        $query->bindValue('id', $pid);
        $query->execute();
        $result = $query->fetch();

        $query = $db->prepare('SELECT * FROM categorie');
        $query->execute();
        $result2 = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result2 as $id => $cat)
        {
            if($result['idCategorie']==$cat["id"]){
                $result['categorie'] = $cat["nom"];
            }
        }

        return $this->render('default/createProduct.html.twig', [
            'errors' => $errors,
            'user' => [
                'name' => $session->get('name'),
                'role' => $session->get('role')
            ],
            'categories' => $result2,
            'productToUpdate' => $result,
            'sections' => $defaultController->menu('prod')
        ]);
        
    }

    /**
     * @Route("/listProducts", name="listProducts")
     */
    public function listProductsAction(Request $request)
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

        $query = $db->prepare('SELECT * FROM produit');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach($result as $id=>$res){
            
            $query = $db->prepare('SELECT nom FROM categorie WHERE id = :id');
            $query->bindValue('id', $res['idCategorie']);
            $query->execute();
            $result2 = $query->fetch();

            $result[$id]["categorie"] = $result2['nom'];
        }

        return $this->render('default/listProducts.html.twig', [
            'errors' => $errors,
            'user' => [
                'name' => $session->get('name'),
                'role' => $session->get('role')
            ],
            'produits' => $result,
            'sections' => $defaultController->menu('prod')
        ]);
        
    }

     
}
