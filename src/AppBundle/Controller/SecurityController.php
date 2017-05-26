<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        $session = $request->getSession();
        $sessid = $session->get('id');
        $session->invalidate();

        if (!empty($sessid))
        {
            return $this->redirectToRoute('homepage');
        }

        $errors = [];
        $username = "";

        if ($request->getMethod() == 'POST')
        {
            
            $username = $request->request->get('name');
            $password = $request->request->get('password');
            $remember = $request->request->get('remember');

            if (empty($username))
            {
                $errors[] = "Missing username";
            }

            if (empty($password))
            {
                $errors[] = "Missing password";
            }

            if (empty($errors))
            {
                $db = $this->getDoctrine()->getManager()->getConnection();
                $query = $db->prepare(
                    'SELECT * FROM user WHERE pseudo = :username AND password = :password'
                );
                $query->bindValue('username', $username);
                $query->bindValue('password', md5($password));
                $query->execute();
                $user = $query->fetch();

                if ($user == false)
                {
                    $errors[] = "Username/password mismatch";
                }
                else
                {
                    $query = $db->prepare(
                        'SELECT * FROM role WHERE id = :id'
                    );
                    $query->bindValue('id', $user['idRole']);
                    $query->execute();
                    $role = $query->fetch();


                    $session->set('id', $user['id']);
                    $session->set('name', $user['pseudo']);
                    $session->set('role', $role['nom']);
                    return $this->redirectToRoute('homepage');
                }
            }
        }

        return $this->render('default/login.html.twig', [
            'errors' => $errors,
            'username' => $username
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction(Request $request)
    {
        $session = $request->getSession();
        $session->invalidate();

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/register", name="register")
     */
    public function registerAction(Request $request)
    {
        $session = $request->getSession();

        $errors = [];


        if ($request->getMethod() == 'POST')
        {
            $username = $request->request->get('username');
            $password = $request->request->get('password');
            $password2 = $request->request->get('password2');
        

            if (empty($username))
            {
                $errors[] = 'Missing username';
            }

            if (empty($password))
            {
                $errors[] = 'Missing password';
            }

            if (empty($password2))
            {
                $errors[] = 'Missing password verification';
            }

            if (empty($errors))
            {
                if ($password != $password2)
                {
                    $errors[] = 'Password verification failed';
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
                        $errors[] = 'A user with that name already exists';
                    }
                    else
                    {
                        $query = $db->prepare('SELECT id FROM role WHERE nom = :nom');
                        $query->bindValue('nom', 'administrateur');
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

                        $session->set('id', $userid);
                        $session->set('name', $username);
                        $session->set('role', 'administrateur');

                        return $this->redirectToRoute('homepage');
                    }
                }
            }
        }

        return $this->render('default/register.html.twig', [
            'errors' => $errors
        ]);
    }
}
