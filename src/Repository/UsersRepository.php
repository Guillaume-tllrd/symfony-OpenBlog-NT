<?php

namespace App\Repository;

use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Users>
 */
class UsersRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Users) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * @return Users[] Returns an array of Users objects
    //     */
    // on crée une méthode pour trouver les users par leur post, on décommente l'ex donné par défaut
    public function getUsersByPosts($limit): array
    {
        // notre requête SQL/ SELECT users.*, COUNT(post.id) as total FROM 'users' LEFT JOIN posts ON posts.users_id = users.id GROUP BY users.id ORDER BY total desc;
        return $this->createQueryBuilder('u') // createQueryBuilder permet de créer notre requête
            ->addSelect('COUNT(p) as total')
            ->leftJoin('u.posts', 'p')
            ->groupBy('u.id')
            ->orderBy('total', 'desc')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
            // à partir de là on a la même requête sql au dessus en ajoutant même la limit, on a plus qu'a invoqué cette méthode dans le controller que l'on souhaite
        ;
    }

    //    public function findOneBySomeField($value): ?Users
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
