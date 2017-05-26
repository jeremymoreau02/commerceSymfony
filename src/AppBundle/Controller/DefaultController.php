<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function menu($active){
        $isCat = false;
        $isProd = false;
        $isStock = false;
        $isUser = false;

        switch($active){
            case 'stock':
                $isStock = true;
                break;
            case 'cat':
                $isCat = true;
                break;
            case 'prod':
                $isProd = true;
                break;
            case 'users':
                $isUser = true;
                break;
        }
        
        return [
                [
                    'name' => 'Main navigation',
                    'links' => [
                        [
                            'title' => 'Produits',
                            'href' => '/listProducts',
                            'active' => $isProd,
                            'administrateur' => true,
                            'responsable' => true,
                            'magasinier' => false,
                            'icon' => 'fa fa-dashboard',
                            'subsections' => [
                                [
                                    'name' => 'Produits navigation',
                                    'links' => [
                                        [
                                            'title' => 'Liste des produits',
                                            'href' => '/listProducts',
                                            'active' => false,
                                            'administrateur' => true,
                                            'responsable' => true,
                                            'magasinier' => false,
                                            'icon' => 'fa fa-dashboard',
                                        ],
                                            [
                                            'title' => 'Nouveau produit',
                                            'href' => '/newProduct',
                                            'active' => false,
                                            'administrateur' => true,
                                            'responsable' => true,
                                            'magasinier' => false,
                                            'icon' => 'fa fa-dashboard',
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'Stock',
                            'href' => '/listStock',
                            'active' => $isStock,
                            'administrateur' => true,
                            'responsable' => true,
                            'magasinier' => true,
                            'icon' => 'fa fa-building',
                            'subsections' => [
                                [
                                    'name' => 'Produits navigation',
                                    'links' => [
                                        [
                                            'title' => 'Liste des produits en stock',
                                            'href' => '/listStock',
                                            'active' => true,
                                            'administrateur' => true,
                                            'responsable' => true,
                                            'magasinier' => true,
                                            'icon' => 'fa fa-building',
                                        ],
                                            [
                                            'title' => 'Ajouter un produit au stock',
                                            'href' => '/addStock',
                                            'active' => false,
                                            'administrateur' => true,
                                            'responsable' => true,
                                            'magasinier' => false,
                                            'icon' => 'fa fa-building',
                                        ],
                                    ]
                                ]
                            ]
                        ],
                            [
                            'title' => 'Catégories',
                            'href' => '/listCategories',
                            'active' => $isCat,
                            'administrateur' => true,
                            'responsable' => true,
                            'magasinier' => false,
                            'icon' => 'fa fa-dashboard',
                            'subsections' => [
                                [
                                    'name' => 'Produits navigation',
                                    'links' => [
                                        [
                                            'title' => 'Liste des catégories',
                                            'href' => '/listCategories',
                                            'active' => false,
                                            'administrateur' => true,
                                            'responsable' => true,
                                            'magasinier' => false,
                                            'icon' => 'fa fa-dashboard',
                                        ],
                                            [
                                            'title' => 'Nouvelle catégorie',
                                            'href' => '/newCategorie',
                                            'active' => false,
                                            'administrateur' => true,
                                            'responsable' => true,
                                            'magasinier' => false,
                                            'icon' => 'fa fa-dashboard',
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'Utilisateurs',
                            'href' => '/ingames',
                            'active' => $isUser,
                            'administrateur' => true,
                            'responsable' => false,
                            'magasinier' => false,
                            'icon' => 'fa fa-user',
                            'subsections' => [
                                [
                                    'name' => 'Utilisateurs navigation',
                                    'links' => [
                                        [
                                            'title' => 'Liste des utilisateurs',
                                            'href' => '/listUser',
                                            'active' => false,
                                            'administrateur' => true,
                                            'responsable' => false,
                                            'magasinier' => false,
                                            'icon' => 'fa fa-user',
                                        ],
                                            [
                                            'title' => 'Nouvel utilisateur',
                                            'href' => '/newUser',
                                            'active' => false,
                                            'administrateur' => true,
                                            'responsable' => false,
                                            'magasinier' => false,
                                            'icon' => 'fa fa-user',
                                        ],
                                    ]
                                ]
                            ]
                        ],
                    ]
                ]
            ];
    }

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $db = $this->getDoctrine()->getManager()->getConnection();
        $session = $request->getSession();
        $sessid = $session->get('id');

        if (!empty($sessid))
        {
            return $this->redirectToRoute('index');
        }

        $query = $db->prepare('SELECT id FROM role WHERE nom = :nom');
        $query->bindValue('nom', 'administrateur');
        $query->execute();
        $result = $query->fetch();

        $query = $db->prepare('SELECT COUNT(*) AS nb FROM user WHERE idRole = :role');
        $query->bindValue('role', $result['id']);
        $query->execute();
        $result = $query->fetch();
        if($result['nb'] > 0){
            // replace this example code with whatever you need
            return $this->render('default/login.html.twig', [
                'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
                'errors' => []
            ]);
        }
        else{
            // replace this example code with whatever you need
            return $this->render('default/register.html.twig', [
                'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
                'errors' => []
            ]);
        }
        
    }

     /**
     * @Route("/home", name="index")
     */
    public function homeAction(Request $request)
    {
        
        return $this->redirectToRoute('listStock');
        
    }
}
