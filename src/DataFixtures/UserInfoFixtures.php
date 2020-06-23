<?php
/**
 * UserInfo fixtures.
 */

namespace App\DataFixtures;

use App\Entity\UserInfo;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;

/**
 * Class UserInfoFixtures.
 */
class UserInfoFixtures extends AbstractBaseFixtures
{
    /**
     * User repository.
     *
     * @var UserRepository
     */
    private $userRepo;

    /**
     * UserFixtures constructor.
     *
     * @param UserRepository $userRepo User repository
     */
    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * Load data.
     *
     * @param \Doctrine\Persistence\ObjectManager $manager Persistence object manager
     */
    public function loadData(ObjectManager $manager): void
    {
        $users = $this->userRepo->findAll();
        foreach ($users as $user) {
            $groupName = sprintf('userinfo%d', $user->getId());
            $this->createMany(1, $groupName, function () use ($user) {
                $details = new UserInfo();
                $details->setFirstname($this->faker->firstName);
                $details->setLastname($this->faker->lastName);
                $details->setUser($user);

                return $details;
            });
        }

        $manager->flush();
    }
}
