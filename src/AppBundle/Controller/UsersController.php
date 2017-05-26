<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use PDO;

class UsersController extends Controller
{
    /**
     * @Route("/newUser", name="newUser")
     */
    public function newUserAction(Request $request)
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
           $username = $request->request->get('username');
            $password = $request->request->get('password');
            $password2 = $request->request->get('password2');
            $role = $request->request->get('role');
        

            if (empty($username))
            {
                $errors[] = 'Veuillez saisir un pseudo.';
            }

            if (empty($password))
            {
                $errors[] = 'Veuillez saisir un mot de passe.';
            }

            if (empty($password2))
            {
                $errors[] = 'Veuillez saisir le mot de passe de vérification';
            }

            if (empty($errors))
            {
                if ($password != $password2)
                {
                    $errors[] = 'Les mots de passes sont différents';
                }
                else
                {
                    $db = $this->getDoctrine()->getManager()->getConnection();

                    $query = $db->prepare('SELECT COUNT(*) AS nuser FROM user WHERE pseudo = :username');
                    $query->bindValue('username', $username);
                    $query->execute();
                    $result = $query->fetch();

                    if ($result['nuser'] > 0)
                    {
                        $errors[] = 'Un utilisateur avec ce nom existe déjà.';
                    }
                    else
                    {
                        $query = $db->prepare('SELECT id FROM role WHERE nom = :nom');
                        $query->bindValue('nom', $role);
                        $query->execute();
                        $result2 = $query->fetch();
                        $query = $db->prepare(
                            'INSERT INTO user (idRole,pseudo,password) VALUES (:role,:username,:password)'
                        );
                        $query->bindValue('username', $username);
                        $query->bindValue('role', $result2['id']);
                        $query->bindValue('password', md5($password));
                        $query->execute();
                        $userid = $db->lastInsertId();

                        return $this->redirectToRoute('listUser');
                    }
                }
            }
        }

        $query = $db->prepare('SELECT * FROM role');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        $roles = [];
        foreach ($result as $id => $role)
        {
            $roles[$id] = $role["nom"];
        }

        return $this->render('default/createAccount.html.twig', [
            'errors' => $errors,
            'user' => [
                'name' => $session->get('name'),
                'role' => $session->get('role')
            ],
            'roles' => $roles,
            'sections' => $defaultController->menu('users')
        ]);
        
    }

    /**
     * @Route("/deleteUser/{uid}", name="deleteUser")
     */
    public function deleteUserAction(Request $request, $uid)
    {
        $db = $this->getDoctrine()->getManager()->getConnection();
        $session = $request->getSession();
        $sessid = $session->get('id');
        $sessrole = $session->get('role');
        $query = $db->prepare(
            'DELETE FROM user WHERE id = :id'
        );
        $query->bindValue('id', $uid);
        $query->execute();

        if($uid == $sessid){
            $session->invalidate();
        }
        
        return $this->redirectToRoute('listUser');
    }

    /**
     * @Route("/updateUser/{uid}", name="updateUser")
     */
    public function updateUserAction(Request $request, $uid)
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
           $username = $request->request->get('username');
            $password = $request->request->get('password');
            $password2 = $request->request->get('password2');
            $role = $request->request->get('role');
        

            if (empty($username))
            {
                $errors[] = 'Veuillez saisir un pseudo.';
            }
            
            if (empty($errors))
            {
                if ($password != $password2)
                {
                    $errors[] = 'Les mots de passes sont différents';
                }
                else
                {
                    $db = $this->getDoctrine()->getManager()->getConnection();

                    $query = $db->prepare('SELECT COUNT(*) AS nuser FROM user WHERE pseudo = :username AND id != :id');
                    $query->bindValue('username', $username);
                    $query->bindValue('id', $uid);
                    $query->execute();
                    $result = $query->fetch();

                    if ($result['nuser'] > 0)
                    {
                        $errors[] = 'Un utilisateur avec ce nom existe déjà.';
                    }
                    else
                    {
                        $query = $db->prepare('SELECT id FROM role WHERE nom = :nom');
                        $query->bindValue('nom', $role);
                        $query->execute();
                        $result2 = $query->fetch();
                        if($password != ""){
                            $query = $db->prepare(
                                'UPDATE user SET pseudo = :pseudo, idRole = :role WHERE id = :id'
                            );
                            $query->bindValue('id', $uid);
                            $query->bindValue('pseudo', $username);
                            $query->bindValue('role', $result2['id']);
                            $query->execute();

                        }else{
                            $query = $db->prepare(
                                'UPDATE user SET pseudo = :pseudo, idRole = :role, password = :password WHERE id = :id'
                            );
                            $query->bindValue('id', $uid);
                            $query->bindValue('pseudo', $username);
                            $query->bindValue('role', $result2['id']);
                            $query->bindValue('password', md5($password));
                            $query->execute();

                        }
                        
                        return $this->redirectToRoute('listUser');
                    }
                }
            }
        }

       

        $query = $db->prepare('SELECT * FROM user WHERE id = :id');
        $query->bindValue('id', $uid);
        $query->execute();
        $result = $query->fetch();

        $query = $db->prepare('SELECT * FROM role');
        $query->execute();
        $result2 = $query->fetchAll(PDO::FETCH_ASSOC);

        $roles = [];
        foreach ($result2 as $id => $role)
        {
            $roles[$id] = $role["nom"];
            if($result['idRole']==$role["id"]){
                $result['role'] = $role["nom"];
            }
        }

        return $this->render('default/createAccount.html.twig', [
            'errors' => $errors,
            'user' => [
                'name' => $session->get('name'),
                'role' => $session->get('role')
            ],
            'roles' => $roles,
            'userToUpdate' => $result,
            'sections' => $defaultController->menu('users')
        ]);
        
    }

    /**
     * @Route("/listUser", name="listUser")
     */
    public function listUserAction(Request $request)
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

        $query = $db->prepare('SELECT * FROM user');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach($result as $id=>$res){
            
            $query = $db->prepare('SELECT nom FROM role WHERE id = :id');
            $query->bindValue('id', $res['idRole']);
            $query->execute();
            $result2 = $query->fetch();

            $result[$id]["role"] = $result2['nom'];
        }


        return $this->render('default/listUsers.html.twig', [
            'errors' => $errors,
            'user' => [
                'name' => $session->get('name'),
                'role' => $session->get('role')
            ],
            'users' => $result,
            'sections' => $defaultController->menu('users')
        ]);
        
    }

     
}
